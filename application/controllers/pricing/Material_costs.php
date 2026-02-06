<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Material_costs extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[material_costs.item_fg_id]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('pricing/material_costs');
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

    public function readItems($division_id)
    {
        $division_id = base64_decode($division_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_fg_npd WHERE status = '0' AND division_id like '%$division_id%' AND (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%') ORDER BY number ASC");
        echo json_encode($send);
    }

    public function datatables()
    {
        $get = $this->input->get();

        $filter_period_month = @base64_decode($get['filter_period_month']);
        $filter_period_year = @base64_decode($get['filter_period_year']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_revision = @base64_decode($get['filter_revision']);

        // Ambil pagination dan sorting
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        // Pagination 1-10
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        // Select Query Utama
        $this->db->select('a.*');
        $this->db->from('material_costs a');
        $this->db->where('a.deleted', 0);
        if ($filter_period_month != "") {
            $this->db->where('a.p_month', $filter_period_month);
        }

        if ($filter_period_year != "") {
            $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.revision');
        
        $this->db->order_by($sort, $order);
        $totalRows = $this->db->count_all_results('', false);

        // Batasi hasil query sesuai pagination
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        // Inisialisasi array untuk menyimpan hasil akhir
        $arr = [];

        foreach ($records as $record) {

            // Simpan data ke array hasil akhir
            $arr[] = array(
                "item_fg_id" => $record['item_fg_id'],
                "item_fg_number" => $record['item_fg_number'],
                "item_fg_name" => $record['item_fg_name'],
                "revision" => $record['revision'],
                "p_month" => $record['p_month'],
                "p_year" => $record['p_year'],
                "created_by" => $record['created_by'],
                "created_date" => $record['created_date'],
                "updated_by" => $record['updated_by'],
                "updated_date" => $record['updated_date'],
            );
        }

        // Kembalikan hasil sebagai JSON
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);
        echo json_encode($result);
    }


    public function datatableDetails($item_fg_id,$revision,$p_month,$p_year)
    {
        $item_fg_id = base64_decode($item_fg_id);
        $revision = base64_decode($revision);
        $p_month = base64_decode($p_month);
        $p_year = base64_decode($p_year);

        $this->db->select('a.*');
        $this->db->from('material_costs a');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->where('a.revision', $revision);
        $this->db->where('a.p_month', $p_month);
        $this->db->where('a.p_year', $p_year);

        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            
            // var_dump($status);
            $arr[] = array(
                "id" => $record['id'],
                "item_rm_id" => $record['item_rm_id'],
                "part_no" => $record['part_no'],
                "part_name" => $record['part_name'],
                "product_family" => $record['product_family'],
                "uom" => $record['uom'],
                "used" => $record['used'],
                "price" => $record['price'],
                "currency" => $record['currency'],
                "adj" => $record['adj'],
                "adj_price_nominal" => $record['adj_price_nominal'],
                "material_cost" => $record['material_cost'],
                "total_material_cost" => $record['total_material_cost']
            );
        }
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }

    public function datatablesTemp()
    {
        $item_fg_id = $this->input->get('item_fg_id');

        // Subquery untuk mencari harga tertinggi
        $price_subquery = "
            (SELECT item_rm_id, MAX(price) as max_price , currency, supplier_id
            FROM supplier_items 
            WHERE share_order = 100 
            GROUP BY item_rm_id) c";

        $this->db->select("
            d.id as item_fg_id,
            d.number as product_no,
            d.name as product_name,
            b.id as item_rm_id,
            b.number as part_no,
            b.name as part_name,
            e.name as product_family,
            ROUND(a.composition, 4) as used,
            b.uom,
            COALESCE(c.max_price, 0) as price,
            f.currency
        ");
        
        $this->db->from("bom_npd a");
        $this->db->join("item_rm b", "a.item_rm_id = b.id", "left");
        $this->db->join("$price_subquery", "b.id = c.item_rm_id", "left");
        $this->db->join("item_fg_npd d", "a.item_fg_id = d.id", "left");
        $this->db->join("item_familys e", "e.id = b.item_family_id", "left");
        $this->db->join("suppliers f", "f.id = c.supplier_id", "left");

        $this->db->where("a.item_fg_id", $item_fg_id);
        $this->db->order_by("b.item_family_id", "ASC"); // Mengurutkan berdasarkan family

        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    //CREATE DATA
    public function create_batch()
    {
        $header  = $this->input->post('header');
        $details = $this->input->post('details');

        if (!$header || !$details) {
            echo json_encode(['success' => false, 'message' => 'Data is empty']);
            return;
        }

        // --- Hitung Total  ---
        $total_all_rows = 0;
        foreach ($details as $row) {
            $total_all_rows += floatval($row['material_cost']);
        }

        $this->db->trans_start(); 

        // --- Masuk ke Loop Simpan ---
        foreach ($details as $row) {
            $where = [
                'item_fg_id' => $header['item_fg_id'],
                'division_id'=> $header['division_id'],
                'p_month'    => $header['p_month'],
                'p_year'     => $header['p_year'],
                'revision'   => $header['revision'],
                'item_rm_id' => $row['item_rm_id']
            ];

            $existing = $this->db->get_where('material_costs', $where)->row();

            $data = [
                'item_fg_number'    => $header['item_fg_number'],
                'item_fg_name'      => $header['item_fg_name'],
                'part_no'           => $row['part_no'],
                'part_name'         => $row['part_name'],
                'product_family'    => $row['product_family'],
                'used'              => $row['used'],
                'uom'               => $row['uom'],
                'price'             => $row['price'],
                'currency'          => $row['currency'],
                'adj'               => $row['adj'],
                'adj_price_nominal' => $row['adj_price_nominal'],
                'material_cost'     => $row['material_cost'],
                'total_material_cost' => $total_all_rows, 
                'deleted'           => 0
            ];

            if ($existing) {
                $this->db->update('material_costs', $data, ['id' => $existing->id]);
            } else {
                $data['item_fg_id'] = $header['item_fg_id'];
                $data['division_id']= $header['division_id'];
                $data['item_rm_id'] = $row['item_rm_id'];
                $data['p_month']    = $header['p_month'];
                $data['p_year']     = $header['p_year'];
                $data['revision']   = $header['revision'];
                
                $this->crud->create('material_costs', $data);
            }
        }

        $this->db->trans_complete(); 

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Data processed successfully',
                'total_cost' => number_format($total_all_rows, 2)
            ]);
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('material_costs', ["item_fg_id" => $data['item_fg_id'],"revision" => $data['revision'],"p_month" => $data['p_month'],"p_year" => $data['p_year']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_costs_$format.xls");
        }

        $get = $this->input->get();

        $filter_period_month = @base64_decode($get['filter_period_month']);
        $filter_period_year = @base64_decode($get['filter_period_year']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_revision = @base64_decode($get['filter_revision']);

        // $filter_operation = $this->input->get('filter_operation');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('material_costs a');
        $this->db->where('a.deleted', 0);
        if ($filter_period_month != "") {
            $this->db->where('a.p_month', $filter_period_month);
        }

        if ($filter_period_year != "") {
            $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        // $this->db->group_by('a.p_month');
        // $this->db->group_by('a.p_year');
        // $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.revision');

        $records = $this->db->get()->result_array();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                 <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>MATERIAL COSTS</h3>
                </div>
            </center>
            
            <table id="customers" border="1">
            <tr>
                <th>No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Revision</th>
                <th>Month</th>
                <th>Year</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Product Family</th>
                <th>Used</th>
                <th>Uom</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Adj %</th>
                <th>Price Adjust</th>
                <th>Material Cost</th>
                <th>Total</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['revision'] . '</td>
                        <td>' . $data['p_month'] . '</td>
                        <td>' . $data['p_year'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                        <td>' . $data['product_family'] . '</td>
                        <td>' . $data['used'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['adj'] . '</td>
                        <td>' . $data['adj_price_nominal'] . '</td>
                        <td>' . $data['material_cost'] . '</td>
                        <td>' . $data['total_material_cost'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}