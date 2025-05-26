<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mpp extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);
        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mpp.product_no]');
    }

    public function index(){
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

    public function check_calendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        //Select Query
        $this->db->select('*');
        $this->db->from('calendars');
        $this->db->like("working_date", $filter_year."-".$filter_month);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function readRevisions($month, $year){
        $this->db->select('revision');
        $this->db->from('generate_mps');
        $this->db->where('p_month', $month);
        $this->db->where('p_year', $year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        die(json_encode($revisions));
    }

    public function datatableNotMps(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $this->db->select('a.item_fg_id, a.prod_plan, c.number as item_fg_number, c.name as item_fg_name, b.name as customer_name');
        $this->db->from('generate_mps_details a');
        $this->db->join('customers b', 'a.customer_id = b.number');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.prod_plan >', 0);
        $this->db->group_by('c.number');
        $this->db->order_by('b.name', 'asc');
        $records = $this->db->get()->result_array();

        $data = array();
        foreach ($records as $record) {
            $this->db->select('*');
            $this->db->from('generate_mpp');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('item_fg_id', $record['item_fg_id']);
            $this->db->group_by('revision');
            $this->db->order_by('revision', 'desc');
            $mpp = $this->db->get()->row();

            if(empty($mpp->product_no)){
                $data[] = array(
                    "item_fg_number" => $record['item_fg_number'],
                    "item_fg_name" => $record['item_fg_name'],
                    "customer_name" => $record['customer_name'],
                    "prod_plan" => $record['prod_plan'],
                );
            }
        }

        die(json_encode($data));
    }

    public function datatables(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

        // $this->db->select('revision');
        // $this->db->from('generate_mpp');
        // $this->db->where('p_month', $filter_month);
        // $this->db->where('p_year', $filter_year);
        // $this->db->group_by('revision');
        // $this->db->order_by('revision', 'desc');
        // $revisions = $this->db->get()->row();

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, a.prod_plan as mpsprod, e.number as item_fg_number, e.name as item_fg_name, e.number_customer, e.lot, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating, b.name as customer_name, b.line_id');
        $this->db->from('generate_mpp a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        // $this->db->join('generate_mps c', "a.p_month = c.p_month and a.p_year = c.p_year and c.revision = '$filter_revision' and a.item_fg_id = c.item_fg_id");
        // $this->db->join("(SELECT * FROM generate_mps_details ORDER BY ltpp_month2 ASC) d", "d.p_month = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision' and a.item_fg_id = d.item_fg_id");
        $this->db->join("item_fg e", "a.item_fg_id = e.id");
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.customer_id');
        $this->db->order_by('a.item_fg_id', 'ASC');

        //Total Data
        $totalRows = $this->db->count_all_results('', false);

        //Limit 1 - 10
        $this->db->limit($rows, $offset);

        //Get Data Array
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            $periode = $record['p_year'] . $record['p_month'];
            $revision = $record['revision'];
            $assy_no = $record['number_customer'];
            $line = $record['line_id'];

            $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
            $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

            $no = 1;
            $arr = array();
            $arr_date = array();
            while (strtotime($firstDate) <= strtotime($endDate)) {
                $working_date = date('Y-m-d', strtotime($firstDate));
                $day = date('j', strtotime($firstDate));

                $this->db->select('qty');
                $this->db->from('production_schedules');
                $this->db->where('trans_date', $working_date);
                $this->db->where('item_fg_id', $record['item_fg_id']);
                $this->db->where('customer_id', $record['customer_id']);
                $production_schedule = $this->db->get()->row();

                if(!empty($production_schedule)){
                    $status_wds = ($record["date_".$day] * -1);
                }else{
                    $status_wds = $record["date_".$day];
                }

                $arr = array("wds_".$no => $status_wds);
                $arr_date = array_merge($arr, $arr_date);

                $no++;
                $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
            }
            $finals[] = array_merge($arr_date, $record);
        }
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => @$finals]);
        echo json_encode($result);
    }

    function getdata(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));
        
        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $hkw = 0;
        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            // if (date('w', $z) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $hkw += 0;
                } else {
                    $hkw += 1;
                }
            // } else {
            //     $hkw += 0;
            // }
        }

        $this->db->select("a.item_fg_id, b.number_customer, a.customer_id, COUNT(c.circuit_no) as circuit_no, a.prod_plan, b.lot, d.line_id");
        $this->db->from('generate_mps_details a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('wos c', 'a.item_fg_id = c.item_fg_id', 'left');
        $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.ltpp_month2', $ltppMonth);
        $this->db->where('a.prod_plan > 0');
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->group_by("b.number");
        $this->db->group_by("a.customer_id");
        $this->db->order_by("b.number", "asc");
        $recordDetails = $this->db->get()->result_array();

        $mpp = array();
        foreach ($recordDetails as $detail) {
            $periode = $filter_year . $filter_month;
            $assy_no = $record['number_customer'];
            $line = $record['line_id'];

            $this->db->select('SUM(qty) as qty');
            $this->db->from('production_schedules');
            $this->db->where('item_fg_id', $detail['item_fg_id']);
            $this->db->where('customer_id', $detail['customer_id']);
            $this->db->where('period', $periode);
            $prod = $this->db->get()->row();

            $rows = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => 0,
                "customer_id" => $detail['customer_id'],
                "item_fg_id" => $detail['item_fg_id'],
                "circuit_no" => $detail['circuit_no'],
                "prod_plan" => $detail['prod_plan'],
            );
            
            $prodplan = ($detail['prod_plan'] - @$prod->qty);
            $prodplanHkw = ($prodplan / $hkw);
            $lots = @(ceil($prodplanHkw / $detail['lot']) * $detail['lot']);
            $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            $no = 1;
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date = date('Y-m-d', strtotime($firstDate2));

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                if ($prodplan >= $lots) {
                    $qty = is_nan($lots) ? 0 : $lots;
                } elseif ($prodplan < 0) {
                    $qty = 0;
                } else {
                    $qty = $prodplan;
                }

                $this->db->select('qty');
                $this->db->from('production_schedules');
                $this->db->where('trans_date', $working_date);
                $this->db->where('item_fg_id', $detail['item_fg_id']);
                $this->db->where('customer_id', $detail['customer_id']);
                $production_schedule = $this->db->get()->row();

                // $this->dummy->select('COUNT(a.serial_mpp) as total');
                // $this->dummy->from("wip_trx_mpp a");
                // $this->dummy->join("wip_trx_wds b", "a.serial_mpp = b.serial_mpp");
                // $this->dummy->where("periode", $periode);
                // $this->dummy->where("assy_no", $assy_no);
                // $this->dummy->where("line", $line);
                // $this->dummy->where("wp_date", $working_date);
                // $this->dummy->group_by("a.assy_no");
                // $this->dummy->group_by("a.wp_date");
                // $wip_trx_mpp = $this->dummy->get()->result_array();

                if(count($production_schedule) > 0){
                    //
                }else{
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $rows = array_merge($rows, array("date_".$no => "W"));
                    } else {
                        $rows = array_merge($rows, array("date_".$no => "$qty"));
                        $prodplan = ($prodplan - $lots);
                    }
                }

                $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
                $no++;
            }

            $mpp[] = $rows;
        }
        echo json_encode($mpp);
    }
    
    public function create()
    {
        if ($this->input->post('data')) {
            $post = $this->input->post('data');
            $read = $this->crud->read("generate_mpp", [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_fg_id" => $post['item_fg_id'],
                "customer_id" => $post['customer_id'],
            ]);

            if ($read) {
                $send = $this->crud->update('generate_mpp', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_fg_id" => $post['item_fg_id'],
                    "customer_id" => $post['customer_id']
                ], $post);
            } else {
                $send = $this->crud->create('generate_mpp', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function createProductionSchedules()
    // {
    //     if ($this->input->post()) {
    //         $this->dummy = $this->load->database('dummy', TRUE);
    //         $post = $this->input->post('data');

    //         $p_month = $post['p_month'];
    //         $p_year = $post['p_year'];
    //         $period = $p_year.$p_month;
            
    //         $firstDate = date("Y-m-01", strtotime(date("$p_year-$p_month-01")));
    //         $endDate = date("Y-m-t", strtotime(date("$p_year-$p_month")));

    //         $wp = 1;
    //         $tgl = 1;
    //         $dataFinal = [];
    //         while (strtotime($firstDate) <= strtotime($endDate)) {
    //             $wpp = $wp;
    //             $working_date = date('Y-m-d', strtotime($firstDate));
    //             $qty = $post['date_'.$tgl];
    //             $wds = $post['wds_'.$tgl];

    //             $this->dummy->select('COUNT(a.serial_mpp) as total');
    //             $this->dummy->from("wip_trx_mpp a");
    //             $this->dummy->join("wip_trx_wds b", "a.serial_mpp = b.serial_mpp");
    //             $this->dummy->where("periode", $period);
    //             $this->dummy->where("assy_no", $post['number_customer']);
    //             $this->dummy->where("line", $post['line_id']);
    //             $this->dummy->where("wp_date", $working_date);
    //             $this->dummy->group_by("a.assy_no");
    //             $this->dummy->group_by("a.wp_date");
    //             $wip_trx_mpp = $this->dummy->get()->row();

    //             if($wds > 0 && $qty != "W" && @$wip_trx_mpp->total > 0){
    //                 $datenow = date("ymd");
    //                 $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedules WHERE workorder like '%$datenow%'");
    //                 $rowID = $sqlGetID->row();
    //                 $kode = $rowID->kode;
    //                 if ($kode == NULL) {
    //                     $autoID = sprintf("%05s", $kode + 1);
    //                 } else {
    //                     $urutan = (int) substr($kode, -4);
    //                     $urutan++;
    //                     $autoID = sprintf("%05s", $urutan);
    //                 }

    //                 $workOrderNo = "WO" . $datenow . "-" . $autoID;
    //                 $trans_date = $p_year."-".$p_month."-".sprintf("%02s", $tgl);

    //                 $dataFinal = array(
    //                     "customer_id" => $post['customer_id'], 
    //                     "item_fg_id" => $post['item_fg_id'],
    //                     "workorder" => $workOrderNo,
    //                     "trans_date" => $trans_date,
    //                     "period" => $period,
    //                     "year" => $p_year,
    //                     "month" => $p_month,
    //                     "wp" => $wpp,
    //                     "qty" => $qty,
    //                 );

    //                 $productionSchedule = $this->crud->read("production_schedules", [], [
    //                     "customer_id" => $post['customer_id'], 
    //                     "item_fg_id" => $post['item_fg_id'],
    //                     "period" => $period,
    //                     "year" => $p_year,
    //                     "month" => $p_month,
    //                     "wp" => $wpp,
    //                 ]);

    //                 if($qty > 0){
    //                     if(count($productionSchedule) > 0){
    //                         $send = json_encode(array("title" => "Exists", "message" => "Data Already Exist in Production Schedules", "theme" => "error"));
    //                     }else{
    //                         $send = $this->crud->create('production_schedules', $dataFinal);
    //                     }
    //                 }else{
    //                     $send = json_encode(array("title" => "Qty 0", "message" => "Qty WP is 0", "theme" => "error"));
    //                 }
    //             }else{
    //                 $send = json_encode(array("title" => "Nothing WOS", "message" => "Please Generate WOS First in MRP Banshu", "theme" => "error"));
    //             }

    //             $tgl++;
    //             $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //             $wp++;
    //         }
            
    //         die(json_encode($dataFinal));
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }
    
    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('generate_mpp', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }


    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_calculation_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

        $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

        $period_start = $filter_year."-".$filter_month."-01";
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-01"));

        $this->db->select("a.*, c.name as customer_name, b.number_customer, b.name as description, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as plotting");
        $this->db->from('generate_mpp a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->order_by('b.number');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.customer_id');
        $records = $this->db->get()->result_array();

        $wp = 1;
        $header = "";
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));

            $header .= "<th style='text-align:center;'>WP $wp</th>";

            $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
            $wp++;
        }

        //Setting Header
        $content = '';
        $no = 1;
        foreach ($records as $record) {
            $content .= "<tr>
                            <td>" . $no . "</td>
                            <td style='mso-number-format:\@;'>" . $record['customer_name'] . "</td>
                            <td style='mso-number-format:\@;'>" . $record['item_fg_id'] . "</td>
                            <td style='mso-number-format:\@;'>" . $record['number_customer'] . "</td>
                            <td style='mso-number-format:\@;'>" . $record['description'] . "</td>
                            <td style='text-align:right;'>" . round($record['prod_plan']) . "</td>
                            <td style='text-align:right;'>" . round($record['plotting']) . "</td>";
            
            $styles2 = "";
            $content2 = "";
            $day = 1;

            $firstDate2 = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date = date("Y-m-d", strtotime($firstDate2));

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                if (date('w', strtotime($firstDate2)) !== '0') {
                    if (@$holiday->remarks != null or @$holiday->remarks != ""){
                        $styles2 = 'background:#FFD974;';
                    }else{
                        if($balance < 0){
                            $styles2 = 'background:#FFC2C2;';
                        }else{
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }

                $content2 .= "<td style='text-align:right; ".$styles2."'>".@$record['date_'.$day]."</td>";

                $firstDate2 = date("d M Y", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $content .= $content2 . "</tr>";
            $no++;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>GENERATE MPP</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>

        <table id="customers" border="1" style="width:100%;">
            <tr>
                <th style="text-align:center;" width="20">NO</th>
                <th style="text-align:center;" width="150">Customer</th>
                <th style="text-align:center;" width="150">Product No EBWS</th>
                <th style="text-align:center;" width="200">Product No Customer</th>
                <th style="text-align:center;" width="200">Description</th>
                <th style="text-align:center;" width="100">Prod Plan</th>
                <th style="text-align:center;" width="100">Plotting</th>
                '.$header.'
            </tr>' . $content;
        echo $html;
    }

}

