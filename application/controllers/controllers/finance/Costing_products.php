<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Costing_products extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/costing_products');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-10 year', strtotime(date('Y'))));
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

    public function checkPurchaseInvoice(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        // $this->db->select('number');
        // $this->db->from('purchase_invoices');
        // $this->db->where('trans_date >=', $filter_from);
        // $this->db->where('trans_date <=', $filter_to);
        // $this->db->group_by('number');
        // $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->where('modul', 'PURCHASE INVOICING');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_b) > 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function checkSalesInvoice(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        $this->db->select('number');
        $this->db->from('sales_invoices');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->group_by('number');
        $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->where('modul', 'SALES INVOICING');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_a) > 0 && count($records_b) > 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function checkApPayment(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        $this->db->select('payment_no');
        $this->db->from('ap_payments');
        $this->db->where('payment_date >=', $filter_from);
        $this->db->where('payment_date <=', $filter_to);
        $this->db->group_by('payment_no');
        $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_a) == count($records_b) && count($records_b) > 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function checkArReceipt(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        $this->db->select('receipt_no');
        $this->db->from('ar_receipts');
        $this->db->where('receipt_date >=', $filter_from);
        $this->db->where('receipt_date <=', $filter_to);
        $this->db->group_by('receipt_no');
        $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->where('modul', 'AR RECEIPT');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_a) == count($records_b) && count($records_b) > 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function checkAsset(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        $this->db->select('asset_no');
        $this->db->from('asset_journals');
        $this->db->where('periode', $periode);
        $this->db->group_by('asset_no');
        $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('journal_date >=', $filter_from);
        $this->db->where('journal_date <=', $filter_to);
        $this->db->where('modul', 'ASSET');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_a) > 0 && count($records_b) > 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function checkCurrency(){
        $month = $this->input->post('filter_month');
        $year = $this->input->post('filter_year');
        $periode = $year . '-' . $month;

        $filter_from = date("Y-m-01", strtotime($periode));
        $filter_to = date("Y-m-t", strtotime($periode));

        $this->db->select('number');
        $this->db->from('journal_revaluations');
        $this->db->where('period', $periode);
        $this->db->group_by('number');
        $records_a = $this->db->get()->result_array();

        $this->db->select('document_no');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $filter_from);
        $this->db->where('trans_date <=', $filter_to);
        $this->db->where('modul', 'CURRENCY REVALUATION');
        $this->db->group_by('document_no');
        $records_b = $this->db->get()->result_array();

        if(count($records_a) == count($records_b) && count($records_b) > 0){
            echo 1;
        }else{
            if(count($records_a) == 0){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    public function getData()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $year . '-' . $month;
            $periode2 = $year . $month;

            $filter_from = date("Y-m-01", strtotime($periode));
            $filter_to = date("Y-m-t", strtotime($periode));

            $this->db->select('c.*');
            $this->db->from('item_fg a');
            $this->db->join('production_schedules c', "a.id = c.item_fg_id and c.month = '$month' and c.year = '$year'");
            $this->db->where("b.number", '001');
            $this->db->like('a.id', $item_fg_id);
            $this->db->group_by('c.workorder');
            $this->db->order_by('a.number', 'asc');
            $item_fg = $this->db->get()->result_array();

            $inventories_rm = $this->crud->reads('inventory_rm', [], ["period" => $periode]);

            $labor_components = $this->crud->reads("costing_components", [], ["name" => "LABOR"]);
            $total_labor = 0;
            foreach ($labor_components as $labor_component) {
                $labor = $this->crud->query("SELECT account_number, (SUM(local_debit) - SUM(local_credit)) as total_ap 
                FROM journal_postings
                WHERE account_number = '$labor_component->account_number' and journal_date LIKE '%$periode%'
                GROUP BY account_number");
                $total_labor += @$labor[0]->total_ap;
            }

            $overtime_components = $this->crud->reads("costing_components", [], ["name" => "OVERTIME"]);
            $total_overtime = 0;
            foreach ($overtime_components as $overtime_component) {
                $overtime = $this->crud->query("SELECT account_number, (SUM(local_debit) - SUM(local_credit)) as total_ap 
                FROM journal_postings
                WHERE account_number = '$overtime_component->account_number' and journal_date LIKE '%$periode%'
                GROUP BY account_number");
                $total_overtime += @$overtime[0]->total_ap;
            }

            $foh_components = $this->crud->reads("costing_components", [], ["name" => "FOH"]);
            $total_foh = 0;
            foreach ($foh_components as $foh_component) {
                $foh = $this->crud->query("SELECT account_number, (SUM(local_debit) - SUM(local_credit)) as total_ap 
                FROM journal_postings
                WHERE account_number = '$foh_component->account_number' and journal_date LIKE '%$periode%'
                GROUP BY account_number");
                $total_foh += @$foh[0]->total_ap;
            }

            $dataFinals = array();
            if(count($inventories_rm) > 0){
                $datas = array();

                $total_umh = 0;
                foreach ($item_fg as $item) {
                    $this->db->select("item_fg_id, SUM(cycle_time) as total");
                    $this->db->from('umh');
                    $this->db->where('item_fg_id', $item['item_fg_id']);
                    $this->db->group_by('item_fg_id');
                    $umh = $this->db->get()->row();

                    $total_umh += ($item['qty'] * @$umh->total);
                }

                foreach ($item_fg as $item) {
                    $workorder = $item['workorder'];
                    
                    $this->db->select("item_fg_id, SUM(cycle_time) as total");
                    $this->db->from('umh');
                    $this->db->where('item_fg_id', $item['item_fg_id']);
                    $this->db->group_by('item_fg_id');
                    $umh = $this->db->get()->row();

                    $supply_sheets = $this->crud->query("SELECT a.item_fg_id, a.trans_date, a.document_no, SUM(a.qty) as qty, a.price, SUM(a.amount) as amount
                    FROM inventory_rm a
                    JOIN supply_sheets b ON a.document_no = b.request_no and a.item_fg_id = b.component_id
                    JOIN item_fg e ON a.item_fg_id = e.id
                    JOIN item_familys f ON e.item_family_id = f.id
                    WHERE f.number = '002' and a.trans_type = 'ISSUED' and a.trans_date BETWEEN '$filter_from' and '$filter_to' and b.workorder = '$workorder'
                    GROUP BY a.document_no, a.item_fg_id");
                    
                    $direct_material = 0;
                    $costing_product_details = array();
                    foreach ($supply_sheets as $supply_sheet) {
                        $costing_product_details[] = array(
                            "item_fg_id" => $supply_sheet->item_fg_id,
                            "periode" => $periode,
                            "workorder" => $item['workorder'],
                            "supply_type" => "SUPPLY SHEET",
                            "supply_date" => $supply_sheet->trans_date,
                            "supply_no" => $supply_sheet->document_no,
                            "issued" => abs($supply_sheet->qty),
                            "price" => abs($supply_sheet->price),
                            "amount" => abs($supply_sheet->amount),
                        );

                        // $this->crud->create("costing_product_details", $arr);
                        $direct_material += abs($supply_sheet->amount);
                    }

                    $supply_materials = $this->crud->query("SELECT a.item_fg_id, a.trans_date, a.document_no, SUM(a.qty) as qty, a.price, SUM(a.amount) as amount
                    FROM inventory_rm a
                    JOIN supply_materials b ON a.document_no = b.request_no and a.item_fg_id = b.item_fg_id
                    JOIN item_fg e ON a.item_fg_id = e.id
                    JOIN item_familys f ON e.item_family_id = f.id
                    WHERE f.number = '002' and a.trans_type = 'ISSUED' and a.trans_date BETWEEN '$filter_from' and '$filter_to' and b.workorder = '$workorder'
                    GROUP BY a.document_no, a.item_fg_id");

                    $non_supply = 0;
                    foreach ($supply_materials as $supply_material) {
                        $costing_product_details[] = array(
                            "item_fg_id" => $supply_material->item_fg_id,
                            "periode" => $periode,
                            "workorder" => $item['workorder'],
                            "supply_type" => "NON SUPPLY SHEET",
                            "supply_date" => $supply_material->trans_date,
                            "supply_no" => $supply_material->document_no,
                            "issued" => abs($supply_material->qty),
                            "price" => abs($supply_material->price),
                            "amount" => abs($supply_material->amount),
                        );

                        // $this->crud->create("costing_product_details", $arr);
                        $non_supply += abs($supply_material->amount);
                    }

                    $supply_requestions = $this->crud->query("SELECT a.item_fg_id, a.trans_date, a.document_no, SUM(a.qty) as qty, a.price, SUM(a.amount) as amount
                    FROM inventory_rm a
                    JOIN supply_requestions b ON a.document_no = b.request_no and a.item_fg_id = b.item_fg_id
                    JOIN item_fg e ON a.item_fg_id = e.id
                    JOIN item_familys f ON e.item_family_id = f.id
                    WHERE f.number = '002' and a.trans_type = 'ISSUED' and a.trans_date BETWEEN '$filter_from' and '$filter_to' and b.workorder = '$workorder'
                    GROUP BY a.document_no, a.item_fg_id");

                    $direct_requestion = 0;
                    foreach ($supply_requestions as $supply_requestion) {
                        $costing_product_details[] = array(
                            "item_fg_id" => $supply_requestion->item_fg_id,
                            "periode" => $periode,
                            "workorder" => $item['workorder'],
                            "supply_type" => "MATERIAL REQUESTION",
                            "supply_date" => $supply_requestion->trans_date,
                            "supply_no" => $supply_requestion->document_no,
                            "issued" => abs($supply_requestion->qty),
                            "price" => abs($supply_requestion->price),
                            "amount" => abs($supply_requestion->amount),
                        );

                        // $this->crud->create("costing_product_details", $arr);
                        $direct_requestion += abs($supply_requestion->amount);
                    }

                    $dataFinals[] = array(
                        "item_fg_id" => $item['item_fg_id'],
                        "periode" => $periode,
                        "wp" => $item['wp'],
                        "workorder" => $item['workorder'],
                        "qty" => $item['qty'],
                        "umh" => @$umh->total,
                        "total_umh" => ($item['qty'] * @$umh->total),
                        "direct_labor" => ((($item['qty'] * @$umh->total) / $total_umh) * $total_labor),
                        "direct_foh" => ((($item['qty'] * @$umh->total) / $total_umh) * $total_foh),
                        "direct_overtime" => ((($item['qty'] * @$umh->total) / $total_umh) * $total_overtime),
                        "direct_material" => $direct_material,
                        "direct_requestion" => ($direct_requestion + $non_supply),
                        "costing_product_details" => $costing_product_details
                    );
                }
            }

            $result['total'] = count($dataFinals);
            $result = array_merge($result, ['rows' => $dataFinals]);
            echo json_encode($result);
        }
    }

    public function getDataProducts(){
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $year . '-' . $month;

            $this->db->select('item_fg_id,
                SUM(qty) as total_qty, 
                SUM(total_umh) as total_umh, 
                SUM(direct_labor) as direct_labor, 
                SUM(direct_foh) as direct_foh, 
                SUM(direct_material) as direct_material,
                SUM(direct_overtime) as direct_overtime,
                SUM(direct_requestion) as direct_requestion');
            $this->db->from('costing_product_workorders');
            $this->db->where('periode', $periode);
            $this->db->like('item_fg_id', $item_fg_id);
            $this->db->group_by('item_fg_id');
            $this->db->order_by('item_fg_id', 'asc');
            $item_fg = $this->db->get()->result_array();

            foreach ($item_fg as $item) {
                $direct_total = ((($item['direct_material'] + $item['direct_requestion']) / $item['total_qty']) + (($item['direct_labor'] + $item['direct_overtime']) / $item['total_qty']) + ($item['direct_foh'] / $item['total_qty']));

                $dataFinals[] = array(
                    "item_fg_id" => $item['item_fg_id'],
                    "periode" => $periode,
                    "umh" => $item['total_umh'],
                    "total_qty" => $item['total_qty'],
                    "direct_material" => $item['direct_material'],
                    "direct_requestion" => $item['direct_requestion'],
                    "direct_material_total" => ($item['direct_material'] + $item['direct_requestion']),
                    "direct_material_pcs" => (($item['direct_material'] + $item['direct_requestion']) / $item['total_qty']),
                    "direct_labor" => $item['direct_labor'],
                    "direct_overtime" => $item['direct_overtime'],
                    "direct_labor_total" => ($item['direct_labor'] + $item['direct_overtime']),
                    "direct_labor_pcs" => (($item['direct_labor'] + $item['direct_overtime']) / $item['total_qty']),
                    "direct_foh" => $item['direct_foh'],
                    "direct_foh_pcs" => ($item['direct_foh'] / $item['total_qty']),
                    "direct_total" => $direct_total,
                );
            }

            $result['total'] = count($dataFinals);
            $result = array_merge($result, ['rows' => $dataFinals]);
            echo json_encode($result);
        }
    }

    public function getDataBom()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $year . '-' . $month;

            $filter_from = date("Y-m-01", strtotime($periode));
            $filter_to = date("Y-m-t", strtotime($periode));

            $this->db->select('*');
            $this->db->from('bom');
            $this->db->like('item_fg_id', $item_fg_id);
            $this->db->order_by('component_id', 'asc');
            $boms = $this->db->get()->result_array();

                $datas = array();
                foreach ($item_fg as $item) {
                    $this->db->select('a.item_fg_id, e.number, e.name, b.request_no, SUM(a.qty) as qty, SUM(a.amount) as amount');
                    $this->db->from('inventory_rm a');
                    $this->db->join('supply_sheets b', "a.document_no = b.request_no AND a.item_fg_id = b.component_id", 'left');
                    $this->db->join('supply_materials c', "a.document_no = c.request_no AND a.item_fg_id = c.item_fg_id", 'left');
                    $this->db->join('supply_requestions d', "a.document_no = d.request_no AND a.item_fg_id = d.item_fg_id", 'left');
                    $this->db->join("item_fg e", "a.item_fg_id = e.id");
                    $this->db->join("item_familys f", "e.item_family_id = f.id");
                    $this->db->join("production_schedules g", "b.workorder = g.workorder or d.workorder = g.workorder or (c.period = g.period and c.wp = g.wp)");
                    $this->db->where('g.item_fg_id', $item['id']);
                    $this->db->where('f.number', '002');
                    $this->db->where('a.trans_type', 'ISSUED');
                    $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
                    $this->db->group_by('a.document_no, item_fg_id');
                    $inventories = $this->db->get()->result_array();

                    $direct_material = 0;
                    $direct_requestion = 0;
                    $direct_requestion_qty = 0;
                    $direct_material_qty = 0;
                    foreach ($inventories as $inventory) {
                        if(empty($inventory['request_no'])){
                            $direct_requestion += round(abs($inventory['amount']), 2);
                            $direct_requestion_qty += abs($inventory['qty']);
                        }else{
                            $direct_material += round(abs($inventory['amount']), 2);
                            $direct_material_qty += abs($inventory['qty']);
                        }
                    }

                    if($direct_material > 0){
                        $price = (($direct_requestion_qty + $direct_material_qty) / ($direct_material + $direct_requestion));
                    }else{
                        $price = 0;
                    }

                    $datas[] = array(
                        "item_fg_id" => $item['id'],
                        "item_no" => $item['number'],
                        "item_name" => $item['name'],
                        "qty" => $item['qty'],
                        "price" => $price,
                        "direct_material" => $direct_material,
                        "direct_requestion" => $direct_requestion,
                    );

                    $total_qty_production += $item['qty'];
                }

                foreach ($datas as $data) {
                    $shared = round(($data['qty'] / $total_qty_production) * 100, 0);

                    $labor_components = $this->crud->reads("costing_components", [], ["name" => "LABOR"]);
                    $total_labor = 0;
                    foreach ($labor_components as $labor_component) {
                        $labor = $this->crud->query("SELECT account_number, SUM(local_debit) as total_ap 
                        FROM journal_postings
                        WHERE account_number = '$labor_component->account_number' and journal_date LIKE '%$periode%'
                        GROUP BY account_number");
                        $total_labor += @$labor[0]->total_ap;
                    }
                    $direct_labor = round(($total_labor * $shared) / 100, 2);

                    $overtime_components = $this->crud->reads("costing_components", [], ["name" => "OVERTIME"]);
                    $total_overtime = 0;
                    foreach ($overtime_components as $overtime_component) {
                        $overtime = $this->crud->query("SELECT account_number, SUM(local_debit) as total_ap 
                        FROM journal_postings
                        WHERE account_number = '$overtime_component->account_number' and journal_date LIKE '%$periode%'
                        GROUP BY account_number");
                        $total_overtime += @$overtime[0]->total_ap;
                    }
                    $direct_overtime = round(($total_overtime * $shared) / 100, 2);
                    $direct_labor_total = ($direct_labor + $direct_overtime);

                    if ($data['qty'] > 0) {
                        $direct_labor_pcs = round($direct_labor_total / $data['qty'], 2);
                    } else {
                        $direct_labor_pcs = 0;
                    }

                    $foh_components = $this->crud->reads("costing_components", [], ["name" => "FOH"]);
                    $total_foh = 0;
                    foreach ($foh_components as $foh_component) {
                        $foh = $this->crud->query("SELECT account_number, SUM(local_debit) as total_ap 
                        FROM journal_postings
                        WHERE account_number = '$foh_component->account_number' and journal_date LIKE '%$periode%'
                        GROUP BY account_number");
                        $total_foh += @$foh[0]->total_ap;
                    }
                    $direct_foh = round(($total_foh * $shared) / 100, 2);
                    if ($data['qty'] > 0) {
                        $direct_foh_pcs = round($direct_foh / $data['qty'], 2);
                    } else {
                        $direct_foh_pcs = 0;
                    }

                    if($data['qty'] == 0){
                        $qty = 1;
                    }else{
                        $qty = $data['qty'];
                    }

                    $direct_total = ((($data['direct_material'] + $data['direct_requestion']) / $qty) + $direct_labor_pcs + $direct_foh_pcs);

                    $dataFinals[] = array(
                        "periode" => $periode,
                        "item_fg_id" => $data['item_fg_id'],
                        "price" => $data['price'],
                        "qty" => $data['qty'],
                        "total_qty" => $total_qty_production,
                        "shared" => $shared,
                        "direct_material" => $data['direct_material'],
                        "direct_requestion" => $data['direct_requestion'],
                        "direct_material_total" => ($data['direct_material'] + $data['direct_requestion']),
                        "direct_material_pcs" => (($data['direct_material'] + $data['direct_requestion']) / $qty),
                        "direct_labor" => $direct_labor,
                        "direct_overtime" => $direct_overtime,
                        "direct_labor_total" => $direct_labor_total,
                        "direct_labor_pcs" => $direct_labor_pcs,
                        "direct_foh" => $direct_foh,
                        "direct_foh_pcs" => $direct_foh_pcs,
                        "direct_total" => $direct_total,
                    );
                }

            $result['total'] = count($dataFinals);
            $result = array_merge($result, ['rows' => $dataFinals]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_item = base64_decode($this->input->get('filter_item'));

            $filter_period = $filter_year . "-" . $filter_month;

            //Select Query
            $this->db->select('a.*, b.number as item_no, b.name as item_name');
            $this->db->from('costing_products a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.periode', $filter_period);
            $this->db->like('a.item_fg_id', $filter_item);
            $this->db->order_by('a.qty', 'desc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Select Query
            $this->db->select('item_fg_id, 
                SUM(total_qty) as total_qty, 
                SUM(umh) as total_umh, 
                SUM(direct_material) as direct_material,
                SUM(direct_requestion) as direct_requestion,
                SUM(direct_material_total) as direct_material_total,
                SUM(direct_material_pcs) as direct_material_pcs,
                SUM(direct_labor) as direct_labor,
                SUM(direct_overtime) as direct_overtime,
                SUM(direct_labor_total) as direct_labor_total,
                SUM(direct_labor_pcs) as direct_labor_pcs,
                SUM(direct_foh) as direct_foh,
                SUM(direct_foh_pcs) as direct_foh_pcs,
                SUM(direct_total) as direct_total');
            $this->db->from('costing_products');
            $this->db->where('periode', $filter_period);
            $this->db->like('item_fg_id', $filter_item);
            $this->db->group_by('periode');
            $total = $this->db->get()->row();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result['footer'] = [array(
                "history" => "Grand Total",
                "total_qty" => $total->total_qty,
                "umh" => $total->total_umh,
                "direct_material" => $total->direct_material,
                "direct_requestion" => $total->direct_requestion,
                "direct_material_total" => $total->direct_material_total,
                "direct_material_pcs" => $total->direct_material_pcs,
                "direct_labor" => $total->direct_labor,
                "direct_overtime" => $total->direct_overtime,
                "direct_labor_total" => $total->direct_labor_total,
                "direct_labor_pcs" => $total->direct_labor_pcs,
                "direct_foh" => $total->direct_foh,
                "direct_foh_pcs" => $total->direct_foh_pcs,
                "direct_total" => $total->direct_total,
            )];
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableWorkorders(){
        if ($this->input->get()) {
            $periode = base64_decode($this->input->get('periode'));
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('costing_product_workorders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.periode', $periode);
            $this->db->like('a.item_fg_id', $item_fg_id);
            $this->db->order_by('a.qty', 'desc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails(){
        if ($this->input->get()) {
            $periode = base64_decode($this->input->get('periode'));
            $workorder = base64_decode($this->input->get('workorder'));

            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom');
            $this->db->from('costing_product_details a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->where('a.periode', $periode);
            $this->db->like('a.workorder', $workorder);
            $this->db->order_by('a.amount', 'desc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $costing_product_details = @$post['costing_product_details'];
            if(!empty($costing_product_details)){
                foreach ($costing_product_details as $costing_product_detail) {
                    $read_costing_product_detail = $this->crud->read('costing_product_details', [], [
                        "periode" => $costing_product_detail['periode'], 
                        "item_fg_id" => $costing_product_detail['item_fg_id'],
                        "workorder" => $costing_product_detail['workorder'],
                        "supply_no" => $costing_product_detail['supply_no']
                    ]);

                    if (@$read_costing_product_detail->id != "") {
                        $send = $this->crud->update('costing_product_details', [
                            "periode" => $costing_product_detail['periode'], 
                            "item_fg_id" => $costing_product_detail['item_fg_id'], 
                            "workorder" => $costing_product_detail['workorder'],
                            "supply_no" => $costing_product_detail['supply_no']
                        ], $costing_product_detail);
                    } else {
                        $send = $this->crud->create('costing_product_details', $costing_product_detail);
                    }
                }
            }

            $costing_product_workorder = $this->crud->read('costing_product_workorders', [], [
                "periode" => $post['periode'], 
                "item_fg_id" => $post['item_fg_id'],
                "workorder" => $post['workorder']
            ]);

            $data = array(
                "item_fg_id" => $post['item_fg_id'],
                "periode" => $post['periode'],
                "wp" => $post['wp'],
                "workorder" => $post['workorder'],
                "qty" => $post['qty'],
                "umh" => $post['umh'],
                "total_umh" => $post['total_umh'],
                "direct_labor" => $post['direct_labor'],
                "direct_foh" => $post['direct_foh'],
                "direct_material" => $post['direct_material'],
                "direct_overtime" => $post['direct_overtime'],
                "direct_requestion" => $post['direct_requestion'],
            );

            if (@$costing_product_workorder->id != "") {
                $send = $this->crud->update('costing_product_workorders', ["periode" => $post['periode'], "item_fg_id" => $post['item_fg_id'], "workorder" => $post['workorder']], $data);
            } else {
                $send = $this->crud->create('costing_product_workorders', $data);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createProducts()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $costing_products = $this->crud->read('costing_products', [], ["periode" => $post['periode'], "item_fg_id" => $post['item_fg_id']]);

            if (@$costing_products->id != "") {
                $send = $this->crud->update('costing_products', ["periode" => $post['periode'], "item_fg_id" => $post['item_fg_id']], $post);
            } else {
                $send = $this->crud->create('costing_products', $post);
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
        $costing_product = $this->crud->read('costing_products', [], ["id" => $data['id']]);
        $costing_product_workorders = $this->crud->reads('costing_product_workorders', [], [
            "item_fg_id" => $costing_product->item_fg_id,
            "periode" => $costing_product->periode
        ]);

        foreach ($costing_product_workorders as $costing_product_workorder) {
            $this->crud->delete('costing_product_workorders', ["id" => $costing_product_workorder->id]);
            $this->crud->delete('costing_product_details', [
                "periode" => $costing_product_workorder->periode,
                "workorder" => $costing_product_workorder->workorder,
            ]);
        }

        $send = $this->crud->delete('costing_products', $data);
        echo $send;
    }

    public function uploadclearFailed()
    {
        @unlink('failed/costing_products.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/costing_products.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/costing_products.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=costing_products_$format.xls");
        }

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_item = base64_decode($this->input->get('filter_item'));

        $filter_period = $filter_year . "-" . $filter_month;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_no, b.name as item_name');
        $this->db->from('costing_products a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.periode', $filter_period);
        $this->db->like('a.item_fg_id', $filter_item);
        $this->db->order_by('a.qty', 'desc');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>GENERATE COSTING PRODUCTS PERIOD ' . $filter_period . '</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="3" width="20">No</th>
                <th rowspan="3">Product No</th>
                <th rowspan="3">Product Name</th>
                <th rowspan="3">Quantity</th>
                <th rowspan="3">UMH</th>
                <th colspan="10">Total Manufacture Cost</th>
                <th rowspan="3">Cost Product</th>
            </tr>
            <tr>
                <th colspan="4">Direct Material</th>
                <th colspan="4">Direct Labor</th>
                <th colspan="2">FOH</th>
            </tr>
            <tr>
                <th>Supply</th>
                <th>Material<br>Requestion</th>
                <th>Total</th>
                <th>Pcs</th>
                <th>Regular</th>
                <th>Overtime</th>
                <th>Total</th>
                <th>Pcs</th>
                <th>FOH</th>
                <th>Pcs</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['total_qty'] . '</td>
                            <td>' . $data['umh'] . '</td>
                            <td>' . $data['direct_material'] . '</td>
                            <td>' . $data['direct_requestion'] . '</td>
                            <td>' . $data['direct_material_total'] . '</td>
                            <td>' . $data['direct_material_pcs'] . '</td>
                            <td>' . $data['direct_labor'] . '</td>
                            <td>' . $data['direct_overtime'] . '</td>
                            <td>' . $data['direct_labor_total'] . '</td>
                            <td>' . $data['direct_labor_pcs'] . '</td>
                            <td>' . $data['direct_foh'] . '</td>
                            <td>' . $data['direct_foh_pcs'] . '</td>
                            <td>' . $data['direct_total'] . '</td>
                        </tr>';

            $html .= '  <tr>
                            <th colspan="16" style="background:#A3FF73;">Detail of Product No '.$data['item_no'].' - '.$data['item_name'].'</th>
                        </tr>
                        <tr>
                            <th width="20">No</th>
                            <th>Workorder</th>
                            <th>Period / WP</th>
                            <th>Qty</th>
                            <th>UMH</th>
                            <th>Total UMH</th>
                            <th>Direct Material</th>
                            <th>Direct Requestion</th>
                            <th>Direct Labor</th>
                            <th>Direct Overtime</th>
                            <th>Direct FOH</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>';

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('costing_product_workorders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.periode', $filter_period);
            $this->db->like('a.item_fg_id', $data['item_fg_id']);
            $this->db->order_by('a.qty', 'desc');
            //Get Data Array
            $workorders = $this->db->get()->result_array();

            $nod = 1;
            foreach ($workorders as $workorder) {
                $html .= '  <tr>
                                <td>' . $no.'.'.$nod . '</td>
                                <td>' . $workorder['workorder'] . '</td>
                                <td>' . $workorder['periode'] . ' /  '.$workorder['wp'].'</td>
                                <td>' . $workorder['qty'] . '</td>
                                <td>' . $workorder['umh'] . '</td>
                                <td>' . $workorder['total_umh'] . '</td>
                                <td>' . $workorder['direct_material'] . '</td>
                                <td>' . $workorder['direct_requestion'] . '</td>
                                <td>' . $workorder['direct_labor'] . '</td>
                                <td>' . $workorder['direct_overtime'] . '</td>
                                <td>' . $workorder['direct_foh'] . '</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>';
                
                    $html .= '  <tr>
                                    <th colspan="16" style="background:#FFDB73;">Detail of Workorder No '.$workorder['workorder'].'</th>
                                </tr>
                                <tr>
                                    <th width="20">No</th>
                                    <th>Component No</th>
                                    <th>Component Name</th>
                                    <th>Uom</th>
                                    <th>Supply Type</th>
                                    <th>Supply Date</th>
                                    <th>Supply No</th>
                                    <th>Issued</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>';

                $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom');
                $this->db->from('costing_product_details a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->join('uom c', 'b.uom_id = c.id');
                $this->db->where('a.periode', $filter_period);
                $this->db->like('a.workorder', $workorder['workorder']);
                $this->db->order_by('a.amount', 'desc');
                $details = $this->db->get()->result_array();

                $noc = 1;
                foreach ($details as $detail) {
                    $html .= '  <tr>
                                    <td>' . $no.'.'.$nod.'.'.$noc.'</td>
                                    <td>' . $detail['item_number'] . '</td>
                                    <td>' . $detail['item_name'] . '</td>
                                    <td>' . $detail['uom'] . '</td>
                                    <td>' . $detail['supply_type'] . '</td>
                                    <td>' . $detail['supply_date'] . '</td>
                                    <td>' . $detail['supply_no'] . '</td>
                                    <td>' . $detail['issued'] . '</td>
                                    <td>' . $detail['price'] . '</td>
                                    <td>' . $detail['amount'] . '</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>';
                    $noc++;
                }
                $nod++;
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
