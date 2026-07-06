<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Forecast_suppliers extends CI_Controller
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
            $this->load->view('planning/forecast_suppliers', $data);
        } else {
            redirect('error_access');
        }
    }

    //GET PERIOD
    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
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

    public function readRevisions() {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        $this->db->select('revision, DATE(cutoff) as cutoff', FALSE);
        $this->db->from('generate_mrp_finals');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        
        $this->db->group_by('revision');
        $this->db->group_by('DATE(cutoff)');
        
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    //GET REVISION LAST
    public function readRevisionLast()
    {
        $customer_id = $this->input->post('customer_id');
        $send = $this->crud->query("SELECT max(revision) as rev FROM forecasts WHERE customer_id = ?", array($customer_id));

        if (count($send) > 0) {
            if ($send[0]->rev == "5") {
                $data = array("revision" => ($send[0]->rev));
            } else {
                $data = array("revision" => ($send[0]->rev + 1));
            }
        } else {
            $data = array("revision" => 1);
        }

        echo json_encode($data);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_month           = @base64_decode($get['filter_month']);
            $filter_year            = @base64_decode($get['filter_year']);
            $filter_supplier_id     = @base64_decode($get['filter_supplier_id']); 
            $filter_items           = @base64_decode($get['filter_items']);
            $filter_revision        = @base64_decode($get['filter_revision']);
            $filter_product_family  = @base64_decode($get['filter_product_family']);

            $period_1 = "";
            $period_2 = "";
            $period_3 = "";
            
            if (!empty($filter_month) && !empty($filter_year)) {
                $period_1 = date("F Y", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));
                $period_2 = date("F Y", strtotime("+2 months", strtotime($filter_year."-".$filter_month."-01")));
                $period_3 = date("F Y", strtotime("+3 months", strtotime($filter_year."-".$filter_month."-01")));
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            
            $result = array();
            
            // Select Query
            $this->db->select('a.*, b.number as part_no, b.name as part_name, c.name as product_family, e.name as supplier_name');
            $this->db->from('forecast_suppliers a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('suppliers e', 'a.supplier_id = e.id','left');

            if (!empty($filter_month)) { $this->db->where('a.p_month', $filter_month); }
            if (!empty($filter_year)) { $this->db->where('a.p_year', $filter_year); }
            if (!empty($filter_supplier_id)) { $this->db->where('a.supplier_id', $filter_supplier_id); }
            if (!empty($filter_items)) { $this->db->where('a.item_rm_id', $filter_items); }
            if (!empty($filter_product_family)) { $this->db->where('b.item_family_id', $filter_product_family); }
            if ($filter_revision != "") { $this->db->where('a.revision', $filter_revision); }

            $totalRows = $this->db->count_all_results('', false);
            
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();
            
            $data_rows = array();
            $no = $offset + 1;
            
            foreach ($records as $record) {
                $record['no'] = $no++;
                $data_rows[] = $record;
            }

            $result['total']    = $totalRows;
            $result['rows']     = $data_rows;
            $result['period_1'] = $period_1;
            $result['period_2'] = $period_2;
            $result['period_3'] = $period_3;
            
            echo json_encode($result);
        }
    }

    public function saveAll() {
        $p_month  = $this->input->post('p_month');
        $p_year   = $this->input->post('p_year');
        $revision = $this->input->post('revision');
        $rows     = json_decode($this->input->post('forecast_data'), true);

        if (empty($rows) || $p_month == "" || $p_year == "" || $revision == "") {
            echo json_encode(['success' => false, 'message' => 'Data or filter incomplete']);
            return;
        }

        $table_name = 'forecast_suppliers'; 

        $this->db->trans_start();

        // ==========================================
        // PROSES 1: HAPUS DATA LAMA 
        // ==========================================
        $this->db->where('p_month', $p_month);
        $this->db->where('p_year', $p_year);
        $this->db->where('revision', $revision);
        $this->db->delete($table_name); 

        // ==========================================
        // PROSES 2: INSERT DATA 
        // ==========================================
        foreach ($rows as $row) {
            $insert_data = array(
                'p_month'      => $p_month,
                'p_year'       => $p_year,
                'revision'     => $revision,
                
                'item_rm_id'   => isset($row['item_rm_id']) ? $row['item_rm_id'] : null,
                'supplier_id'  => isset($row['supplier_id']) ? $row['supplier_id'] : null,
                'product_family_id'  => isset($row['product_family_id']) ? $row['product_family_id'] : null,
                'class_abc'  => isset($row['class_abc']) ? $row['class_abc'] : null,
                'leadtime'  => isset($row['leadtime']) ? $row['leadtime'] : null,
                'mpq'  => isset($row['mpq']) ? $row['mpq'] : null,
                'moq'  => isset($row['moq']) ? $row['moq'] : null,
                
                // Group Material
                'os_po'        => isset($row['os_po']) ? str_replace(',', '', $row['os_po']) : 0, 
                'used_1'       => isset($row['used_1']) ? str_replace(',', '', $row['used_1']) : 0,
                'used_2'       => isset($row['used_2']) ? str_replace(',', '', $row['used_2']) : 0,
                'used_3'       => isset($row['used_3']) ? str_replace(',', '', $row['used_3']) : 0,
                'average'      => isset($row['average']) ? str_replace(',', '', $row['average']) : 0,
                
                // Group Month 1
                'need_1'       => isset($row['need_1']) ? str_replace(',', '', $row['need_1']) : 0,
                'balance_1'    => isset($row['balance_1']) ? str_replace(',', '', $row['balance_1']) : 0,
                'month1_fc'    => isset($row['need_1']) ? str_replace(',', '', $row['need_1']) : 0,
                
                // Group Month 2
                'need_2'       => isset($row['need_2']) ? str_replace(',', '', $row['need_2']) : 0,
                'balance_2'    => isset($row['balance_2']) ? str_replace(',', '', $row['balance_2']) : 0,
                'month2_fc'    => isset($row['need_2']) ? str_replace(',', '', $row['need_2']) : 0,
                
                // Group Month 3
                'need_3'       => isset($row['need_3']) ? str_replace(',', '', $row['need_3']) : 0,
                'balance_3'    => isset($row['balance_3']) ? str_replace(',', '', $row['balance_3']) : 0,
                'month3_fc'    => isset($row['need_3']) ? str_replace(',', '', $row['need_3']) : 0
            );

            $this->crud->create($table_name, $insert_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to save data. Please check your database connection.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'All Forecast Data saved successfully!']);
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();

            $dataFinal = array(
                //field
                "month1_fc" => $post['month1_fc'],
                "month2_fc" => $post['month2_fc'],
                "month3_fc" => $post['month3_fc'],
                "remarks" => "Update",
            );

            $send = $this->crud->update('forecast_suppliers', ["id" => $id], $dataFinal);           
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_suppliers_$format.xls");
        }

        $get = $this->input->get();
        $filter_month           = @base64_decode($get['filter_month']);
        $filter_year            = @base64_decode($get['filter_year']);
        $filter_supplier_id     = @base64_decode($get['filter_supplier_id']); 
        $filter_items           = @base64_decode($get['filter_items']);
        $filter_revision        = @base64_decode($get['filter_revision']);
        $filter_product_family  = @base64_decode($get['filter_product_family']);

        $period_1 = "";
        $period_2 = "";
        $period_3 = "";
        
        if (!empty($filter_month) && !empty($filter_year)) {
            $period_1 = date("F Y", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_2 = date("F Y", strtotime("+2 months", strtotime($filter_year."-".$filter_month."-01")));
            $period_3 = date("F Y", strtotime("+3 months", strtotime($filter_year."-".$filter_month."-01")));
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as part_no, b.name as part_name, c.name as product_family, e.name as supplier_name');
        $this->db->from('forecast_suppliers a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id','left');

        if (!empty($filter_month)) { $this->db->where('a.p_month', $filter_month); }
        if (!empty($filter_year)) { $this->db->where('a.p_year', $filter_year); }
        if (!empty($filter_supplier_id)) { $this->db->where('a.supplier_id', $filter_supplier_id); }
        if (!empty($filter_items)) { $this->db->where('a.item_rm_id', $filter_items); }
        if (!empty($filter_product_family)) { $this->db->where('b.item_family_id', $filter_product_family); }
        if ($filter_revision != "") { $this->db->where('a.revision', $filter_revision); }

        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>FORECAST SUPPLIER</h3>
            </div>
        </center>
        
       <table id="supplier_items" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Part No.</th>
                <th rowspan="2">Part Name</th>
                <th rowspan="2">Product Family</th>
                <th rowspan="2">Supplier Name</th>
                <th rowspan="2">Class A/B/C</th>
                <th rowspan="2">Leadtime</th>
                <th rowspan="2">MPQ</th>
                <th rowspan="2">MOQ</th>
                
                <th colspan="4">Material</th>
                <th rowspan="2">OS PO</th>
                
                <th colspan="3">' . $period_1 . '</th>
                <th colspan="3">' . $period_2 . '</th>
                <th colspan="3">' . $period_3 . '</th>
                
                <th colspan="3">Approved</th>
                <th colspan="2">Created</th>
            </tr>
            <tr>
                <th>USED 1</th>
                <th>USED 2</th>
                <th>USED 3</th>
                <th>AVERAGE</th>
                
                <th>NEED</th>
                <th>BAL</th>
                <th>FC</th>
                
                <th>NEED</th>
                <th>BAL</th>
                <th>FC</th>
                
                <th>NEED</th>
                <th>BAL</th>
                <th>FC</th>
                
                <th>Status</th>
                <th>By</th>
                <th>Date</th>
                
                <th>By</th>
                <th>Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $approved_to = $data['approved_to'];
            if ($approved_to == "" || $approved_to === null) {
                $status_text = 'Approved';
                $status_style = 'background-color: #53D636; color: white; text-align: center;';
            } else {
                $status_text = 'Checking';
                $status_style = 'background-color: #FF5F5F; color: white; text-align: center;';
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td style="mso-number-format:\@;">' . str_replace("Ø", "&Oslash;", $data['part_no']) . '</td>
                    <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                    <td>' . $data['product_family'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td style="text-align:center;">' . $data['class_abc'] . '</td>
                    <td style="text-align:right;">' . $data['leadtime'] . '</td>
                    <td style="text-align:right;">' . $data['mpq'] . '</td>
                    <td style="text-align:right;">' . $data['moq'] . '</td>
                    
                    <td style="text-align:right;">' . $data['used_1'] . '</td>
                    <td style="text-align:right;">' . $data['used_2'] . '</td>
                    <td style="text-align:right;">' . $data['used_3'] . '</td>
                    <td style="text-align:right;">' . $data['average'] . '</td>
                    
                    <td style="text-align:right;">' . $data['os_po'] . '</td>
                    
                    <td style="text-align:right;">' . $data['need_1'] . '</td>
                    <td style="text-align:right;">' . $data['balance_1'] . '</td>
                    <td style="text-align:right;">' . $data['month1_fc'] . '</td>
                    
                    <td style="text-align:right;">' . $data['need_2'] . '</td>
                    <td style="text-align:right;">' . $data['balance_2'] . '</td>
                    <td style="text-align:right;">' . $data['month2_fc'] . '</td>
                    
                    <td style="text-align:right;">' . $data['need_3'] . '</td>
                    <td style="text-align:right;">' . $data['balance_3'] . '</td>
                    <td style="text-align:right;">' . $data['month3_fc'] . '</td>
                    
                    <td style="' . $status_style . '">' . $status_text . '</td>
                    <td style="text-align:center;">' . $data['approved_by'] . '</td>
                    <td style="text-align:center;">' . $data['approved_date'] . '</td>
                    
                    <td style="text-align:center;">' . $data['created_by'] . '</td>
                    <td style="text-align:center;">' . $data['created_date'] . '</td>
                </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_forecast($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_suppliers_$format.xls");
        }

        $get = $this->input->get();
        $filter_month           = @base64_decode($get['filter_month']);
        $filter_year            = @base64_decode($get['filter_year']);
        $filter_supplier_id     = @base64_decode($get['filter_supplier_id']); 
        $filter_items           = @base64_decode($get['filter_items']);
        $filter_revision        = @base64_decode($get['filter_revision']);
        $filter_product_family  = @base64_decode($get['filter_product_family']);

        $period_1 = ""; 
        $period_2 = ""; 
        $period_3 = "";
        $title_period = "";
        
        if (!empty($filter_month) && !empty($filter_year)) {
            $base_date = $filter_year . "-" . $filter_month . "-01";
            $period_1 = strtoupper(date("M", strtotime("+1 month", strtotime($base_date))));
            $period_2 = strtoupper(date("M", strtotime("+2 months", strtotime($base_date))));
            $period_3 = strtoupper(date("M", strtotime("+3 months", strtotime($base_date))));
            $title_period = strtoupper(date("F Y", strtotime($base_date)));
        }

        $forecast_supplier = $this->crud->read('forecast_suppliers', [], ["p_month" => $filter_month,"p_year" => $filter_year,"supplier_id" => $filter_supplier_id,"revision" => $filter_revision,]);
        $approval = $this->crud->read('approvals', [], ["table_name" => "forecast_suppliers"]);

        $user_0 = (!empty($forecast_supplier)) ? $this->crud->read('users', [], ["username" => $forecast_supplier->created_by]) : (object)["name" => ""];
        $user_1 = (!empty($approval) && !empty($approval->user_approval_1)) ? $this->crud->read('users', [], ["username" => $approval->user_approval_1]) : (object)["name" => ""];
        $user_2 = (!empty($approval) && !empty($approval->user_approval_2)) ? $this->crud->read('users', [], ["username" => $approval->user_approval_2]) : (object)["name" => ""];
        $user_3 = (!empty($approval) && !empty($approval->user_approval_3)) ? $this->crud->read('users', [], ["username" => $approval->user_approval_3]) : (object)["name" => ""];

        $users_0 = ''; $users_1 = ''; $users_2 = ''; $users_3 = '';
        $app_level = !empty($forecast_supplier) ? $forecast_supplier->approved : 0;

        if ($app_level >= 0 && !empty($user_0->name)) {
            $this->createQrcode($user_0->name, "assets/image/qrcode/");
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $user_0->name . '.png') . '" width="80"/>';
        }
        if ($app_level >= 2 && !empty($user_1->name)) {
            $this->createQrcode($user_1->name, "assets/image/qrcode/");
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
        }
        if ($app_level >= 3 && !empty($user_2->name)) {
            $this->createQrcode($user_2->name, "assets/image/qrcode/");
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
        }
        if ($app_level >= 4 && !empty($user_3->name)) {
            $this->createQrcode($user_3->name, "assets/image/qrcode/");
            $users_3 = '<img src="' . base_url('assets/image/qrcode/' . $user_3->name . '.png') . '" width="80"/>';
        }

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $company_name = isset($config->name) ? $config->name : 'PT. BANSHU ELECTRIC INDONESIA';

        $this->db->select('a.*, b.uom, b.number as part_no, b.name as part_name, c.name as product_family, e.name as supplier_name, f.maker, f.item_supplier');
        $this->db->from('forecast_suppliers a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id','left');
        $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id','left');
        $this->db->group_by('a.id');

        if (!empty($filter_month)) { $this->db->where('a.p_month', $filter_month); }
        if (!empty($filter_year)) { $this->db->where('a.p_year', $filter_year); }
        if (!empty($filter_supplier_id)) { $this->db->where('a.supplier_id', $filter_supplier_id); }
        if (!empty($filter_items)) { $this->db->where('a.item_rm_id', $filter_items); }
        if (!empty($filter_product_family)) { $this->db->where('b.item_family_id', $filter_product_family); }
        if ($filter_revision != "") { $this->db->where('a.revision', $filter_revision); }

        $records = $this->db->get()->result_array();

        $nama_supplier = "........................................";
        if(!empty($records) && !empty($records[0]['supplier_name'])){
            $nama_supplier = strtoupper($records[0]['supplier_name']);
        }
        $this->createQrcode($user_0->name, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Print Forecast</title>
            <style>
                @media print {
                    @page { size: landscape; margin: 10mm; }
                    body { -webkit-print-color-adjust: exact; }
                }
                body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; }
                
                /* Header Styling */
                .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
                .header-left { display: flex; align-items: center; font-size: 12px; font-weight: bold; }
                .header-right { font-size: 10px; text-align: right; }
                
                h3 { text-align: center; font-size: 16px; margin: 20px 0; font-weight: bold; }
                
                /* Table Styling */
                table.data-table { border-collapse: collapse; width: 100%; font-size: 10px; }
                table.data-table th, table.data-table td { border: 1px solid black; padding: 4px; }
                table.data-table th { text-align: center; font-weight: bold; vertical-align: middle; }
                table.data-table td { vertical-align: middle; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                
                /* Notes & Signature Styling */
                .notes { margin-top: 15px; font-size: 10px; font-weight: bold; }
                .notes-text { font-style: italic; font-weight: normal; }
                
                table.signature-table { 
                    width: 100%; 
                    margin-top: 30px; 
                    border: 1px solid black; 
                    border-collapse: collapse; 
                    font-size: 11px; 
                    text-align: center; 
                }
                table.signature-table th, table.signature-table td { 
                    border: 1px solid black; 
                    padding: 5px; 
                }
                table.signature-table th { 
                    font-weight: bold; 
                    vertical-align: middle;
                }
                .sig-space { 
                    height: 90px; 
                    vertical-align: middle; 
                }
            </style>
        </head>
        <body>

            <div class="header-container">
                <div class="header-left">
                    <img src="' . (isset($config->favicon) ? $config->favicon : '') . '" width="40" style="margin-right: 10px; display:none;"> 
                    <div>
                        ' . $company_name . '<br>
                        <span style="font-weight: normal;">FORECAST</span>
                    </div>
                </div>
                <div class="header-right">
                    Print Date ' . date("d M Y H:i:s") . '
                </div>
            </div>

            <h3>FORECAST ' . $title_period . '</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="20">NO</th>
                        <th rowspan="2">PART NO</th>
                        <th rowspan="2">PART NAME</th>
                        <th rowspan="2">PRODUCT FAMILY</th>
                        <th rowspan="2">PART NO. VENDOR</th>
                        <th rowspan="2">MAKER</th>
                        <th rowspan="2">CLASS<br>A/B/C</th>
                        <th colspan="3">FORECAST</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">SUPPLIER\'S CAPACITY</th>
                        <th rowspan="2">CONFIRMATION OF<br>SUPPLIER\'S ABILITY</th>
                    </tr>
                    <tr>
                        <th width="60">' . $period_1 . '</th>
                        <th width="60">' . $period_2 . '</th>
                        <th width="60">' . $period_3 . '</th>
                    </tr>
                </thead>
                <tbody>';
                
            $no = 1;
            foreach ($records as $data) {
                $part_vendor = isset($data['item_supplier']) ? $data['item_supplier'] : ''; 
                $class_abc   = isset($data['class_abc']) ? $data['class_abc'] : '-';
                $uom         = isset($data['uom']) ? $data['uom'] : '-';
                
                $qty_1 = isset($data['month1_fc']) ? number_format($data['month1_fc'], 2, '.', '') : '0.00';
                $qty_2 = isset($data['month2_fc']) ? number_format($data['month2_fc'], 2, '.', '') : '0.00';
                $qty_3 = isset($data['month3_fc']) ? number_format($data['month3_fc'], 2, '.', '') : '0.00';

                $html .= '<tr>
                            <td class="text-center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . str_replace("Ø", "&Oslash;", $data['part_no']) . '</td>
                            <td>' . $data['part_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $part_vendor . '</td>
                            <td>' . $data['maker'] . '</td>
                            <td class="text-center">' . $class_abc . '</td>
                            <td class="text-right">' . $qty_1 . '</td>
                            <td class="text-right">' . $qty_2 . '</td>
                            <td class="text-right">' . $qty_3 . '</td>
                            <td class="text-center">' . $uom . '</td>
                            <td></td>
                            <td></td>
                        </tr>';
                $no++;
            }
            
            $html .= '
                </tbody>
            </table>

            <div class="notes">
                Note :<br>
                1. Angka pada kolom forecast merupakan estimasi quantity kebutuhan BPI pada bulan tersebut <span class="notes-text">(The numbers in the forecast column are estimates of the BPI quantity needed for that month).</span><br>
                2. Data Forecast berdasarkan hasil dari Generate MRP bulan berjalan <span class="notes-text">(Forecast data is based on the results of the current month\'s MRP Generate).</span>
            </div>

            <table class="signature-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">
                            ' . $nama_supplier . '
                        </th>
                        <th colspan="4" style="width: 80%;">
                            ' . $company_name . '
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 20%;">ACCEPTED BY</th>
                        <th style="width: 20%;">APPROVED</th>
                        <th style="width: 20%;">APPROVED</th>
                        <th style="width: 20%;">CHECKED</th>
                        <th style="width: 20%;">PREPARED</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="sig-space"></td> 
                        
                        <td class="sig-space">' . $users_2 . '</td>
                        <td class="sig-space">' . $users_3 . '</td>
                        <td class="sig-space">' . $users_1 . '</td>
                        <td class="sig-space">' . $users_0 . '</td>

                    </tr>
                    <tr>
                        <td>________________________</td>

                        <td>' . (!empty($user_2->name) ? strtoupper($user_2->name) : '________________________') . '</td>
                        <td>' . (!empty($user_3->name) ? strtoupper($user_3->name) : '________________________') . '</td>
                        <td>' . (!empty($user_1->name) ? strtoupper($user_1->name) : '________________________') . '</td>
                        <td>' . (!empty($user_0->name) ? strtoupper($user_0->name) : '________________________') . '</td>
                        
                    </tr>
                </tbody>
            </table>

            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        </body>
        </html>';

        echo $html;
    }
}
