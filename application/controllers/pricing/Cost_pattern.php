<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Cost_pattern extends CI_Controller
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
    // public function index()
    // {
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
    //         $data['button'] = $this->getbutton($this->id_menu());
    //         $this->load->view('template/header', $data);
    //         $this->load->view('pricing/cost_pattern');
    //     } else {
    //         redirect('error_access');
    //     }
    // }
    //INDEX untuk kebutuhan NPD
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        }
        
        $url_menu_id = $this->input->get('menu_id');
        $active_menu = (!empty($url_menu_id)) ? $url_menu_id : $this->id_menu();

        if ($this->checkuserAccess($active_menu) > 0) {
            $data['button'] = $this->getbutton($active_menu);

            $this->load->view('template/header', $data);
            $this->load->view('pricing/cost_pattern');
        } else {
            redirect('error_access');
        }
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

    public function checkApproval()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $item_fg_id = $this->input->post('item_fg_id');
        $revision = $this->input->post('revision');

        $this->db->select('approved_to');
        $this->db->from('cost_patterns');
        $this->db->where('p_month', $p_month);
        $this->db->where('p_year', $p_year);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('revision', $revision);
        $record = $this->db->get()->row_array();

        echo json_encode($record);
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

    // public function getData()
    // {
    //     if (!$this->input->get()) {
    //         show_error("Cannot process your request");
    //     }

    //     $p_month     = base64_decode($this->input->get('p_month'));
    //     $p_year      = base64_decode($this->input->get('p_year'));
    //     $revision    = base64_decode($this->input->get('revision'));
    //     $customer_id = base64_decode($this->input->get('customer_id'));
    //     $item_fg_id  = base64_decode($this->input->get('item_fg_id'));
    //     $model_name  = base64_decode($this->input->get('model_name'));

    //     /* =====================================================
    //     * 0. DETERMINE ITEM FG LIST (SINGLE / BULK GENERATE)
    //     * ===================================================== */
    //     $itemFgList = [];

    //     if (!empty($item_fg_id)) {
    //         // Generate single FG
    //         $itemFgList[] = $item_fg_id;
    //     } else {
    //         // Generate all FG by customer + period
    //         $itemFgList = $this->db
    //             ->distinct()
    //             ->select('item_fg_id')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('customer_id', $customer_id)
    //             ->get('process_costs')
    //             ->result_array();

    //         $itemFgList = array_column($itemFgList, 'item_fg_id');
    //     }

    //     /* =====================================================
    //     * PREVENT EMPTY DATA
    //     * ===================================================== */
    //     if (empty($itemFgList)) {
    //         echo json_encode(['total' => 0, 'rows' => []]);
    //         exit;
    //     }

    //     $rows = [];
    //     $no = 1;

    //     /* =====================================================
    //     * LOOP PER ITEM FG
    //     * ===================================================== */
    //     foreach ($itemFgList as $item_fg_id) {

    //         /* ================================
    //         * 1. GET MAX REVISION COST PATTERN
    //         * ================================ */
    //         $rev_cp = $this->db->select_max('revision')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->where('customer_id', $customer_id)
    //             ->get('cost_patterns')
    //             ->row();

    //         $revisions = ($revision == "")
    //             ? (empty($rev_cp->revision) ? 0 : $rev_cp->revision + 1)
    //             : $revision;

    //         /* ================================
    //         * 2. DELETE OLD COST PATTERN
    //         * ================================ */
    //         $this->db->where([
    //             'p_month'      => $p_month,
    //             'p_year'       => $p_year,
    //             'revision'     => $revisions,
    //             'item_fg_id'   => $item_fg_id,
    //             'customer_id'  => $customer_id
    //         ])->delete('cost_patterns');

    //         /* ================================
    //         * 3. GET MAX REVISION MATERIAL COST
    //         * ================================ */
    //         $rev_mc = $this->db->select_max('revision')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->get('material_costs')
    //             ->row()->revision;

    //         $materials = $this->db
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('revision', $rev_mc)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->order_by('product_family', 'asc')
    //             ->order_by('id', 'asc')
    //             ->get('material_costs')
    //             ->result_array();

    //         $material_total = $this->db
    //             ->select('total_material_cost')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('revision', $rev_mc)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->limit(1)
    //             ->get('material_costs')
    //             ->row_array();

    //         /* ================================
    //         * 4. GET MAX REVISION PROCESS COST
    //         * ================================ */
    //         $rev_pc = $this->db->select_max('revision')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->where('customer_id', $customer_id)
    //             ->get('process_costs')
    //             ->row()->revision;

    //         $process = $this->db
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('revision', $rev_pc)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->where('customer_id', $customer_id)
    //             ->get('process_costs')
    //             ->row_array();

    //         if (empty($process)) {
    //             continue; // skip jika tidak ada process
    //         }

    //         /* ================================
    //         * 5. GET PACKAGING & TRANSPORT
    //         * ================================ */
    //         $rev_ptc = $this->db->select_max('revision')
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->where('customer_id', $customer_id)
    //             ->get('packaging_transportation_costs')
    //             ->row()->revision;

    //         $ptc = $this->db
    //             ->where('p_month', $p_month)
    //             ->where('p_year', $p_year)
    //             ->where('revision', $rev_ptc)
    //             ->where('item_fg_id', $item_fg_id)
    //             ->where('customer_id', $customer_id)
    //             ->get('packaging_transportation_costs')
    //             ->row_array();

    //         /* ================================
    //         * 6. GROUPING MATERIAL COST
    //         * ================================ */
    //         $familyKey = [
    //             'VIRGIN'       => 'virgin',
    //             'MASTER BATCH' => 'master_batch',
    //             'CHILD PART'   => 'child_part'
    //         ];

    //         $familyCounter = [];
    //         $groups = [];

    //         foreach ($materials as $m) {
    //             $fam = $m['product_family'];

    //             if (!isset($familyCounter[$fam])) {
    //                 $familyCounter[$fam] = 0;
    //             }

    //             $idx = $familyCounter[$fam];

    //             if (!isset($groups[$idx])) {
    //                 $groups[$idx] = [
    //                     'item_fg_id'   => $item_fg_id,
    //                     'virgin'       => null,
    //                     'master_batch' => null,
    //                     'child_part'   => null
    //                 ];
    //             }

    //             $groups[$idx][$familyKey[$fam]] = $m;
    //             $familyCounter[$fam]++;
    //         }

    //         /* ================================
    //         * 7. BUILD RESULT ROWS
    //         * ================================ */
    //         foreach ($groups as $g) {
    //             $rows[] = [
    //                 'no' => $no++,
    //                 'p_month'           => $p_month,
    //                 'p_year'            => $p_year,
    //                 'model_name'        => $model_name,
    //                 'item_fg_id'        => $g['item_fg_id'],
    //                 'item_fg_number'    => $process['item_fg_number'] ?? '',
    //                 'item_fg_name'      => $process['item_fg_name'] ?? '',
    //                 'customer_id'       => $process['customer_id'] ?? '',
    //                 'customer_name'     => $process['customer_name'] ?? '',

    //                 // MATERIAL
    //                 'item_rm_id_vg'     => $g['virgin']['item_rm_id'] ?? '',
    //                 'part_no_vg'        => $g['virgin']['part_no'] ?? '',
    //                 'part_name_vg'      => $g['virgin']['part_name'] ?? '',
    //                 'used_vg'           => $g['virgin']['used'] ?? '',
    //                 'nett_vg'           => $g['virgin']['used'] ?? '', // belum perhitungan nett
    //                 'price_vg'          => $g['virgin']['price'] ?? '',
    //                 'virgin_cost'       => $g['virgin']['material_cost'] ?? 0,
    //                 // MB
    //                 'item_rm_id_mb'     => $g['master_batch']['item_rm_id'] ?? '',
    //                 'part_no_mb'        => $g['master_batch']['part_no'] ?? '',
    //                 'part_name_mb'      => $g['master_batch']['part_name'] ?? '',
    //                 'used_mb'           => $g['master_batch']['used'] ?? '',
    //                 'price_mb'          => $g['master_batch']['price'] ?? '',
    //                 'mb_cost'           => $g['master_batch']['material_cost'] ?? 0,
    //                 // CP
    //                 'item_rm_id_cp'     => $g['child_part']['item_rm_id'] ?? '',
    //                 'part_no_cp'        => $g['child_part']['part_no'] ?? '',
    //                 'part_name_cp'      => $g['child_part']['part_name'] ?? '',
    //                 'used_cp'           => $g['child_part']['used'] ?? '',
    //                 'price_cp'          => $g['child_part']['price'] ?? '',
    //                 'child_part_cost'   => $g['child_part']['material_cost'] ?? 0,

    //                 'total_material_cost'   => $material_total['total_material_cost'] ?? 0,

    //                 // PROCESS
    //                 'cycle_time'        => $process['cycle_time'] ?? 0,
    //                 'cycle_time_process'=> $process['cycle_time_process'] ?? 0,
    //                 'cavity_standard'   => $process['cavity_standard'] ?? 0,
    //                 'toonage'           => $process['toonage'] ?? 0,
    //                 'plain_rate_sec'    => $process['plain_rate_sec'] ?? 0,
    //                 'labour_cost'       => $process['labour_cost'] ?? 0,
    //                 'total_process_cost'=> $process['total_process_cost'] ?? 0,
    //                 'ng_ratio_cost'     => $process['ng_ratio_cost'] ?? 0,
    //                 'adm_foh_cost'      => $process['adm_foh_cost'] ?? 0,
    //                 'mtn_cost'          => $process['mtn_cost'] ?? 0,
    //                 'purging_value'     => $process['purging_value'] ?? 0,
    //                 'mold_depreciation' => $process['mold_depreciation'] ?? 0,
    //                 'depreciation'      => $process['depreciation'] ?? 0,
    //                 'mold_name'         => $process['mold_name'] ?? 0,
    //                 'mold_price'        => $process['mold_price'] ?? 0,
    //                 'profit_nominal'    => $process['profit_nominal'] ?? 0,
    //                 'moq'               => $process['moq'] ?? 0,
    //                 'volume'            => $process['volume'] ?? 0,
    //                 'purging_cost'      => $process['purging_cost'] ?? 0,
    //                 'start_setting'     => $process['start_setting'] ?? 0,

    //                 // PACKAGING TRANSPORTATION COSTS
    //                 'total_packing_cost'        => $ptc['adj_total_packing_cost'] ?? 0,
    //                 'transportasion_cost_pcs'   => $ptc['transportasion_cost_pcs'] ?? 0,

    //                 'revision'             => $revisions
    //             ];
    //         }
    //     }

    //     echo json_encode([
    //         'total' => count($rows),
    //         'rows'  => $rows
    //     ]);
    //     exit;
    // }

    public function getData()
    {
        if (!$this->input->get()) {
            show_error("Cannot process your request");
        }

        $p_month     = base64_decode($this->input->get('p_month'));
        $p_year      = base64_decode($this->input->get('p_year'));
        $revision    = base64_decode($this->input->get('revision'));
        $customer_id = base64_decode($this->input->get('customer_id'));
        $item_fg_id  = base64_decode($this->input->get('item_fg_id'));
        $model_name  = base64_decode($this->input->get('model_name'));

        /* =====================================================
        * 0. DETERMINE ITEM FG LIST (SINGLE / BULK GENERATE)
        * ===================================================== */
        $itemFgList = [];

        if (!empty($item_fg_id)) {
            // Generate single FG
            $itemFgList[] = $item_fg_id;
        } else {
            // Generate all FG by customer + period
            $itemFgList = $this->db
                ->distinct()
                ->select('item_fg_id')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('customer_id', $customer_id)
                ->get('process_costs')
                ->result_array();

            $itemFgList = array_column($itemFgList, 'item_fg_id');
        }

        /* =====================================================
        * PREVENT EMPTY DATA
        * ===================================================== */
        if (empty($itemFgList)) {
            echo json_encode(['total' => 0, 'rows' => []]);
            exit;
        }

        $rows = [];
        $no = 1;

        /* =====================================================
        * LOOP PER ITEM FG
        * ===================================================== */
        foreach ($itemFgList as $item_fg_id) {

            /* ================================
            * 1. GET MAX REVISION COST PATTERN
            * ================================ */
            $rev_cp = $this->db->select_max('revision')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('item_fg_id', $item_fg_id)
                ->where('customer_id', $customer_id)
                ->get('cost_patterns')
                ->row();

            $revisions = ($revision == "")
                ? (empty($rev_cp->revision) ? 0 : $rev_cp->revision + 1)
                : $revision;

            /* ================================
            * 2. DELETE OLD COST PATTERN
            * ================================ */
            $this->db->where([
                'p_month'      => $p_month,
                'p_year'       => $p_year,
                'revision'     => $revisions,
                'item_fg_id'   => $item_fg_id,
                'customer_id'  => $customer_id
            ])->delete('cost_patterns');

            /* ================================
            * 3. GET MAX REVISION MATERIAL COST
            * ================================ */
            $rev_mc = $this->db->select_max('revision')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('item_fg_id', $item_fg_id)
                ->get('material_costs')
                ->row()->revision;

            $materials = $this->db
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('revision', $rev_mc)
                ->where('item_fg_id', $item_fg_id)
                ->order_by('product_family', 'asc')
                ->order_by('id', 'asc')
                ->get('material_costs')
                ->result_array();

            $material_total = $this->db
                ->select('total_material_cost')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('revision', $rev_mc)
                ->where('item_fg_id', $item_fg_id)
                ->limit(1)
                ->get('material_costs')
                ->row_array();

            /* ================================
            * 4. GET MAX REVISION PROCESS COST
            * ================================ */
            $rev_pc = $this->db->select_max('revision')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('item_fg_id', $item_fg_id)
                ->where('customer_id', $customer_id)
                ->get('process_costs')
                ->row()->revision;

            $process = $this->db
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('revision', $rev_pc)
                ->where('item_fg_id', $item_fg_id)
                ->where('customer_id', $customer_id)
                ->get('process_costs')
                ->row_array();

            if (empty($process)) {
                continue; // skip jika tidak ada process
            }

            /* ================================
            * 5. GET PACKAGING & TRANSPORT
            * ================================ */
            $rev_ptc = $this->db->select_max('revision')
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('item_fg_id', $item_fg_id)
                ->where('customer_id', $customer_id)
                ->get('packaging_transportation_costs')
                ->row()->revision;

            $ptc = $this->db
                ->where('p_month', $p_month)
                ->where('p_year', $p_year)
                ->where('revision', $rev_ptc)
                ->where('item_fg_id', $item_fg_id)
                ->where('customer_id', $customer_id)
                ->get('packaging_transportation_costs')
                ->row_array();

            /* ================================
            * 6. AMBIL RUNNER & CAVITY
            * ================================ */
            $this->db->select('ml.runner, mld.cavity_standard');
            $this->db->from('menu_loadings ml');
            $this->db->join('molds mld', 'mld.id = ml.mold_id', 'left'); 
            $this->db->where('ml.item_fg_id', $item_fg_id);
            $loading = $this->db->get()->row_array();

            $runner = !empty($loading['runner']) ? (float)$loading['runner'] : 0;
            $cavity = (!empty($loading['cavity_standard']) && (float)$loading['cavity_standard'] > 0) ? (float)$loading['cavity_standard'] : 1;
            
            $runner_per_pcs = $runner / $cavity;

            /* ================================
            * 7. GROUPING MATERIAL COST
            * ================================ */
            $familyKey = [
                'VIRGIN'       => 'virgin',
                'MASTER BATCH' => 'master_batch',
                'CHILD PART'   => 'child_part'
            ];

            $familyCounter = [];
            $groups = [];

            foreach ($materials as $m) {
                $fam = $m['product_family'];

                if (!isset($familyCounter[$fam])) {
                    $familyCounter[$fam] = 0;
                }

                $idx = $familyCounter[$fam];

                if (!isset($groups[$idx])) {
                    $groups[$idx] = [
                        'item_fg_id'   => $item_fg_id,
                        'virgin'       => null,
                        'master_batch' => null,
                        'child_part'   => null
                    ];
                }

                $groups[$idx][$familyKey[$fam]] = $m;
                $familyCounter[$fam]++;
            }

            /* ================================
            * 8. BUILD RESULT ROWS
            * ================================ */
            foreach ($groups as $g) {
                $item_rm_id_vg = $g['virgin']['item_rm_id'] ?? '';
                $nett_vg = 0;

                // KALKULASI NETT VG HANYA JIKA ITEM_RM_ID_VG ADA
                if (!empty($item_rm_id_vg)) {
                    $bom = $this->db->select('comp_vg')
                        ->where('item_fg_id', $g['item_fg_id'])
                        ->where('item_rm_id', $item_rm_id_vg)
                        ->get('bom')
                        ->row_array();

                    $gross_vg = !empty($bom['comp_vg']) ? (float)$bom['comp_vg'] : 0;
                    $nett_vg  = $gross_vg - $runner_per_pcs;
                    
                    // Proteksi agar nilai nett tidak minus
                    $nett_vg  = $nett_vg < 0 ? 0 : $nett_vg;
                }

                $rows[] = [
                    'no' => $no++,
                    'p_month'           => $p_month,
                    'p_year'            => $p_year,
                    'model_name'        => $model_name,
                    'item_fg_id'        => $g['item_fg_id'],
                    'item_fg_number'    => $process['item_fg_number'] ?? '',
                    'item_fg_name'      => $process['item_fg_name'] ?? '',
                    'customer_id'       => $process['customer_id'] ?? '',
                    'customer_name'     => $process['customer_name'] ?? '',

                    // MATERIAL
                    'item_rm_id_vg'     => $item_rm_id_vg,
                    'part_no_vg'        => $g['virgin']['part_no'] ?? '',
                    'part_name_vg'      => $g['virgin']['part_name'] ?? '',
                    'used_vg'           => $g['virgin']['used'] ?? '',
                    'nett_vg'           => $nett_vg, // Hasil Kalkulasi Baru
                    'price_vg'          => $g['virgin']['price'] ?? '',
                    'virgin_cost'       => $g['virgin']['material_cost'] ?? 0,
                    // MB
                    'item_rm_id_mb'     => $g['master_batch']['item_rm_id'] ?? '',
                    'part_no_mb'        => $g['master_batch']['part_no'] ?? '',
                    'part_name_mb'      => $g['master_batch']['part_name'] ?? '',
                    'used_mb'           => $g['master_batch']['used'] ?? '',
                    'price_mb'          => $g['master_batch']['price'] ?? '',
                    'mb_cost'           => $g['master_batch']['material_cost'] ?? 0,
                    // CP
                    'item_rm_id_cp'     => $g['child_part']['item_rm_id'] ?? '',
                    'part_no_cp'        => $g['child_part']['part_no'] ?? '',
                    'part_name_cp'      => $g['child_part']['part_name'] ?? '',
                    'used_cp'           => $g['child_part']['used'] ?? '',
                    'price_cp'          => $g['child_part']['price'] ?? '',
                    'child_part_cost'   => $g['child_part']['material_cost'] ?? 0,

                    'total_material_cost'   => $material_total['total_material_cost'] ?? 0,

                    // PROCESS
                    'cycle_time'        => $process['cycle_time'] ?? 0,
                    'cycle_time_process'=> $process['cycle_time_process'] ?? 0,
                    'cavity_standard'   => $process['cavity_standard'] ?? 0,
                    'toonage'           => $process['toonage'] ?? 0,
                    'plain_rate_sec'    => $process['plain_rate_sec'] ?? 0,
                    'labour_cost'       => $process['labour_cost'] ?? 0,
                    'total_process_cost'=> $process['total_process_cost'] ?? 0,
                    'ng_ratio_cost'     => $process['ng_ratio_cost'] ?? 0,
                    'adm_foh_cost'      => $process['adm_foh_cost'] ?? 0,
                    'mtn_cost'          => $process['mtn_cost'] ?? 0,
                    'purging_value'     => $process['purging_value'] ?? 0,
                    'mold_depreciation' => $process['mold_depreciation'] ?? 0,
                    'depreciation'      => $process['depreciation'] ?? 0,
                    'mold_name'         => $process['mold_name'] ?? 0,
                    'mold_price'        => $process['mold_price'] ?? 0,
                    'profit_nominal'    => $process['profit_nominal'] ?? 0,
                    'moq'               => $process['moq'] ?? 0,
                    'volume'            => $process['volume'] ?? 0,
                    'purging_cost'      => $process['purging_cost'] ?? 0,
                    'start_setting'     => $process['start_setting'] ?? 0,

                    // PACKAGING TRANSPORTATION COSTS
                    'total_packing_cost'        => $ptc['adj_total_packing_cost'] ?? 0,
                    'transportasion_cost_pcs'   => $ptc['transportasion_cost_pcs'] ?? 0,

                    'revision'             => $revisions
                ];
            }
        }

        echo json_encode([
            'total' => count($rows),
            'rows'  => $rows
        ]);
        exit;
    }

    public function check_packaging_trans_cost()
    {
        $p_month = base64_decode($this->input->get('p_month'));
        $p_year = base64_decode($this->input->get('p_year'));

        //Select Query
        $this->db->select('*');
        $this->db->from('packaging_transportation_costs');
        // $this->db->where("approved_to = ''");
        if ($p_month != "" or $p_year != "") {
            $this->db->where('p_month', $p_month);
            $this->db->where('p_year', $p_year);
        }
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();
        
        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function check_material_cost()
    {
        $p_month = base64_decode($this->input->get('p_month'));
        $p_year = base64_decode($this->input->get('p_year'));

        //Select Query
        $this->db->select('*');
        $this->db->from('material_costs');
        // $this->db->where("approved_to = ''");
        if ($p_month != "" or $p_year != "") {
            $this->db->where('p_month', $p_month);
            $this->db->where('p_year', $p_year);
        }
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();
        
        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function check_process_cost()
    {
        $p_month = base64_decode($this->input->get('p_month'));
        $p_year = base64_decode($this->input->get('p_year'));

        //Select Query
        $this->db->select('*');
        $this->db->from('process_costs');
        // $this->db->where("approved_to = ''");
        if ($p_month != "" or $p_year != "") {
            $this->db->where('p_month', $p_month);
            $this->db->where('p_year', $p_year);
        }
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();
        
        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function check_rate()
    {
        $p_month = base64_decode($this->input->get('p_month'));
        $p_year = base64_decode($this->input->get('p_year'));

        //Select Query
        // $this->db->select('*');
        // $this->db->from('stock_fg');
        // $this->db->where("approved_to = ''");
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        // } else {
            // echo json_encode(array("theme" => "error"));
        // }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month_from = $this->input->get('filter_period_month_from');
            $filter_month_to = $this->input->get('filter_period_month_to');
            $filter_period_year = $this->input->get('filter_period_year');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_revision = $this->input->get('filter_revision');
            $filter_customer_id = $this->input->get('filter_customer_id');
        

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, c.uom");
            $this->db->from('cost_patterns a');
            $this->db->join('item_fg b','b.id = a.item_fg_id','left');
            $this->db->join('item_rm c','c.id = a.item_rm_id_vg','left');
            $this->db->where('a.deleted', 0);

            if ($filter_period_year != "") {
                $this->db->where('a.p_year', $filter_period_year);
            }

            if ($filter_month_from != "") {
                $this->db->where('a.p_month >=', $filter_month_from);
            }

            if ($filter_month_to != "") {
                $this->db->where('a.p_month <=', $filter_month_to);
            }

            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }

            if ($filter_revision != "") {
                $this->db->where('a.revision', $filter_revision);
            }

            if ($filter_customer_id != "") {
                $this->db->where('a.customer_id', $filter_customer_id);
            }
           
            $this->db->order_by('a.p_month', 'ASC');
            $this->db->order_by('a.p_year', 'ASC');
            $this->db->order_by('a.revision', 'ASC');
            $this->db->order_by('a.item_fg_id', 'ASC');
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

    // public function create()
    // {   
    //     if ($this->input->post()) {
    //         $post = $this->input->post('data');


    //         $generateLoadcap = $this->crud->reads('cost_patterns', [], [
    //             "p_month" => $post['p_month'],
    //             "p_year" => $post['p_year'],
    //             "revision" => $post['revision'],
    //             "customer_id" => $post['customer_id'],
    //             "item_fg_id" => $post['item_fg_id']
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
    //             $send   = $this->crud->update('cost_patterns', [
    //                 "p_month" => $post['p_month'],
    //                 "p_year" => $post['p_year'],
    //                 "revision" => $post['revision'],
    //                 "customer_id" => $post['customer_id'],
    //                 "item_fg_id" => $post['item_fg_id']
    //             ], $postFinal);
    //             echo $send;
    //         } else {
    //             $send = $this->crud->create('cost_patterns', $postFinal);
    //             echo $send;
    //         }
    //     }
    // }

    public function create()
    {
        $post = $this->input->post('data');

        if (empty($post)) {
            echo json_encode([
                'theme' => 'error',
                'title' => 'Failed',
                'message' => 'No data received'
            ]);
            return;
        }

        $postFinal = [
            'p_month'        => $post['p_month'] ?? null,
            'p_year'         => $post['p_year'] ?? null,
            'revision'       => $post['revision'] ?? 0,

            'item_fg_id'     => $post['item_fg_id'],
            'item_fg_number' => $post['item_fg_number'],
            'item_fg_name'   => $post['item_fg_name'],
            'customer_id'    => $post['customer_id'],
            'customer_name'  => $post['customer_name'],

            // ===== MATERIAL =====
            'item_rm_id_vg'  => $post['item_rm_id_vg'],
            'part_no_vg'     => $post['part_no_vg'],
            'part_name_vg'   => $post['part_name_vg'],
            'used_vg'        => $post['used_vg'],
            'nett_vg'        => $post['nett_vg'],
            'price_vg'       => $post['price_vg'],
            'virgin_cost'    => $post['virgin_cost'],

            'item_rm_id_mb'  => $post['item_rm_id_mb'],
            'part_no_mb'     => $post['part_no_mb'],
            'part_name_mb'   => $post['part_name_mb'],
            'used_mb'        => $post['used_mb'],
            'price_mb'       => $post['price_mb'],
            'mb_cost'        => $post['mb_cost'],

            'item_rm_id_cp'  => $post['item_rm_id_cp'],
            'part_no_cp'     => $post['part_no_cp'],
            'part_name_cp'   => $post['part_name_cp'],
            'used_cp'        => $post['used_cp'],
            'price_cp'       => $post['price_cp'],
            'child_part_cost'=> $post['child_part_cost'],

            'total_material_cost'=> $post['total_material_cost'],

            // ===== PROCESS =====
            'cycle_time'          => $post['cycle_time'],
            'cavity_standard'     => $post['cavity_standard'],
            'toonage'             => $post['toonage'],
            'plain_rate_sec'      => $post['plain_rate_sec'],
            'labour_cost'         => $post['labour_cost'],
            'total_process_cost'  => $post['total_process_cost'],
            'ng_ratio_cost'       => $post['ng_ratio_cost'],
            'adm_foh_cost'        => $post['adm_foh_cost'],
            'mtn_cost'            => $post['mtn_cost'],
            'purging_value'       => $post['purging_value'],
            'mold_depreciation'   => $post['mold_depreciation'],
            'depreciation'        => $post['depreciation'],
            'mold_name'           => $post['mold_name'],
            'mold_price'          => $post['mold_price'],
            'profit_nominal'      => $post['profit_nominal'],
            'moq'                 => $post['moq'],
            'volume'              => $post['volume'],
            'purging_cost'        => $post['purging_cost'],
            'start_setting'       => $post['start_setting'],

            // ===== PACKAGING =====
            'total_packing_cost'      => $post['total_packing_cost'],
            'transportasion_cost_pcs' => $post['transportasion_cost_pcs'],

            'sub_total' => $post['total_process_cost'] + $post['total_material_cost'],
            'grand_total' => $post['total_process_cost'] + $post['total_material_cost'] + $post['ng_ratio_cost'] + $post['adm_foh_cost'] + $post['mtn_cost'] + $post['total_packing_cost'] + $post['transportasion_cost_pcs'] + $post['purging_value'] + $post['mold_depreciation'] + $post['profit_nominal'] ,
        ];

        $insert = $this->crud->create('cost_patterns', $postFinal);

        if ($insert) {
            echo json_encode([
                'theme' => 'success',
                'title' => 'Success',
                'message' => 'Cost Pattern saved'
            ]);
        } else {
            echo json_encode([
                'theme' => 'error',
                'title' => 'Failed',
                'message' => 'Insert failed'
            ]);
        }
    }

    public function delete()
    {
        $ids = $this->input->post('ids');
        if (!empty($ids) && is_array($ids)) {
            $this->db->trans_start();
            
            $success = true;
            
            foreach ($ids as $id) {
                $send = $this->crud->delete('cost_patterns', ["id" => $id]);
                if (!$send) {
                    $success = false;
                }
            }
            
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE || !$success) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete some/all data']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No ID received']);
        }
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
                'item_fg_number'            => $data->val($i, 2),
                'customer_name'             => $data->val($i, 3),
                'model_name'                => $data->val($i, 4),
                'p_month'                   => $data->val($i, 5),
                'p_year'                    => $data->val($i, 6),
                'revision'                  => $data->val($i, 7),

                // Material Virgin (VG)
                'part_no_vg'                => $data->val($i, 8),
                'used_vg'                   => $data->val($i, 9),
                'nett_vg'                   => $data->val($i, 10),
                'price_vg'                  => $data->val($i, 11),
                'virgin_cost'               => $data->val($i, 12),
                
                // Material Master Batch (MB)
                'part_no_mb'                => $data->val($i, 13),
                'used_mb'                   => $data->val($i, 14),
                'price_mb'                  => $data->val($i, 15),
                'mb_cost'                   => $data->val($i, 16),
                
                // Child Part (CP)
                'part_no_cp'                => $data->val($i, 17),
                'used_cp'                   => $data->val($i, 18),
                'price_cp'                  => $data->val($i, 19),
                'child_part_cost'           => $data->val($i, 20),
                
                // Cost & Process Parameters
                'cycle_time'                => $data->val($i, 21),
                'cycle_time_process'        => $data->val($i, 22),
                'cavity_standard'           => $data->val($i, 23),
                'toonage'                   => $data->val($i, 24),
                'plain_rate_sec'            => $data->val($i, 25),
                'labour_cost'               => $data->val($i, 26),
                'total_material_cost'       => $data->val($i, 27),
                'total_process_cost'        => $data->val($i, 28),
                'ng_ratio_cost'             => $data->val($i, 29),
                'adm_foh_cost'              => $data->val($i, 30),
                'mtn_cost'                  => $data->val($i, 31),
                'purging_value'             => $data->val($i, 32),
                'purging_cost'              => $data->val($i, 33),
                'start_setting'             => $data->val($i, 34),
                
                // Mold & Depreciation
                'mold_depreciation'         => $data->val($i, 35),
                'depreciation'              => $data->val($i, 36),
                'mold_name'                 => $data->val($i, 37),
                'mold_price'                => $data->val($i, 38),
            
                // Final Calculation Parameters
                'profit_nominal'            => $data->val($i, 39),
                'moq'                       => $data->val($i, 40),
                'volume'                    => $data->val($i, 41),
                'total_packing_cost'        => $data->val($i, 42),
                'transportasion_cost_pcs'   => $data->val($i, 43)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/cost_pattern.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/cost_pattern.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/cost_pattern.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
    //         $customer = $this->crud->read('customers', [], ["name" => $data['customer_name']]);
    //         $item_rm_vg = $this->crud->read('item_rm', [], ["number" => $data['part_no_vg']]);
    //         $item_rm_mb = $this->crud->read('item_rm', [], ["number" => $data['part_no_mb']]);
    //         $item_rm_cp = $this->crud->read('item_rm', [], ["number" => $data['part_no_cp']]);

    //         if (empty($customer->name)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Customer Name " . $data['customer_name'] . " is Not Found", "theme" => "error"));
    //         } else if (empty($item_fg->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Product Number " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
    //         } else if (!empty($data['part_no_vg']) && empty($item_rm_vg->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Virgin Part Number " . $data['part_no_vg'] . " is Not Found", "theme" => "error"));
    //         } else if (!empty($data['part_no_mb']) && empty($item_rm_mb->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "MB Part Number " . $data['part_no_mb'] . " is Not Found", "theme" => "error"));
    //         } else if (!empty($data['part_no_cp']) && empty($item_rm_cp->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Child Part Number " . $data['part_no_cp'] . " is Not Found", "theme" => "error"));
    //         } else {
    //             $dataFinal = array(
    //                 // Product & Customer Info
    //                 "item_fg_id"              => $item_fg->id,
    //                 "item_fg_number"          => $data['item_fg_number'],
    //                 "item_fg_name"            => $item_fg->name,
    //                 "customer_id"             => $customer->id,
    //                 "customer_name"           => $data['customer_name'],
                    
    //                 // Month, Year, Revision
    //                 "p_month"                 => $data['p_month'],
    //                 "p_year"                  => $data['p_year'],
    //                 "revision"                => $data['revision'],
                    
    //                 // Virgin Material (VG)
    //                 "item_rm_id_vg"           => $item_rm_vg->id,
    //                 "part_no_vg"              => $data['part_no_vg'],
    //                 "part_name_vg"            => $item_rm_vg->name,
    //                 "used_vg"                 => $data['used_vg'],
    //                 "price_vg"                => $data['price_vg'],
    //                 "virgin_cost"             => $data['virgin_cost'],
                    
    //                 // Master Batch (MB)
    //                 "item_rm_id_mb"           => $item_rm_mb->id,
    //                 "part_no_mb"              => $data['part_no_mb'],
    //                 "part_name_mb"            => $item_rm_mb->name,
    //                 "used_mb"                 => $data['used_mb'],
    //                 "price_mb"                => $data['price_mb'],
    //                 "mb_cost"                 => $data['mb_cost'],
                    
    //                 // Child Part (CP)
    //                 "item_rm_id_cp"           => $item_rm_cp->id,
    //                 "part_no_cp"              => $data['part_no_cp'],
    //                 "part_name_cp"            => $item_rm_cp->name,
    //                 "used_cp"                 => $data['used_cp'],
    //                 "price_cp"                => $data['price_cp'],
    //                 "child_part_cost"         => $data['child_part_cost'],
                    
    //                 // Cost & Process Parameters
    //                 "cycle_time"              => $data['cycle_time'],
    //                 "cycle_time_process"      => $data['cycle_time_process'],
    //                 "cavity_standard"         => $data['cavity_standard'],
    //                 "toonage"                 => $data['toonage'],
    //                 "plain_rate_sec"          => $data['plain_rate_sec'],
    //                 "labour_cost"             => $data['labour_cost'],
    //                 "total_material_cost"     => $data['total_material_cost'],
    //                 "total_process_cost"      => $data['total_process_cost'],
    //                 "ng_ratio_cost"           => $data['ng_ratio_cost'],
    //                 "adm_foh_cost"            => $data['adm_foh_cost'],
    //                 "mtn_cost"                => $data['mtn_cost'],
    //                 "purging_value"           => $data['purging_value'],
    //                 "purging_cost"            => $data['purging_cost'],
    //                 "start_setting"           => $data['start_setting'],
                    
    //                 // Mold & Depreciation
    //                 "mold_depreciation"       => $data['mold_depreciation'],
    //                 "depreciation"            => $data['depreciation'],
    //                 "mold_name"               => $data['mold_name'],
    //                 "mold_price"              => $data['mold_price'],
                    
    //                 // Final Calculation
    //                 "profit_nominal"          => $data['profit_nominal'],
    //                 "moq"                     => $data['moq'],
    //                 "volume"                  => $data['volume'],
    //                 "total_packing_cost"      => $data['total_packing_cost'],
    //                 "transportasion_cost_pcs" => $data['transportasion_cost_pcs'],
    //                 "sub_total"               => $data['total_process_cost'] + $data['total_material_cost'],
    //                 "grand_total"             => $data['total_process_cost'] + $data['total_material_cost'] + $data['ng_ratio_cost'] + $data['adm_foh_cost'] + $data['mtn_cost'] + $data['total_packing_cost'] + $data['transportasion_cost_pcs'] + $data['purging_value'] + $data['mold_depreciation'] + $data['profit_nominal'],
    //                 "model_name"              => $data['model_name']
    //             );
    //             $send   = $this->crud->create('cost_patterns', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $customer = $this->crud->read('customers', [], ["name" => $data['customer_name']]);
            
            $item_rm_vg = !empty($data['part_no_vg']) ? $this->crud->read('item_rm', [], ["number" => $data['part_no_vg']]) : null;
            $item_rm_mb = !empty($data['part_no_mb']) ? $this->crud->read('item_rm', [], ["number" => $data['part_no_mb']]) : null;
            $item_rm_cp = !empty($data['part_no_cp']) ? $this->crud->read('item_rm', [], ["number" => $data['part_no_cp']]) : null;

            $cek_duplicate = null;
            if (!empty($item_fg->id)) {
                $cek_duplicate = $this->crud->read('cost_patterns', [], [
                    "item_fg_id" => $item_fg->id,
                    "revision"   => $data['revision'],
                    "p_month"    => $data['p_month'],
                    "p_year"     => $data['p_year']
                ]);
            }

            if (empty($customer->name)) {
                echo json_encode(array("title" => "Not Found", "message" => " Customer Name " . $data['customer_name'] . " is Not Found", "theme" => "error"));
            } else if (empty($item_fg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Product Number " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
            } else if (!empty($data['part_no_vg']) && empty($item_rm_vg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Virgin Part Number " . $data['part_no_vg'] . " is Not Found", "theme" => "error"));
            } else if (!empty($data['part_no_mb']) && empty($item_rm_mb->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "MB Part Number " . $data['part_no_mb'] . " is Not Found", "theme" => "error"));
            } else if (!empty($data['part_no_cp']) && empty($item_rm_cp->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Child Part Number " . $data['part_no_cp'] . " is Not Found", "theme" => "error"));
            
            } else if (!empty($cek_duplicate)) { 
                echo json_encode(array(
                    "title"   => "Duplicate Data", 
                    "message" => "Cost Pattern for Item " . $data['item_fg_number'] . " in Period " . $data['p_month'] . " " . $data['p_year'] . " and Revision " . $data['revision'] . " already exists!", 
                    "theme"   => "error"
                ));

            } else {
                
                $num = function($val) {
                    return (float) str_replace(',', '.', $val ?? 0);
                };

                $dataFinal = array(
                    // Product & Customer Info
                    "item_fg_id"              => $item_fg->id,
                    "item_fg_number"          => $data['item_fg_number'],
                    "item_fg_name"            => $item_fg->name,
                    "customer_id"             => $customer->id,
                    "customer_name"           => $data['customer_name'],
                    
                    // Month, Year, Revision
                    "p_month"                 => $data['p_month'],
                    "p_year"                  => $data['p_year'],
                    "revision"                => $data['revision'],
                    
                    // Virgin Material (VG)
                    "item_rm_id_vg"           => !empty($item_rm_vg) ? $item_rm_vg->id : null,
                    "part_no_vg"              => $data['part_no_vg'],
                    "part_name_vg"            => !empty($item_rm_vg) ? $item_rm_vg->name : null,
                    "used_vg"                 => $num($data['used_vg']),
                    "nett_vg"                 => $num($data['nett_vg']),
                    "price_vg"                => $num($data['price_vg']),
                    "virgin_cost"             => $num($data['virgin_cost']),
                    
                    // Master Batch (MB)
                    "item_rm_id_mb"           => !empty($item_rm_mb) ? $item_rm_mb->id : null,
                    "part_no_mb"              => $data['part_no_mb'],
                    "part_name_mb"            => !empty($item_rm_mb) ? $item_rm_mb->name : null,
                    "used_mb"                 => $num($data['used_mb']),
                    "price_mb"                => $num($data['price_mb']),
                    "mb_cost"                 => $num($data['mb_cost']),
                    
                    // Child Part (CP)
                    "item_rm_id_cp"           => !empty($item_rm_cp) ? $item_rm_cp->id : null,
                    "part_no_cp"              => $data['part_no_cp'],
                    "part_name_cp"            => !empty($item_rm_cp) ? $item_rm_cp->name : null,
                    "used_cp"                 => $num($data['used_cp']),
                    "price_cp"                => $num($data['price_cp']),
                    "child_part_cost"         => $num($data['child_part_cost']),
                    
                    // Cost & Process Parameters
                    "cycle_time"              => $num($data['cycle_time']),
                    "cycle_time_process"      => $num($data['cycle_time_process']),
                    "cavity_standard"         => $num($data['cavity_standard']),
                    "toonage"                 => $num($data['toonage']),
                    "plain_rate_sec"          => $num($data['plain_rate_sec']),
                    "labour_cost"             => $num($data['labour_cost']),
                    "total_material_cost"     => $num($data['total_material_cost']),
                    "total_process_cost"      => $num($data['total_process_cost']),
                    "ng_ratio_cost"           => $num($data['ng_ratio_cost']),
                    "adm_foh_cost"            => $num($data['adm_foh_cost']),
                    "mtn_cost"                => $num($data['mtn_cost']),
                    "purging_value"           => $num($data['purging_value']),
                    "purging_cost"            => $num($data['purging_cost']),
                    "start_setting"           => $num($data['start_setting']),
                    
                    // Mold & Depreciation
                    "mold_depreciation"       => $num($data['mold_depreciation']),
                    "depreciation"            => $data['depreciation'], // Bukan angka
                    "mold_name"               => $data['mold_name'], // Bukan angka
                    "mold_price"              => $num($data['mold_price']),
                    
                    // Final Calculation
                    "profit_nominal"          => $num($data['profit_nominal']),
                    "moq"                     => $num($data['moq']),
                    "volume"                  => $num($data['volume']),
                    "total_packing_cost"      => $num($data['total_packing_cost']),
                    "transportasion_cost_pcs" => $num($data['transportasion_cost_pcs']),
                    
                    "sub_total"               => $num($data['total_process_cost']) + $num($data['total_material_cost']),
                    "grand_total"             => $num($data['total_process_cost']) + $num($data['total_material_cost']) + $num($data['ng_ratio_cost']) + $num($data['adm_foh_cost']) + $num($data['mtn_cost']) + $num($data['total_packing_cost']) + $num($data['transportasion_cost_pcs']) + $num($data['purging_value']) + $num($data['mold_depreciation']) + $num($data['profit_nominal']),
                    "model_name"              => $data['model_name']
                );
                
                $send   = $this->crud->create('cost_patterns', $dataFinal);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=cost_pattern_$format.xls");
        }

        $filter_month_from = $this->input->get('filter_period_month_from');
        $filter_month_to = $this->input->get('filter_period_month_to');
        $filter_period_year = $this->input->get('filter_period_year');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_revision = $this->input->get('filter_revision');
        $filter_customer_id = $this->input->get('filter_customer_id');
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, c.uom");
        $this->db->from('cost_patterns a');
        $this->db->join('item_fg b','b.id = a.item_fg_id');
        $this->db->join('item_rm c','c.id = a.item_rm_id_vg');
        $this->db->where('a.deleted', 0);

        if ($filter_period_year != "") {
            $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_month_from != "") {
            $this->db->where('a.p_month >=', $filter_month_from);
        }

        if ($filter_month_to != "") {
            $this->db->where('a.p_month <=', $filter_month_to);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }

        if ($filter_customer_id != "") {
            $this->db->where('a.customer_id', $filter_customer_id);
        }
        
        $this->db->order_by('a.p_month', 'ASC');
        $this->db->order_by('a.p_year', 'ASC');
        $this->db->order_by('a.revision', 'ASC');
        $this->db->order_by('a.item_fg_id', 'ASC');
        $records = $this->db->get()->result_array();

        $grouped = [];
        foreach ($records as $row) {
            $key = $row['item_fg_id'].'|'.$row['p_month'].'|'.$row['p_year'].'|'.$row['revision'];
            $grouped[$key][] = $row;
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
                                <small>COST PATTERN</small>
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
                    <th rowspan="2" width="25">No</th>
                    <th rowspan="2" width="30">Part No</th>
                    <th rowspan="2" width="35">Part Name</th>
                    <th rowspan="2" width="150">Customer Name</th>
                    <th rowspan="2" width="30">Month</th>
                    <th rowspan="2" width="35">Year</th>
                    <th rowspan="2" width="25">Rev</th>
                    <th colspan="3" width="160">MATERIAL SPEC</th>
                    <th colspan="5" width="160">MATERIAL USED</th>
                    <th colspan="3" width="160">MATERIAL PRICE</th>
                    <th colspan="4" width="200">MATERIAL COST</th>
                    <th colspan="6" width="250">PROCESS COST</th>
                    <th rowspan="2" width="50">TOTAL</th>
                    <th rowspan="2" width="50">NG Cost</th>
                    <th rowspan="2" width="50">ADM/FOH</th>
                    <th rowspan="2" width="50">MTN</th>
                    <th rowspan="2" width="50">Packaging</th>
                    <th rowspan="2" width="50">Transport</th>
                    <th rowspan="2" width="50">Purging</th>
                    <th rowspan="2" width="50">Mold</th>
                    <th rowspan="2" width="50">Profit</th>
                    <th rowspan="2" width="100">GRAND TOTAL</th>
                    <th rowspan="2" width="50">MOQ</th>
                    <th rowspan="2" width="50">Vol</th>
                    <th rowspan="2" width="100">Purging Cost</th>
                    <th rowspan="2" width="100">Start Setting</th>
                </tr>
                <tr>
                    <th>Material Name</th>
                    <th>MB Name</th>
                    <th>CP Name</th>

                    <th>Gross</th>
                    <th>Nett</th>
                    <th>Uom</th>
                    <th>MB</th>
                    <th>CP</th>

                    <th>Mat</th>
                    <th>MB</th>
                    <th>CP</th>

                    <th>Mat</th>
                    <th>MB</th>
                    <th>CP</th>
                    <th>TOTAL 1</th>

                    <th>CT</th>
                    <th>CAV</th>
                    <th>Ton</th>
                    <th>Rate</th>
                    <th>2nd</th>
                    <th>TOTAL 2</th>
                </tr>';
        $no = 1;
        foreach ($grouped as $group) {
            $rowspan = count($group);
            $first   = true;

            foreach ($group as $data) {
                    $grand_total =$data['total_process_cost'] + $data['total_material_cost'] + $data['ng_ratio_cost'] + $data['adm_foh_cost'] + $data['mtn_cost'] + $data['total_packing_cost'] + $data['transportasion_cost_pcs'] + $data['purging_value'] + $data['mold_depreciation'] + $data['profit_nominal'];
                    $html .= '<tr>';
                    if ($first) {
                        $html .= '<td rowspan="'.$rowspan.'" align="center">'.$no.'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['item_fg_number'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['item_fg_name'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['customer_name'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['p_month'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['p_year'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['revision'].'</td>';
                    }

                    $html .= '<td>'.$data['part_name_vg'].'</td>
                            <td>'.$data['part_name_mb'].'</td>
                            <td>'.$data['part_name_cp'].'</td>
                            <td align="right">'.number_format($data['used_vg'],4).'</td>
                            <td align="center">'.$data['nett_vg'].'</td>
                            <td align="center">'.$data['uom'].'</td>
                            <td align="right">'.number_format($data['used_mb'],4).'</td>
                            <td align="right">'.number_format($data['used_cp'],4).'</td>

                            <td align="right">'.number_format($data['price_vg'],2).'</td>
                            <td align="right">'.number_format($data['price_mb'],2).'</td>
                            <td align="right">'.number_format($data['price_cp'],2).'</td>

                            <td align="right">'.number_format($data['virgin_cost'],4).'</td>
                            <td align="right">'.number_format($data['mb_cost'],4).'</td>
                            <td align="right">'.number_format($data['child_part_cost'],4).'</td>
                            <td align="right">'.number_format($data['total_material_cost'],4).'</td>';

                    if ($first) {
                        $html .= '<td rowspan="'.$rowspan.'" align="right">'.number_format($data['cycle_time'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['cavity_standard'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['toonage'].'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['plain_rate_sec'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['cycle_time_process'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['total_process_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['sub_total'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['ng_ratio_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['adm_foh_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['mtn_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['total_packing_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['transportasion_cost_pcs'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['purging_value'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['mold_depreciation'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['profit_nominal'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($grand_total).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['moq'],2).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['volume'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['purging_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['start_setting'],4).'</td>';
                    }
                    $html .= '</tr>';
                    $first = false;
                }

            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_cp($p_month, $p_year, $item_fg_id, $revision)
    {
        $p_month    = base64_decode($p_month);
        $p_year     = base64_decode($p_year);
        $item_fg_id = base64_decode($item_fg_id);
        $revision   = base64_decode($revision);

        $cost_patterns = $this->crud->reads('cost_patterns', [], ["p_month" => $p_month, "p_year" => $p_year, "item_fg_id" => $item_fg_id, "revision" => $revision]);
        $cost_pattern  = $this->crud->read('cost_patterns', [], ["p_month" => $p_month, "p_year" => $p_year, "item_fg_id" => $item_fg_id, "revision" => $revision]);

        if (!$cost_pattern) {
            die("Data tidak ditemukan.");
        }

        $approval = $this->crud->read('approvals', [], ["table_name" => "cost_patterns"]);
        $user_0 = $this->crud->read('users', [], ["username" => $cost_pattern->created_by]); // Prepared
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]); // Checked
        $user_2 = (!empty($approval->user_approval_2)) ? $this->crud->read('users', [], ["username" => $approval->user_approval_2]) : (object) ["name" => ""]; // Approved

        $users_0 = $users_1 = $users_2 = '';
        
        if ($cost_pattern->approved >= 1) {
            $this->createQrcode($user_0->name, "assets/image/qrcode/");
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $user_0->name . '.png') . '" width="70"/>';
        }
        if ($cost_pattern->approved >= 2) {
            $this->createQrcode($user_1->name, "assets/image/qrcode/");
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="70"/>';
        }
        if ($cost_pattern->approved >= 3) {
            $this->createQrcode($user_2->name, "assets/image/qrcode/");
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="70"/>';
        }

        $config = $this->db->get('config')->row();
        $rows_per_page = 20;
        $total_pages = ceil(count($cost_patterns) / $rows_per_page);

        $html = '<html><head><title>Cost Pattern - ' . $cost_pattern->item_fg_number . '</title>';
        $html .= '<link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">';
        $html .= '<style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
                    #customers { border-collapse: collapse; width: 100%; font-size: 9px; table-layout: fixed; }
                    #customers td, #customers th { border: 1px solid black; padding: 2px; word-wrap: break-word; }
                    #customers th { background-color: #f2f2f2; text-align: center; }
                    .header-table { width: 100%; font-size: 11px; margin-bottom: 10px; }
                    .title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 10px; }
                    @media screen { .print-area { display: none; } .instruction { margin: 10% auto; text-align: center; } }
                    @media print { .instruction { display: none; } .print-area { display: block; } @page { size: A3 landscape; margin: 0.5cm; } }
                </style></head><body>';

        $html .= '<div class="instruction">
                    <h1>Press CTRL + P to Print</h1>
                    <p>Layout: Landscape | Paper: A3 | Scale: 98% atau Fit to Page</p>
                </div>';

        $html .= '<div class="print-area">';

        for ($i = 0; $i < $total_pages; $i++) {
            // Fetch limited rows for current page
            $this->db->select('a.*, c.uom');
            $this->db->from('cost_patterns a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id_vg = c.id');
            $this->db->where(['a.p_month' => $p_month, 'a.p_year' => $p_year, 'a.item_fg_id' => $item_fg_id, 'a.revision' => $revision]);
            $this->db->limit($rows_per_page, ($i * $rows_per_page));
            $records = $this->db->get()->result_array();

            // Grouping logic for rowspan
            $grouped = [];
            foreach ($records as $row) {
                $key = $row['item_fg_id'].'|'.$row['p_month'].'|'.$row['p_year'].'|'.$row['revision'];
                $grouped[$key][] = $row;
            }

            $html .= '<div style="page-break-after: always; padding: 10px;">';
            $html .= '<div class="title">COST PATTERN</div>';

            // TOP SECTION: INFO & APPROVAL
            $html .= '<table class="header-table">
                        <tr>
                            <td width="50%" valign="top">
                                <table style="font-size: 10px;">
                                    <tr><td>Print Date</td><td>: ' . date("Y-m-d H:i") . '</td></tr>
                                    <tr><td>Print By</td><td>: ' . $this->session->name . '</td></tr>
                                </table>
                            </td>
                            <td width="50%" align="right">
                                <table id="customers" style="width: 300px; text-align: center;">
                                    <tr>
                                        <th>APPROVED</th>
                                        <th>CHECKED</th>
                                        <th>PREPARED</th>
                                    </tr>
                                    <tr>
                                        <td height="60">' . $users_2 . '</td>
                                        <td height="60">' . $users_1 . '</td>
                                        <td height="60">' . $users_0 . '</td>
                                    </tr>
                                    <tr style="font-weight: bold;">
                                        <td>' . ($user_2->name ?: '-') . '</td>
                                        <td>' . ($user_1->name ?: '-') . '</td>
                                        <td>' . ($user_0->name ?: '-') . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>';

            // MAIN TABLE DATA
            $html .= '<table id="customers">
                        <thead>
                            <tr>
                                <th rowspan="2" width="25">No</th>
                                <th rowspan="2" width="30">Part No</th>
                                <th rowspan="2" width="35">Part Name</th>
                                <th rowspan="2" width="150">Customer Name</th>
                                <th rowspan="2" width="30">Month</th>
                                <th rowspan="2" width="35">Year</th>
                                <th rowspan="2" width="25">Rev</th>
                                <th colspan="3" width="160">MATERIAL SPEC</th>
                                <th colspan="5" width="160">MATERIAL USED</th>
                                <th colspan="3" width="160">MATERIAL PRICE</th>
                                <th colspan="4" width="200">MATERIAL COST</th>
                                <th colspan="6" width="250">PROCESS COST</th>
                                <th rowspan="2" width="50">TOTAL</th>
                                <th rowspan="2" width="50">NG Cost</th>
                                <th rowspan="2" width="50">ADM/FOH</th>
                                <th rowspan="2" width="50">MTN</th>
                                <th rowspan="2" width="50">Packaging</th>
                                <th rowspan="2" width="50">Transport</th>
                                <th rowspan="2" width="50">Purging</th>
                                <th rowspan="2" width="50">Mold</th>
                                <th rowspan="2" width="50">Profit</th>
                                <th rowspan="2" width="100">GRAND TOTAL</th>
                                <th rowspan="2" width="50">MOQ</th>
                                <th rowspan="2" width="50">Vol</th>
                                <th rowspan="2" width="100">Purging Cost</th>
                                <th rowspan="2" width="100">Start Setting</th>
                            </tr>
                            <tr>
                                <th>Material Name</th>
                                <th>MB Name</th>
                                <th>CP Name</th>

                                <th>Gross</th>
                                <th>Nett</th>
                                <th>Uom</th>
                                <th>MB</th>
                                <th>CP</th>

                                <th>Mat</th>
                                <th>MB</th>
                                <th>CP</th>

                                <th>Mat</th>
                                <th>MB</th>
                                <th>CP</th>
                                <th>TOTAL 1</th>

                                <th>CT</th>
                                <th>CAV</th>
                                <th>Ton</th>
                                <th>Rate</th>
                                <th>2nd</th>
                                <th>TOTAL 2</th>
                            </tr>
                        </thead>
                        <tbody>';

            $no = ($i * $rows_per_page) + 1;
            foreach ($grouped as $group) {
                $rowspan = count($group);
                $first = true;

                foreach ($group as $data) {
                    $grand_total =$data['total_process_cost'] + $data['total_material_cost'] + $data['ng_ratio_cost'] + $data['adm_foh_cost'] + $data['mtn_cost'] + $data['total_packing_cost'] + $data['transportasion_cost_pcs'] + $data['purging_value'] + $data['mold_depreciation'] + $data['profit_nominal'];
                    $html .= '<tr>';
                    if ($first) {
                        $html .= '<td rowspan="'.$rowspan.'" align="center">'.$no.'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['item_fg_number'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['item_fg_name'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['customer_name'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['p_month'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['p_year'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['revision'].'</td>';
                    }

                    $html .= '<td>'.$data['part_name_vg'].'</td>
                            <td>'.$data['part_name_mb'].'</td>
                            <td>'.$data['part_name_cp'].'</td>
                            <td align="right">'.number_format($data['used_vg'],4).'</td>
                            <td align="center">'.$data['nett_vg'].'</td>
                            <td align="center">'.$data['uom'].'</td>
                            <td align="right">'.number_format($data['used_mb'],4).'</td>
                            <td align="right">'.number_format($data['used_cp'],4).'</td>

                            <td align="right">'.number_format($data['price_vg'],2).'</td>
                            <td align="right">'.number_format($data['price_mb'],2).'</td>
                            <td align="right">'.number_format($data['price_cp'],2).'</td>

                            <td align="right">'.number_format($data['virgin_cost'],4).'</td>
                            <td align="right">'.number_format($data['mb_cost'],4).'</td>
                            <td align="right">'.number_format($data['child_part_cost'],4).'</td>
                            <td align="right">'.number_format($data['total_material_cost'],4).'</td>';

                    if ($first) {
                        $html .= '<td rowspan="'.$rowspan.'" align="right">'.number_format($data['cycle_time'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['cavity_standard'].'</td>
                                <td rowspan="'.$rowspan.'" align="center">'.$data['toonage'].'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['plain_rate_sec'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['cycle_time_process'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['total_process_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['sub_total'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['ng_ratio_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['adm_foh_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['mtn_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['total_packing_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['transportasion_cost_pcs'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['purging_value'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['mold_depreciation'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['profit_nominal'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($grand_total).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['moq'],2).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['volume'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['purging_cost'],4).'</td>
                                <td rowspan="'.$rowspan.'" align="right">'.number_format($data['start_setting'],4).'</td>';
                    }
                    $html .= '</tr>';
                    $first = false;
                }
                $no++;
            }
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        $html .= '</div><script>window.print();</script></body></html>';
        die($html);
    }
}
