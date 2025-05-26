<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supply_sheets extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/supply_sheets');
        } else {
            redirect('error_access');
        }
    }

    // public function bpiPeriod()
    // {
    //     $this->bpi = $this->load->database('bpi', TRUE);
        
    //     $this->bpi->select("TO_CHAR(issudate::date, 'yyyymm') as period");
    //     $this->bpi->from('worko');
    //     $this->bpi->group_by("TO_CHAR(issudate::date, 'yyyymm')");
    //     // $this->bpi->like("TO_CHAR(datesupply::date, 'yyyymm')", "2024");
    //     $this->bpi->order_by("period", "desc");
    //     $data = $this->bpi->get()->result_array();

    //     die(json_encode($data));
    // }

    // public function bpiWp($period)
    // {
    //     $this->bpi = $this->load->database('bpi', TRUE);
        
    //     $this->bpi->select("lotno as wp");
    //     $this->bpi->from('worko');
    //     $this->bpi->where("TO_CHAR(issudate::date, 'yyyymm') = '$period'");
    //     $this->bpi->group_by("lotno");
    //     $this->bpi->order_by("lotno", "asc");
    //     $data = $this->bpi->get()->result_array();

    //     die(json_encode($data));
    // }

    // public function bpiWo($period, $wp)
    // {
    //     $this->bpi = $this->load->database('bpi', TRUE);
        
    //     $this->bpi->select("wo_no as workorder, partno as product_no");
    //     $this->bpi->from('worko');
    //     $this->bpi->where("TO_CHAR(issudate::date, 'yyyymm') = '$period'");
    //     $this->bpi->where("lotno = '$wp'");
    //     $this->bpi->group_by("wo_no, partno");
    //     $this->bpi->order_by("wo_no", "asc");
    //     $data = $this->bpi->get()->result_array();

    //     die(json_encode($data));
    // }

    public function bpiPeriod()
    {
        $query = $this->db->query("SELECT DISTINCT period FROM production_schedules ORDER BY period desc");
        $result = $query->result();

        echo json_encode($result);
    }

    public function bpiWp($period)
    {
        $send = $this->crud->query("SELECT DISTINCT `period`, lot_no 
            FROM production_schedules
            WHERE `status` = 0  and period = '$period' 
            ORDER BY lot_no ASC");
        echo json_encode($send);
    }

    public function bpiWps()
    {
        $send = $this->crud->query("SELECT DISTINCT `period`, lot_no 
            FROM production_schedules
            WHERE `status` = 0  
            ORDER BY lot_no ASC");
        echo json_encode($send);
    }

    // public function bpiWo($period, $lot_no)
    // {
    //     $send = $this->crud->query("SELECT DISTINCT `period`, lot_no, wo_no
    //         FROM production_schedules
    //         WHERE `status` = 0  and period = '$period' and lot_no = '$lot_no' 
    //         ORDER BY wo_no ASC");
    //     echo json_encode($send);
    // }

    // public function bpiWo($period)
    // {
    //     $send = $this->crud->query("SELECT DISTINCT b.period, b.lot_no, b.wo_no, a.id as item_fg_id, a.number as item_fg_number
    //         FROM item_fg a
    //         JOIN production_schedules b on a.id = b.item_fg_id
    //         LEFT JOIN supply_sheets c on b.wo_no = c.workorder 
    //         WHERE a.status = 0 and c.qty_bal > 0 and b.period = '$period'
    //         ORDER BY b.wo_no ASC");
    //     echo json_encode($send);
    // }

    public function bpiWo($period)
    {
        $send = $this->crud->query("SELECT DISTINCT b.period, b.lot_no, b.wo_no, a.id as item_fg_id, a.number as item_fg_number
            FROM item_fg a
            JOIN production_schedules b on a.id = b.item_fg_id
            LEFT JOIN supply_sheets c on b.wo_no = c.workorder 
            WHERE a.status = 0 
            AND (c.qty_bal IS NULL OR c.qty_bal != 0)
            AND b.period = '$period'
            ORDER BY b.wo_no ASC");
        echo json_encode($send);
    }

    public function readItems($wo_no)
    {
        $wono = base64_decode($wo_no);
        $send = $this->crud->query("SELECT DISTINCT a.id as item_fg_id, a.number as item_number, a.name as item_name  
            FROM item_fg a
            JOIN production_schedules b on a.id = b.item_fg_id
            WHERE a.status = 0 and b.wo_no = '$wono'");
        echo json_encode($send);
    }

    // public function checkProduct()
    // {
    //     $product_no = $this->input->post('product_no');

    //     $item_fg = $this->crud->read('item_fg', [], ['number' => $product_no]);
        
    //     if ($item_fg) {
    //         $this->db->select('item_fg_id');
    //         $this->db->from('customer_items');
    //         $this->db->where('item_fg_id', $item_fg->id);
    //         $result = $this->db->get()->row_array();
            
    //         echo json_encode($result);
    //     } else {
    //         echo json_encode(['error' => 'Product not found']);
    //     }
    // }

    // public function readPeriod()
    // {
    //     $send = $this->crud->query("SELECT a.period
    //     FROM production_schedules a 
    //     WHERE a.status = 1
    //     GROUP BY a.period
    //     ORDER BY a.period DESC");

    //     echo json_encode($send);
    // }

    public function readMPQ($item)
    {
        $item_id = base64_decode($item);
        $send = $this->crud->query("SELECT b.name, a.mpq FROM supplier_items a JOIN suppliers b ON a.supplier_id = b.id WHERE a.item_rm_id = '$item_id'");
        echo json_encode($send);
    }

    // public function readWp()
    // {
    //     $period = base64_decode($this->input->get('period'));
    //     $wp = base64_decode($this->input->get('wp'));

    //     $send = $this->crud->query("SELECT a.wp
    //     FROM production_schedules a 
    //     WHERE a.status = 1 and a.period = '$period'
    //     GROUP BY a.wp
    //     ORDER BY a.wp DESC");

    //     echo json_encode($send);
    // }

    public function readRequestNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT request_no FROM supply_sheets WHERE request_no LIKE '%$post%'");
        echo json_encode($send);
    }

    public function readWoNo()
    {
        $send = $this->crud->query("SELECT DISTINCT workorder FROM supply_sheets WHERE `deleted` = '0' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }


    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM supply_sheets WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "SH-" . $datenow . "-" . $autoID;
    }

   
    public function datatables()
    {
        // Ambil filter dari input GET/POST
        $filter_supply_type = $this->input->get('filter_supply_type');
        $filter_period = $this->input->get('filter_period');
        $filter_lot_no = $this->input->get('filter_lot_no');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_wo_no = $this->input->get('filter_wo_no');
        $filter_status = $this->input->get('filter_status');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_status_subcont = $this->input->get('filter_status_subcont');
        $filter_subcont_type = $this->input->get('filter_subcont_type');

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
        $this->db->select('a.*, SUM(a.qty_req) as qty_req2, SUM(a.qty_act) as qty_act2, SUM(a.qty_bal) as qty_bal2, b.number as item_fg_number,
        b.name as item_fg_name, b.uom, b.status_subcont, b.subcont_type, g.qty_issued2, COALESCE(SUM(c.qty),0) as qty_actual, COUNT(a.status) as total_status, i.total_status_open, h.total_status_close');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('issued_material_details c', 'a.request_no = c.request_no and a.item_rm_id = c.item_rm_id','left');
        $this->db->join("(SELECT a.request_no, a.item_rm_id, SUM(a.qty) as qty, b.qty_req, (SUM(a.qty) - b.qty_req) as qty_issued2 
        FROM issued_material_details a 
        JOIN supply_sheets b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
        GROUP BY a.request_no, a.item_rm_id
        HAVING (SUM(a.qty) - b.qty_req) < 0) g", "a.request_no = g.request_no", "LEFT");
        $this->db->join('(SELECT a.request_no, COUNT(a.status) as total_status_close FROM supply_sheets a JOIN bom b ON a.item_fg_id = b.item_fg_id and a.item_rm_id = b.item_rm_id WHERE a.status = 1 GROUP BY a.request_no) h', 'a.request_no = h.request_no', 'left');
        $this->db->join('(SELECT a.request_no, COUNT(a.status) as total_status_open FROM supply_sheets a JOIN bom b ON a.item_fg_id = b.item_fg_id and a.item_rm_id = b.item_rm_id WHERE a.status = 0 GROUP BY a.request_no) i', 'a.request_no = i.request_no', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }

        if ($filter_period != "") {
            $this->db->where('a.period', $filter_period);
        }

        if ($filter_lot_no != "") {
            $this->db->where('a.lot_no', $filter_lot_no);
        }

        if ($filter_wo_no != "") {
            $this->db->where('a.workorder', $filter_wo_no);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_status_subcont != "") {
            $this->db->where('b.status_subcont', $filter_status_subcont);
        }

        if ($filter_subcont_type != "") {
            $this->db->where('b.subcont_type', $filter_subcont_type);
        }

        // if ($filter_status != "") {
        //     $this->db->where('a.status', $filter_status);
        // }

        if ($filter_status != "") {
            // Jika filter status adalah '0' (Open)
            if ($filter_status == "0") {
                $this->db->where('i.total_status_open >=', 1);  // Status open
            }
            // Jika filter status adalah '1' (Close)
            elseif ($filter_status == "1") {
                $this->db->where('h.total_status_close >=', 1); // Status close
            }
        }

        $this->db->group_by('a.request_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by($sort, $order);
        $totalRows = $this->db->count_all_results('', false);

        // Batasi hasil query sesuai pagination
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        // Inisialisasi array untuk menyimpan hasil akhir
        $arr = [];

        foreach ($records as $record) {

            if ($record['total_status'] == $record['total_status_open']) {
                $status = "0";
            } elseif ($record['total_status'] == $record['total_status_close']) {
                $status = "1";
            } elseif ($record['total_status_open'] >= 1) {
                $status = "0";
            } elseif ($record['total_status_close'] >= 1) {
                $status = "1";
            } else {
                $status = "0";
            }

            // Simpan data ke array hasil akhir
            $arr[] = array(
                "request_no" => $record['request_no'],
                "request_date" => $record['request_date'],
                "request_name" => $record['request_name'],
                "lot_no" => $record['lot_no'],
                "workorder" => $record['workorder'],
                "item_fg_number" => $record['item_fg_number'],
                "item_fg_name" => $record['item_fg_name'],
                "uom" => $record['uom'],
                "status" => $status,
                "status_subcont" => $record['status_subcont'],
                "subcont_type" => $record['subcont_type'],
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


    public function datatableDetails($request_no)
    {
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_period = $this->input->get('filter_period');
        $filter_lot_no = $this->input->get('filter_lot_no');
        $request_no = base64_decode($request_no);
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_status = $this->input->get('filter_status');

        $this->db->select('a.*, b.number as item_number, b.name as item_name, a.mpq, a.qty_wo,
                c.id as item_rm_id,
                c.number as item_rm_no, 
                c.name as item_rm_name, 
                j.name as item_rm_family, 
                f.recyle, 
                COALESCE(((f.recyle/100) * a.qty_req),0) as req_qty_crusher, 
                a.qty_purging as qty_purging, 
                (CASE WHEN g.uom_soft is null THEN d.name ELSE h.name END) as uom,
                (CASE WHEN g.uom_soft is null THEN f.composition ELSE (f.composition * g.convertion) END) as composition,
                COALESCE(i.qty_issued, 0) as qty_issued,
                f.composition, 
                COALESCE(SUM(k.qty),0) as qty_actual,
                round((CASE WHEN g.uom_soft is null THEN f.composition ELSE (f.composition * g.convertion) END) * e.qty, 4) as need
                ');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('uom d', 'c.uom = d.name');
            $this->db->join('production_schedules e', 'a.workorder = e.wo_no and a.item_fg_id = e.item_fg_id');
            // $this->db->join('purchase_order_receipt_crushers e', 'c.id = e.item_rm_id','left');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id', 'LEFT');
            $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
            $this->db->join('uom h', 'g.uom_soft = h.name', 'left');
            $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
            // $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as issued_qty_crusher FROM issued_material_details GROUP BY request_no, item_rm_id) k", "k.request_no = a.request_no and k.item_rm_id = e.item_rm_id", "LEFT");
            $this->db->join('item_familys j', 'c.item_family_id = j.id', 'left');
            $this->db->join('issued_material_details k', 'a.request_no = k.request_no and a.item_rm_id = k.item_rm_id','left');
            $this->db->where('a.deleted', 0);
            // $this->db->where('a.status', 0);
            $this->db->where('a.request_no', $request_no);

            if ($filter_request_no != "") {
                $this->db->where('a.request_no', $filter_request_no);
            }

            if ($filter_period != "") {
                $this->db->where('a.period', $filter_period);
            }

            if ($filter_lot_no != "") {
                $this->db->where('a.lot_no', $filter_lot_no);
            }

            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }

            if ($filter_status != "") {
                $this->db->where('a.status', $filter_status);
            }

            // if ($filter_status = "0") {
            //     $this->db->where('a.status', 0);
            // }else if ($filter_status = "1"){
            //     $this->db->where('a.status', 1);
            // }

            
            $this->db->group_by('a.workorder');
            $this->db->group_by('a.item_rm_id');
            $records = $this->db->get()->result_array();

            foreach ($records as $record) {
                $issued_qty_crusher = 0;
                $issued_qty_peletizing = 0;
            
                $item_number = $record['item_rm_no'];
            
                // Cek untuk CR-
                $query_cr = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', '$item_number')");
                $new_cr = $query_cr->row_array();
            
                // Cek untuk PL-
                $query_pl = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', '$item_number')");
                $new_pl = $query_pl->row_array();

            
                if ($new_cr) {
                    // Mengambil data issued quantity untuk Crusher
                    $this->db->select('SUM(qty) as issued_qty_crusher');
                    $this->db->from('issued_material_details');
                    $this->db->where('item_rm_id', $new_cr['id']);
                    $this->db->where('request_no', $record['request_no']);
                    $issued_material = $this->db->get()->row_array();
            
                    $issued_qty_crusher = $issued_material ? $issued_material['issued_qty_crusher'] : 0;
                }

                if ($new_pl) {
                    // Mengambil data issued quantity untuk Peletizing
                    $this->db->select('SUM(qty) as issued_qty_peletizing');
                    $this->db->from('issued_material_details');
                    $this->db->where('item_rm_id', $new_pl['id']);
                    $this->db->where('request_no', $record['request_no']);
                    $issued_material = $this->db->get()->row_array();
            
                    $issued_qty_peletizing = $issued_material ? $issued_material['issued_qty_peletizing'] : 0;
                }

                // Menentukan status berdasarkan jumlah yang dikeluarkan dan jumlah yang diminta
                $status = ($record['qty_req'] == ($record['qty_actual'] + $issued_qty_crusher + $issued_qty_peletizing)) ? '1' : '0';
               
                // var_dump($status);
                $arr[] = array(
                    "id" => $record['id'],
                    "request_no" => $record['request_no'],
                    "request_date" => $record['request_date'],
                    "request_name" => $record['request_name'],
                    // "period" => $record['period'],
                    // "wp" => $record['wp'],
                    "workorder" => $record['workorder'],
                    "item_fg_id" => $record['item_fg_id'],
                    "item_rm_id" => $record['item_rm_id'],
                    "item_rm_number" => $record['item_rm_no'],
                    "item_rm_name" => $record['item_rm_name'],
                    "item_rm_family" => $record['item_rm_family'],
                    "qty_wo" => $record['qty_wo'],
                    "qty_req" => $record['qty_req'],
                    "qty_act" => $record['qty_act'],
                    "qty_bal" => $record['qty_bal'],
                    "need" => $record['need'],
                    // "req_qty_crusher" => $record['req_qty_crusher'],
                    "qty_purging" => $record['qty_purging'],
                    "issued_qty_crusher" => $issued_qty_crusher,
                    "issued_qty_peletizing" => $issued_qty_peletizing,
                    // "os_qty_crusher" => ($record['req_qty_crusher'] - $issued_qty_crusher),
                    "qty_issued" => $record['qty_issued'],
                    "qty_issued_bal" => ($record['qty_req'] - $record['qty_issued']),
                    "composition" => $record['composition'],
                    // "qty" => $record['qty'],
                    "mpq" => $record['mpq'],
                    "uom" => $record['uom'],
                    // "supply_type" => $supply_type,
                    // "status" => $status,
                    "status" => $record['status'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date']
                );
            }
            $result = !empty($arr) ? $arr : [];
            echo json_encode($result);
    }

    public function datatablesTemp()
    {
        // $this->bpi = $this->load->database('bpi', TRUE);
        $workorder = base64_decode($this->input->get('workorder'));
        $item_fg_id = $this->input->get('item_fg_id');
        $item_fg_number = $this->input->get('item_fg_number');
        // $operation = $this->input->get('operation');

        //Select Query
        $this->db->select("
            (CASE WHEN b.item_rm_id is null THEN b.item_fg_sa_id ELSE b.item_rm_id END) as item_rm_id,
            (CASE WHEN b.item_rm_id is null THEN gg.number ELSE c.number END) as item_rm_no,
            (CASE WHEN b.item_rm_id is null THEN gg.name ELSE c.name END) as item_rm_name,
            e.qty, 
            COALESCE(h.mpq, 1) as mpq, 
            COALESCE(i.warehouse, 0) as warehouse, 
            (CASE WHEN b.item_rm_id is null THEN gg.uom ELSE c.uom END) as uom,
            (CASE WHEN f.uom_soft is null THEN b.composition ELSE (b.composition * f.convertion) END) as qpa,
            COALESCE(e.total_purging, 0) as total_purging,

            CASE 
                WHEN h.share_order = '100' AND h.calculate = 'YES' THEN 
                    CASE 
                        WHEN MOD(
                            CASE 
                                WHEN f.uom_soft IS NULL THEN ROUND(e.qty * b.composition, 4)
                                ELSE ROUND(e.qty * (b.composition * f.convertion), 4)
                            END + COALESCE(e.total_purging, 0), h.mpq
                        ) = 0 THEN 
                            CASE 
                                
                                WHEN f.uom_soft IS NULL THEN ROUND(e.qty * b.composition, 4)
                                ELSE ROUND(e.qty * (b.composition * f.convertion), 4)
                            END + COALESCE(e.total_purging, 0) 
                        ELSE 
                            CEIL(
                                (CASE 
                                   
                                    WHEN f.uom_soft IS NULL THEN ROUND(e.qty * b.composition, 4)
                                    ELSE ROUND(e.qty * (b.composition * f.convertion), 4)
                                END + COALESCE(e.total_purging, 0)) / h.mpq
                            ) * h.mpq
                    END
                ELSE 
                    CASE 
                        
                        WHEN f.uom_soft IS NULL THEN ROUND(e.qty * b.composition, 4)
                        ELSE ROUND(e.qty * (b.composition * f.convertion), 4)
                    END + COALESCE(e.total_purging, 0)
            END AS qty_req,
            round(((CASE WHEN f.uom_soft is null THEN b.composition ELSE (b.composition * f.convertion) END) * e.qty) + COALESCE(e.total_purging, 0), 4 ) as qty_act,
            round((CASE WHEN f.uom_soft is null THEN b.composition ELSE (b.composition * f.convertion) END) * e.qty, 4) as qty_real, 
        ");

        $this->db->from('bom b');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id','left');
        $this->db->join('item_fg g', 'b.item_fg_id = g.id','left');
        $this->db->join('item_fg gg', 'b.item_fg_sa_id = gg.id','left');
        $this->db->join('supply_sheets d', "d.workorder = '$workorder' and c.id = d.item_rm_id", 'left');
        $this->db->join('production_schedules e', "e.wo_no = '$workorder' and g.id = e.item_fg_id",'left');
        $this->db->join('convertions f', 'b.item_rm_id = f.item_rm_id', 'left');
        $this->db->join('supplier_items h', 'c.id = h.item_rm_id','left');
        $this->db->join('supply_sheets dd', "c.id = dd.item_rm_id", 'left');
        $this->db->join('wip_balances i', 'dd.item_rm_id = i.item_rm_id','left');
        
        $this->db->like('h.share_order', 100);
        $this->db->where('b.item_fg_id', $item_fg_id);
       
        $this->db->group_by('c.id');
        $records = $this->db->get()->result_object();

        echo json_encode($records);
    }

    // public function create() // jangan di rubah
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post = $this->input->post();
    //             $item_rm_id = $post['item_rm_id'];
    //             $qty_act = $post['qty_act'];
    //             $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");
    //             $supplier_items = $this->crud->read("supplier_items", [], ["item_rm_id" => $item_rm_id]);
    //             $supply_sheets = $this->crud->reads("supply_sheets", [], ["workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
    //             $date = date("Y-m-t");

    //             //Stock kWarehouse RM
    //             $stockWarehouse = $this->crud->query("SELECT 
    //             a.id, 
    //             a.number, 
    //             ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
    //             (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
    //                         FROM item_rm a
    //                         LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date <= '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
    //                         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
    //                         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
    //                         LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date <= '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
    //                         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
    //                         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
    //                         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
    //             WHERE a.id like '$item_rm_id'
    //             GROUP BY a.id
    //             ORDER BY a.number");

    //             if (count($supply_sheets) > 0) {
    //                 echo json_encode(array(
    //                     "theme" => "error",
    //                     "message" => "Duplicate"
    //                 ));
    //                 return;
    //             } else {
    //                 $qIssued = $this->db->query("SELECT COALESCE(SUM(a.qty), 0) + COALESCE(h.qty_stock_rm, 0) as balance 
    //                     FROM scan_item_receipts a 
    //                     JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
    //                     LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
    //                         FROM os_rm a
    //                         JOIN item_rm b ON a.item_rm_id = b.id
    //                         WHERE a.trans_date < '$dateNow'
    //                         GROUP BY a.item_rm_id) h ON b.item_rm_id = h.item_rm_id
    //                     WHERE b.item_rm_id = '$item_rm_id' 
    //                     GROUP BY b.item_rm_id");
    //                 $dIssued = $qIssued->row();

    //                 if (!empty($wip_balances->balance)) {
    //                     if ($qty_act >= $wip_balances->balance) {
    //                         //kalo items di hitung mpq
    //                         if (@$supplier_items->calculate == 'YES') {
    //                             $begin = $wip_balances->balance;
    //                             $need = $qty_act;
    //                             $issued = (ceil(($qty_act - $wip_balances->balance) / @$post['mpq']) * @$post['mpq']);
    //                             $balance = (($wip_balances->balance + $issued) - $qty_act);
    //                             $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
    //                         } elseif (@$supplier_items->calculate == 'NO') {
    //                             $begin = $wip_balances->balance;
    //                             $need = $qty_act;
    //                             $issued = abs(($qty_act - $wip_balances->balance));
    //                             $balance = ($issued - $qty_act);
    //                             $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
    //                         } else {
    //                             $begin = $wip_balances->balance;
    //                             $need = $post['qty_act'];
    //                             $issued = 0;
    //                             $balance = 0;
    //                             $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
    //                         }
    //                     } else {
    //                         $begin = $wip_balances->balance;
    //                         $need = $qty_act;
    //                         $issued = 0;
    //                         $balance = (($wip_balances->balance + $issued) - $qty_act);
    //                         $warehouse = (@$stockWarehouse[0]->end_stock);
    //                     }
    //                 } else {
    //                     if ($qty_act >= @$post['mpq']) {
    //                         $begin = 0;
    //                         $need = $qty_act;
    //                         $issued = $qty_act;
    //                         $balance = 0;
    //                                             //0            // 25
    //                         $warehouse = (@$dIssued->balance - $issued);
    //                     } else {
    //                         if (@$supplier_items->calculate == 'YES') {
    //                             $begin = 0;
    //                             $need = $qty_act;
    //                             $issued = @$post['mpq'];
    //                             $balance = (@$post['mpq'] - $qty_act);
    //                             $warehouse = (@$dIssued->balance - $issued);
    //                         }else{
    //                             $begin = 0;
    //                             $need = $post['qty_act'];
    //                             $issued = @$post['qty_act'];
    //                             $balance = 0;
    //                             $warehouse = (@$dIssued->balance - $issued);
    //                         }
    //                     }
    //                 }

    //                 if ($qty_act == 0) {
    //                     echo json_encode(array("title" => "Qty 0", "message" => $post['item_rm_id'] . " Qty 0", "theme" => "error"));
    //                 } else {
    //                     $balance = $this->crud->create("wip_balances", [
    //                         "item_rm_id" => $item_rm_id,
    //                         "request_no" => $post['request_no'],
    //                         "begin" => $begin,
    //                         "need" => $need,
    //                         "issued" => $issued,
    //                         "balance" => $balance,
    //                         "warehouse" => $warehouse
    //                     ]);

    //                     $send   = $this->crud->create('supply_sheets', array_merge($post, array("qty_issued" => $issued)));
    //                     // if ($post['qty_bal'] == 0) {
    //                     //     $this->db->update("production_schedules", ["status" => 1], ["workorder" => $post['workorder']]);
    //                     //     echo $send;
    //                     // } else {
    //                     //     $this->db->update("production_schedules", ["status" => 0], ["workorder" => $post['workorder']]);
    //                     //     echo $send;
    //                     // }
    //                     echo $send;
    //                 }
    //             }
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create() // dokumentasi : penyesuaian dengan Wip Balance
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $item_rm_id = $post['item_rm_id'];
                $qty_act = $post['qty_act'];
                $qty_req = $post['qty_req'];
                $qty_real = $post['qty_real'];
                $query = "SELECT * FROM wip_balances WHERE item_rm_id = ? ORDER BY created_date DESC, id DESC LIMIT 1";
                $result = $this->db->query($query, [$item_rm_id])->row();
                
                $supply_sheets = $this->crud->reads("supply_sheets", [], ["workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                $date = date("Y-m-t");

                //Stock kWarehouse RM
                $stockWarehouse = $this->crud->query("SELECT 
                a.id, 
                a.number, 
                ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
                (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
                            FROM item_rm a
                            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date <= '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date <= '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                WHERE a.id like '$item_rm_id'
                GROUP BY a.id
                ORDER BY a.number");

                if (count($supply_sheets) > 0) {
                    echo json_encode(array(
                        "theme" => "error",
                        "message" => "Duplicate"
                    ));
                    return;
                } else {
                    
                    $begin = $result ? $result->balance : 0;

                    $need = $qty_act;
                    
                    if ($begin >= $qty_real) {
                        $supply = 0;
                    } else {
                        $supply = $qty_req;
                    }
                    
                    $balance = $begin + $supply - $need;

                    if ($qty_act == 0) {
                        echo json_encode(array("title" => "Qty 0", "message" => $post['item_rm_id'] . " Qty 0", "theme" => "error"));
                    } else {
                        $balance = $this->crud->create("wip_balances", [
                            "item_rm_id" => $item_rm_id,
                            "request_no" => $post['request_no'],
                            "begin" => $begin,
                            "need" => $qty_act,
                            "issued" => $supply,
                            "balance" => $balance,
                            "qty_real" => $qty_real,
                            "warehouse" => $stockWarehouse[0]->ending_stock
                        ]);


                        $send   = $this->crud->create('supply_sheets', array_merge($post, array("qty_issued" => $supply)));
                        echo $send;
                    }
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('supply_sheets', ["request_no" => $data['request_no']]);
        // $update = $this->crud->update('production_schedules', ["workorder" => $data['workorder'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
        $delete = $this->crud->delete('wip_balances', ["request_no" => $data['request_no']]);
        echo $send;
    }

    public function print_kanban($request_no)//, $operation
    {
        $requestno = base64_decode($request_no);

        $this->db->select('a.*');
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('uom d', 'c.uom = d.name');
        $this->db->join('bom e', 'a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id');
        $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id", 'left');
        $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id','left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        // $this->db->where('e.operation', base64_decode($operation));
        $this->db->like('a.request_no', $requestno);
        $this->db->group_by('c.number');
        $supply_sheet_total = $this->db->get()->result_array();

        // var_dump($supply_sheet_total);
        // exit;

        $kanban = $this->crud->read('supply_sheets', [], ["request_no" => $requestno]);
        // $production_schedule = $this->crud->read('production_schedules', [], ["workorder" => $kanban->workorder]);
        $product = $this->crud->read('item_fg', [], ["id" => $kanban->item_fg_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 8;
        $page = ceil(count($supply_sheet_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, 
            (CASE WHEN a.mpq > 0 THEN a.mpq ELSE g.mpq END) as mpq, 
            c.number as item_rm_no, 
            c.name as item_rm_name, 
            e.composition, 
            f.location, 
            c.uom, 
            COALESCE(i.qty_issueds, 0) as qty_issueds,
            COALESCE(((e.recyle/100) * a.qty_req),0) as req_qty_crusher, 
            a.qty_purging as req_qty_purging,
            round((CASE WHEN j.uom_soft is null THEN e.composition ELSE (e.composition * j.convertion) END) * h.qty, 4) as need');
            $this->db->from('supply_sheets a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('uom d', 'c.uom = d.name');
            $this->db->join('bom e', 'a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id');
            $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id", 'left');
            $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id','left');
            $this->db->join('production_schedules h', 'a.workorder = h.wo_no and a.item_fg_id = h.item_fg_id');
            $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issueds FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
            $this->db->join('convertions j', 'a.item_rm_id = j.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);
            // $this->db->where('a.status', 0);
            // $this->db->where('e.operation', base64_decode($operation));
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->group_by('c.number');
            $this->db->order_by('c.number', 'ASC');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();
            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $kanban->request_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_supply_sheet . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_supply_sheet . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>SUPPLY SHEET</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Doc. No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Doc. Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->request_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Created By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">WO ID</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->workorder . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Product No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$product->number . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Lot No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->lot_no .  '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">WO Qty</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->qty_wo . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y") . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$this->session->name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Part No</th>
                                        <th>Part Name</th>
                                        <th>QPA</th>
                                        <th>Uom</th>
                                        <th>MPQ</th>
                                        <th>Need</th>
                                        <th>Qty Purging</th>
                                        <th>Supply</th>
                                        <th>Issued</th>
                                        <th>Balance WIP</th>
                                        <th>Stock WHS</th>
                                        <th width="10">WHS Location</th>
                                    </tr>';
            foreach ($records as $record) {
                $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $record['item_rm_id'], "request_no" => $record['request_no']], "", "id", "desc");
                
                $date = date("Y-m-t");
                $item_rm_id = $record['item_rm_id'];
                $query = $this->crud->query("SELECT 
                a.id, 
                a.number, 
                ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
                (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
                            FROM item_rm a
                            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date <= '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date <= '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                WHERE a.id like '$item_rm_id'
                GROUP BY a.id
                ORDER BY a.number");

                if ($query && count($query) > 0) {
                    $ending_stock = $query[0]->ending_stock;
                } else {
                    $ending_stock = 0;
                }
                
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_no'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td style="text-align:right;">' . $record['composition'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['mpq'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['need'], 4) . '</td>
                                <td style="text-align:right;">' . @number_format($record['req_qty_purging'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['qty_req'], 4) . '</td>
                                <td style="text-align:right;">' . @number_format($record['qty_issueds'], 4) . '</td>
                                <td style="text-align:right;">' . @number_format(($wip_balances->balance), 4) . '</td>
                                <td style="text-align:right;">' . @number_format(($ending_stock), 4) . '</td>
                                <td>' . $record['location'] . '</td>
        

                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th>Production</th>
                        <th>Warehouse</th>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px;"></td>
                        <td style="height:20px;"></td>
                    </tr>
                </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function label_supply($request_no)//, $operation
    {
        $requestno = base64_decode($request_no);

        $this->db->select('a.*');
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('uom d', 'c.uom = d.name');
        $this->db->join('bom e', 'a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id');
        $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id", 'left');
        $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id','left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        // $this->db->where('e.operation', base64_decode($operation));
        $this->db->like('a.request_no', $requestno);
        $this->db->group_by('c.number');
        $supply_sheet_total = $this->db->get()->result_array();

        // var_dump($supply_sheet_total);
        // exit;

        $kanban = $this->crud->read('supply_sheets', [], ["request_no" => $requestno]);
        // $production_schedule = $this->crud->read('production_schedules', [], ["workorder" => $kanban->workorder]);
        $product = $this->crud->read('item_fg', [], ["id" => $kanban->item_fg_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 8;
        $page = ceil(count($supply_sheet_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        // for ($i = 0; $i < $page; $i++) {
        //     $this->db->select('a.request_no,
        //         a.item_rm_id, 
        //         f.number as item_rm_no, 
        //         f.name as item_rm_name, 
        //         f.uom, 
        //         h.lotno, 
        //         b.number as item_fg_no, 
        //         SUM(a.qty) as qty, 
        //         i.mpq, 
        //         c.workorder, 
        //         e.number as machine_no');
        //     $this->db->from('issued_material_details a');
        //     $this->db->join('supply_sheets c', 'a.request_no = c.request_no','left');
        //     $this->db->join('item_fg b', 'c.item_fg_id = b.id', 'left');
        //     $this->db->join('item_rm f', 'a.item_rm_id = f.id', 'left');
        //     $this->db->join('production_schedules d', 'c.workorder = d.wo_no AND c.item_fg_id = d.item_fg_id', 'left');
        //     $this->db->join('machines e', 'd.machine_id = e.id', 'left');
        //     $this->db->join('purchase_order_labels g', 'a.label_no = g.label_no', 'left');
        //     $this->db->join('purchase_order_receipts h', 'g.receipt_id = h.receipt_id', 'left');
        //     $this->db->join('supplier_items i', 'c.item_rm_id = i.item_rm_id AND i.share_order = 100', 'left');
        //     $this->db->where('a.deleted', 0);
        //     $this->db->like('a.request_no', base64_decode($request_no));
        //     $this->db->group_by('f.number');
        //     $this->db->order_by('f.number', 'ASC');
        //     $this->db->limit(8, ($i * 8));
        //     $records = $this->db->get()->result_array();

        //     $html .= '<table style="width:100%;">
        //                 </table>
        //                 <div style="border: 1px solid black; width:100%;">
        //                     <div style="padding:10px;">
        //                       <div style="float:left; width:70%;"> 
        //                             <table style="width:100%; font-size:12px; margin-bottom:10px; border: 1px solid black; height: 80px; text-align: justify;">
        //                                 <tr>
        //                                      <td style="text-align: center; font-size: 18px;">LABEL SUPPLY</td>
        //                                 </tr>
        //                             </table>
        //                         </div>
        //                         <div style="float:left; width:30%;"> 
        //                             <table style="width:100%; font-size:12px; margin-bottom:10px; border: 1px solid black; height: 80px; text-align: justify;">
        //                                 <tr>
        //                                     <td style="text-align: center;">FM-MCL-029-Rev01</td>
        //                                 </tr>
        //                             </table>
        //                         </div>
                                
        //                         <table id="customers">
        //                             <tr>
        //                                 <th rowspan="2" width="90px">Product No</th>
        //                                 <th colspan="2" rowspan="2">' . (isset($records[0]['item_fg_no']) ? $records[0]['item_fg_no'] : '') . '</th>
        //                                 <th>Wo no</th>
        //                                 <th colspan="2">' . (isset($records[0]['workorder']) ? $records[0]['workorder'] : '') . '</th>

        //                             </tr>
        //                             <tr>
        //                                 <th>Machine No</th>
        //                                 <th colspan="2">' . (isset($records[0]['machine_no']) ? $records[0]['machine_no'] : '') . '</th>
        //                             </tr>
        //                             <tr>
        //                                 <th>-</th>
        //                                 <th>Material No</th>
        //                                 <th>Material Name</th>
        //                                 <th>Qty</th>
        //                                 <th>U/M</th>
        //                                 <th>Lot No</th>
        //                             </tr>';

        //     $berat_total = 0;
        //     foreach ($records as $record) {
        //         $berat_total += $record['qty']; // Hitung total berat dengan menjumlahkan qty dari setiap record
        //     }
            
        //     // Menghitung qty_label menggunakan mpq dari data pertama sebagai contoh
        //     $mpq = isset($records[0]['mpq']) ? $records[0]['mpq'] : 1; // pastikan mpq ada, atau gunakan nilai default
        //     $qty_label = floor($berat_total / $mpq); // ROUNDDOWN(berat_total / mpq)
                                    
        //     foreach ($records as $record) {   
                
        //         $qty_final = $qty_label ? $record['qty'] / $qty_label : 0; 
                
        //         $html .= '  <tr>
        //                         <td style="text-align:center">' ."Material ". $no . '</td>
        //                         <td>' . $record['item_rm_no'] . '</td>
        //                         <td>' . $record['item_rm_name'] . '</td>
        //                         <td style="text-align:right;">' . number_format($qty_final, 2) . '</td>
        //                         <td style="text-align:right;">' . $record['uom'] . '</td>
        //                         <td style="text-align:right;">' . $record['lotno'] . '</td>
        //                     </tr>';
        //         $no++;
        //     }
        //         $html .= '</table>
        //                     <br>
        //                     <table id="customers">
        //                         <tr>
        //                             <th style="text-align: Left; width:70%;">PIC Name</th>
        //                             <th>Date</th>
        //                         </tr>
        //                         <tr>
        //                             <td style="height:60px;"></td>
        //                             <td style="height:60px;"></td>
        //                         </tr>
        //                     </div>
        //                 </div>';
        //     if (($i + 1) != $page) {
        //         $html .= '<div style="page-break-after:always;"></div>';
        //     }
        //     $hal++;
        // }
        // $html .= "</div></div><script>window.print()</script></body>";
        // die($html);

        for ($i = 0; $i < $page; $i++) {
            // Query records sesuai batasan halaman
            $this->db->select('a.request_no, a.item_rm_id, f.number as item_rm_no, f.name as item_rm_name, f.uom, h.lotno, b.number as item_fg_no, SUM(a.qty) as qty, i.mpq, c.workorder, e.number as machine_no');
            $this->db->from('issued_material_details a');
            $this->db->join('supply_sheets c', 'a.request_no = c.request_no', 'left');
            $this->db->join('item_fg b', 'c.item_fg_id = b.id', 'left');
            $this->db->join('item_rm f', 'a.item_rm_id = f.id', 'left');
            $this->db->join('production_schedules d', 'c.workorder = d.wo_no AND c.item_fg_id = d.item_fg_id', 'left');
            $this->db->join('machines e', 'd.machine_id = e.id', 'left');
            $this->db->join('purchase_order_labels g', 'a.label_no = g.label_no', 'left');
            $this->db->join('purchase_order_receipts h', 'g.receipt_id = h.receipt_id', 'left');
            $this->db->join('supplier_items i', 'c.item_rm_id = i.item_rm_id AND i.share_order = 100', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->group_by('f.number');
            $this->db->order_by('f.number', 'ASC');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();
        
            // Perhitungan qty_label dan distribusi qty per label
            $berat_total = array_sum(array_column($records, 'qty'));
            $mpq = isset($records[0]['mpq']) ? $records[0]['mpq'] : 1;
            $qty_label = floor($berat_total / $mpq);
        
            // Loop untuk menampilkan dua label supply per halaman
            for ($label_count = 1; $label_count <= $qty_label; $label_count++) {
                // Memulai blok HTML untuk setiap label supply
                $html .= '<div style="border: 1px solid black; width:100%; margin-bottom: 20px;">';
                $html .= '<table style="width:100%; font-size:12px; margin-bottom:10px; border: 1px solid black; height: 80px; text-align: center; border-collapse: collapse;">
                            <tr>
                                <td style="text-align: center; width: 70%; border: 2px solid black;">LABEL SUPPLY</td>
                                <td style="text-align: center; width: 30%; border: 2px solid black;">FM-MCL-029-Rev01</td>
                            </tr>
                        </table>';

        
                $html .= '<table id="customers">
                            <tr>
                                <th rowspan="2" width="90px">Product No</th>
                                <th colspan="2" rowspan="2">' . (isset($records[0]['item_fg_no']) ? $records[0]['item_fg_no'] : '') . '</th>
                                <th>Wo no</th>
                                <th colspan="2">' . (isset($records[0]['workorder']) ? $records[0]['workorder'] : '') . '</th>
                            </tr>
                            <tr>
                                <th>Machine No</th>
                                <th colspan="2">' . (isset($records[0]['machine_no']) ? $records[0]['machine_no'] : '') . '</th>
                            </tr>
                            <tr>
                                <th>-</th>
                                <th>Material No</th>
                                <th>Material Name</th>
                                <th>Qty</th>
                                <th>U/M</th>
                                <th>Lot No</th>
                            </tr>';
        
                foreach ($records as $record) {
                    $qty_final = $record['qty'] / $qty_label;
        
                    $html .= '<tr>
                                <td style="text-align:center">' . "Material " .'</td>
                                <td>' . $record['item_rm_no'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td style="text-align:right;">' . number_format($qty_final, 2) . '</td>
                                <td style="text-align:right;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . $record['lotno'] . '</td>
                              </tr>';
                    $no++;
                }
        
                $html .= '</table>
                          <br>
                          <table id="customers">
                              <tr>
                                  <th style="text-align: Left; width:70%;">PIC Name</th>
                                  <th>Date</th>
                              </tr>
                              <tr>
                                  <td style="height:60px;"></td>
                                  <td style="height:60px;"></td>
                              </tr>
                          </table>
                          </div>';
        
                // Tambahkan page break setelah setiap dua label kecuali halaman terakhir
                if ($label_count % 2 == 0 && $label_count < $qty_label) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
            }
            $hal++;
        }
        
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
        
        
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=supply_sheets_$format.xls");
        }

        $filter_supply_type = $this->input->get('filter_supply_type');
        $filter_period = $this->input->get('filter_period');
        $filter_lot_no   = $this->input->get('filter_lot_no');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_wo_no = $this->input->get('filter_wo_no');

        // $filter_operation = $this->input->get('filter_operation');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,
            b.number as item_number,
            c.id as item_rm_id,
            c.number as item_rm_no, 
            c.name as item_rm_name, 
            (CASE WHEN g.uom_soft is null THEN d.name ELSE h.name END) as uom,
            (CASE WHEN g.uom_soft is null THEN f.composition ELSE (f.composition * g.convertion) END) as composition,
            i.qty_issued as qty_issued,
            (i.qty_issued - a.qty_req) as qty_issued_bal,
            f.composition');
            // e.period, 
            // e.wp, 
            // e.qty
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('uom d', 'c.uom = d.name');
        // $this->db->join('production_schedules e', 'a.item_fg_id = e.item_fg_id and a.workorder = e.workorder');
        $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
        $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->join('uom h', 'g.uom_soft = h.name', 'left');
        $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
        // $this->db->where('a.deleted', 0);
        if ($filter_supply_type == "OPEN") {
            $this->db->where('(i.qty_issued - a.qty_req) <', 0);
        }elseif ($filter_supply_type == "CLOSE") {
            $this->db->where('(i.qty_issued - a.qty_req) =', 0);
        }
        if ($filter_period != "") {
            $this->db->where('a.period', $filter_period);
        }
        if ($filter_lot_no != "") {
            $this->db->where('a.lot_no', $filter_lot_no);
        }
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if ($filter_wo_no != "") {
            $this->db->where('a.workorder', $filter_wo_no);
        }
        // if ($filter_operation != "") {
        //     $this->db->where('f.operation', $filter_operation);
        // }
        $this->db->group_by('a.workorder');
        $this->db->group_by('a.item_rm_id');
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
                                <small>SUPPLY SHEET</small>
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
                <th>No</th>
                <th>Supply ID</th>
                <th>Status</th>
                <th>Supply Date</th>
                <th>Requester</th>
                <th>Period</th>
                <th>WP</th>
                <th>Work Order</th>
                <th>Product No</th>
                <th>Component No</th>
                <th>Component Name</th>
                <th>Uom</th>
                <th>Qpa</th>
                <th>WO qty</th>
                <th>Actual Qty</th>
                <th>Issued</th>
                <th>Outstanding</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            // if($data['qty_issued_bal'] < 0){
            //     $supply_type = "OPEN";
            // }else{
            //     $supply_type = "CLOSE";
            // }

            if($data['status'] == 0){
                $status = "OPEN";
            }else{
                $status = "CLOSE";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['period'] . '</td>
                        <td>' . $data['wp'] . '</td>
                        <td>' . $data['workorder'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                        <td>' . $data['item_rm_no'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['composition'] . '</td>
                        <td>' . $data['qty_wo'] . '</td>
                        <td>' . $data['qty_act'] . '</td>
                        <td>' . $data['qty_issued'] . '</td>
                        <td>' . $data['qty_issued_bal'] . '</td>
                    
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}