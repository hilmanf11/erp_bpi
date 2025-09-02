<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_orders extends CI_Controller
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
            $this->load->view('sales/delivery_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readSalesOrderDeliveries($sales_order)
    {
        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_deliveries WHERE `status` = '0' ORDER BY trans_date DESC");
            echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_delivery_rm WHERE `status` = '0'");
            echo json_encode($send);
        }
    }

    public function readsC($sales_order, $delivery_date)
    {
        $delivery_date = base64_decode($delivery_date);
        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_orders a
                JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                -- JOIN production_schedules d ON a.sales_order_no = d.so_number
                WHERE b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_order_rm a
                JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        }
    }

    public function readsCustOrderNo($sales_order, $customer_id, $delivery_date)
    {
        $delivery_date = base64_decode($delivery_date);
        $customer_id = base64_decode($customer_id);

        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT a.customer_order_no, a.division 
                    FROM sales_orders a 
                    JOIN customers c ON a.customer_id = c.id
                    JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                    WHERE a.customer_id= '$customer_id' and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");
                echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT a.customer_order_no, a.division 
                    FROM sales_order_rm a 
                    JOIN customers c ON a.customer_id = c.id
                    JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                    WHERE a.customer_id= '$customer_id' and a.status = 0 and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");
                echo json_encode($send);
        }
    }

    public function readDeliveryOrder($customer_id)
    {
        $send = $this->crud->query("SELECT delivery_order_no, customer_order_no, sales_order_no
            FROM delivery_orders
            WHERE customer_id = '$customer_id' 
            ORDER BY delivery_order_no DESC");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_order_no)
    {
        $send = $this->crud->query("SELECT customer_order_no
            FROM delivery_orders
            WHERE customer_order_no = '$customer_order_no' 
            ORDER BY customer_order_no DESC");
        echo json_encode($send);
    }

    public function readSalesOrder($sales_order_no)
    {
        $send = $this->crud->query("SELECT sales_order_no
            FROM delivery_orders
            WHERE sales_order_no = '$sales_order_no' 
            ORDER BY sales_order_no DESC");
        echo json_encode($send);
    }

    public function readDeliveryOrders()
    {
        $send = $this->crud->query("SELECT delivery_order_no
            FROM delivery_orders
            WHERE `deleted` = 0 
            ORDER BY delivery_order_no DESC");
        echo json_encode($send);
    }

    public function readCustomerOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no
            FROM delivery_orders
            WHERE `deleted` = 0 
            ORDER BY customer_order_no DESC");
        echo json_encode($send);
    }

    public function readSalesOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT COALESCE(sales_order_no,sales_order_no_rm) as sales_order_no
            FROM delivery_orders
            WHERE `deleted` = 0 
            ORDER BY sales_order_no DESC");
        echo json_encode($send);
    }

    public function number($delivery_order_date, $customer_no, $division)
    {
        $datenow    = "DO" . $customer_no . $division . date("ym", strtotime(base64_decode($delivery_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`delivery_order_no`) as kode FROM delivery_orders WHERE `delivery_order_no` like '%$datenow%' and division = '$division'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . $autoID;
    }

    public function datatablesTemp($sales_order, $delivery_date, $customer_id, $customer_order_no)//berubah
    {
        $delivery_date = base64_decode($delivery_date);
        $customer_id = base64_decode($customer_id);
        $customer_order_no = explode(",", base64_decode($customer_order_no));
        $date = date("Y-m-t");

        $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') <= '$date'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date <= '$date'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date <= '$date'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date <= '$date'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date <= '$date'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods2 = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') <= '$date'
        GROUP BY f.item_fg_id";

        // Step 8: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date <= '$date'
        GROUP BY i.item_fg_id";

        if($sales_order == "FG"){
            $this->db->select('b.item_fg_id, d.number as item_fg_number, d.name as item_fg_name, b.njo_number,
                b.customer_order_no,
                b.sales_order_no,
                d.uom, 
                b.division,
                COALESCE(f.qty_dn, 0) as qty_dn, 
                b.qty as qty_so, 
                a.qty as qty_sod, 
                CASE 
                    WHEN b.qty = COALESCE(g.qty, 0) THEN 0
                    WHEN COALESCE(h.qty, 0) > 0 THEN b.qty - ((a.qty - COALESCE(h.qty, 0)) + COALESCE(g.qty, 0))
                    ELSE (b.qty - COALESCE((g.qty + a.qty), a.qty)) 
                END as qty_remain, 

                COALESCE(h.qty, 0) as qty_remain_date,
                
                (b.qty - COALESCE(g.qty, 0)) as qty_do,

                CASE 
                    WHEN b.qty = COALESCE(g.qty, 0) THEN 0
                    WHEN COALESCE(h.qty, 0) > 0 THEN (a.qty - COALESCE(h.qty, 0))
                    ELSE a.qty
                END as qty_del,

                COALESCE(x.begin_stock, 0) as stock,
                COALESCE(g.qty, 0) as accum_qty_do,
                COALESCE(g.qty, 0) as accum_qty_do,
                COALESCE((x.begin_stock - c.qty_del),0) as stock_bal');
            $this->db->select('d.hs_code');
            $this->db->from('sales_orders b');
            $this->db->join('sales_order_deliveries a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');
            $this->db->join('delivery_orders c', 'b.sales_order_no = c.sales_order_no and b.item_fg_id = c.item_fg_id and b.customer_id = c.customer_id and a.trans_date = c.delivery_date', 'left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join("(SELECT b.item_fg_id, COALESCE(SUM(a.qty),0) as qty FROM scan_item_receipts_fg a JOIN wip_receipts b on a.checksheet_number = b.checksheet_number GROUP BY b.item_fg_id) e",'b.item_fg_id = e.item_fg_id','left');
            // $this->db->join('delivery_notes f', 'b.sales_order_no = f.sales_order_no and b.item_fg_id = f.item_fg_id','left');
            $this->db->join("(SELECT SUM(qty) as qty_dn, sales_order_no, item_fg_id FROM delivery_notes GROUP BY sales_order_no,item_fg_id) f",'b.sales_order_no = f.sales_order_no and b.item_fg_id = f.item_fg_id','left');
            $this->db->join("(SELECT sales_order_no, item_fg_id, COALESCE(SUM(qty_del),0) as qty FROM delivery_orders GROUP BY sales_order_no, item_fg_id) g", 'b.sales_order_no = g.sales_order_no and b.item_fg_id = g.item_fg_id','left');
            $this->db->join("(SELECT sales_order_no, item_fg_id,delivery_date, COALESCE(SUM(qty_del),0) as qty FROM delivery_orders GROUP BY sales_order_no, item_fg_id, delivery_date) h", 'b.sales_order_no = h.sales_order_no and b.item_fg_id = h.item_fg_id and a.trans_date = h.delivery_date','left');
            $this->db->join("(
                SELECT a.id AS item_fg_id,
                    (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + 
                     COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
                    (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
                FROM item_fg a
                LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
                LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
                LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
                LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
                LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
                LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
                LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id
                GROUP BY a.id
            ) x", 'b.item_fg_id = x.item_fg_id', 'left');
            $this->db->where('b.customer_id', $customer_id);
            $this->db->where('a.trans_date', $delivery_date);
            $this->db->where('a.status', 0);
            $this->db->where_in('b.customer_order_no', $customer_order_no);
            $this->db->group_by('b.item_fg_id');
            $this->db->group_by('b.sales_order_no');
            $this->db->order_by('b.item_fg_id', 'asc');
        }else{
            $this->db->select('b.item_fg_id, d.number as item_fg_number, d.name as item_fg_name, "" as njo_number,
                b.customer_order_no,
                b.sales_order_no,
                b.division,
                d.uom, 
                COALESCE(SUM(f.qty), 0) as qty_dn, 
                b.qty as qty_so, 
                a.qty as qty_sod, 
                CASE 
                    WHEN b.qty = COALESCE(g.qty, 0) THEN 0
                    WHEN COALESCE(h.qty, 0) > 0 THEN b.qty - ((a.qty - COALESCE(h.qty, 0)) + COALESCE(g.qty, 0))
                    ELSE (b.qty - COALESCE((g.qty + a.qty), a.qty)) 
                END as qty_remain, 

                COALESCE(h.qty, 0) as qty_remain_date,
                
                (b.qty - COALESCE(g.qty, 0)) as qty_do,

                CASE 
                    WHEN b.qty = COALESCE(g.qty, 0) THEN 0
                    WHEN COALESCE(h.qty, 0) > 0 THEN (a.qty - COALESCE(h.qty, 0))
                    ELSE a.qty
                END as qty_del,

                COALESCE(SUM(e.qty), 0) as stock,
                COALESCE(g.qty, 0) as accum_qty_do,
                COALESCE((e.qty - c.qty_del),0) as stock_bal');
            $this->db->select('d.hs_code');
            $this->db->from('sales_order_rm b');
            $this->db->join('sales_order_delivery_rm a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');
            $this->db->join('delivery_orders c', 'b.sales_order_no = c.sales_order_no and b.item_fg_id = c.item_fg_id and b.customer_id = c.customer_id and a.trans_date = c.delivery_date', 'left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join("(SELECT b.item_fg_id, COALESCE(SUM(a.qty),0) as qty FROM scan_item_receipts_fg a JOIN wip_receipts b on a.checksheet_number = b.checksheet_number GROUP BY b.item_fg_id) e",'b.item_fg_id = e.item_fg_id','left');
            $this->db->join('delivery_notes f', 'b.sales_order_no = f.sales_order_no and b.item_fg_id = f.item_fg_id','left');
            $this->db->join("(SELECT sales_order_no, item_fg_id, COALESCE(SUM(qty_del),0) as qty FROM delivery_orders GROUP BY sales_order_no, item_fg_id) g", 'b.sales_order_no = g.sales_order_no and b.item_fg_id = g.item_fg_id','left');
            $this->db->join("(SELECT sales_order_no_rm, item_fg_id,delivery_date, COALESCE(SUM(qty_del),0) as qty FROM delivery_orders GROUP BY sales_order_no_rm, item_fg_id, delivery_date) h", 'b.sales_order_no = h.sales_order_no_rm and b.item_fg_id = h.item_fg_id and a.trans_date = h.delivery_date','left');
            $this->db->where('a.status', 0);
            $this->db->where('b.customer_id', $customer_id);
            $this->db->where('a.trans_date', $delivery_date);
            $this->db->where_in('b.customer_order_no', $customer_order_no);
            $this->db->group_by('b.item_fg_id');
            $this->db->group_by('b.sales_order_no');
            $this->db->order_by('b.item_fg_id', 'asc');
        }

        // die("SELECT a.id AS item_fg_id,
        //             (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + 
        //              COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
        //             (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
        //         FROM item_fg a
        //         LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
        //         LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
        //         LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
        //         LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
        //         LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
        //         LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
        //         LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id
        //         GROUP BY a.id");
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_division = @base64_decode($get['filter_division']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name, 
            d.total_status_open, 
            c.total_status_close, 
            COUNT(a.status_scan) as total_status_scan,
            (CASE 
                WHEN d.total_status_open = COUNT(a.status_scan) THEN '0'
                WHEN c.total_status_close = COUNT(a.status_scan) THEN '1'
                WHEN d.total_status_open >= 1 THEN '0'
                WHEN c.total_status_close >= 1 THEN '1'
                ELSE '0'
            END) as status_scan_label");
            $this->db->from('delivery_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('(SELECT delivery_order_no, COUNT(status_scan) as total_status_close FROM delivery_orders WHERE status_scan = 1 GROUP BY delivery_order_no) c', 'a.delivery_order_no = c.delivery_order_no', 'left');
            $this->db->join('(SELECT delivery_order_no, COUNT(status_scan) as total_status_open FROM delivery_orders WHERE status_scan = 0 GROUP BY delivery_order_no) d', 'a.delivery_order_no = d.delivery_order_no', 'left');
            // $this->db->where("
            //     (CASE 
            //         WHEN a.sales_order_no IS NULL THEN a.sales_order_no_rm 
            //         ELSE a.sales_order_no 
            //     END) LIKE '%" . $filter_sales_order_no . "%'"
            // );

            if (!empty($filter_sales_order_no)) {
                $this->db->where("(a.sales_order_no LIKE '%{$filter_sales_order_no}%' OR a.sales_order_no_rm LIKE '%{$filter_sales_order_no}%')", NULL, FALSE);
            }

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_order_date >=', $filter_from);
                $this->db->where('a.delivery_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            // $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_order_no');
            $this->db->order_by('a.status', 'ASC');
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
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, 
            (CASE WHEN a.sales_order_no is null THEN a.sales_order_no_rm ELSE a.sales_order_no END) as sales_order_number');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, 
            g.qty as accum_qty_do,
            b.number as item_fg_number, 
            b.name as item_fg_name');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(SELECT sales_order_no, item_fg_id, COALESCE(SUM(qty_del),0) as qty FROM delivery_orders WHERE delivery_order_no != '$delivery_order_no' GROUP BY sales_order_no, item_fg_id) g", 'a.sales_order_no = g.sales_order_no and a.item_fg_id = g.item_fg_id','left');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            // var_dump($post);
            // die;

            if ($post['sales_order'] == 'FG') {
                $delivery_orders = $this->crud->read("delivery_orders", [], [
                    "delivery_order_no" => $post['delivery_order_no'],
                    "item_fg_id" => $post['item_fg_id'],
                    "sales_order_no" => $post['sales_order_no']
                ]);
    
                $query = "SELECT SUM(qty_del) AS total_qty_del
                    FROM delivery_orders
                    WHERE delivery_order_no = '{$post['delivery_order_no']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}'
                ";
                $result = $this->crud->query($query);
                $total_qty_del = $result[0]->total_qty_del ?? 0;
    
                $query2 = "SELECT qty 
                    FROM sales_order_deliveries
                    WHERE customer_id = '{$post['customer_id']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND trans_date = '{$post['delivery_date']}'
                ";
                $result2 = $this->crud->query($query2);
                $qty_so_date = $result2[0]->qty ?? 0;
    
                $query3 = "SELECT SUM(qty_del) AS total_qty_del_date
                    FROM delivery_orders
                    WHERE customer_order_no = '{$post['customer_order_no']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND delivery_date = '{$post['delivery_date']}'
                ";
                $result3 = $this->crud->query($query3);
                $total_qty_del_date = $result3[0]->total_qty_del_date ?? 0;

                $query4 = "SELECT SUM(qty_del) AS accum_qty_del
                    FROM delivery_orders
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' 
                ";
                $result4 = $this->crud->query($query4);
                $accum_qty_del = $result4[0]->accum_qty_del ?? 0;

                $query5 = "SELECT SUM(qty) AS shipping_qty
                    FROM shipping_orders
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND delivery_order_no = '{$post['delivery_order_no']}'
                ";
                $result5 = $this->crud->query($query5);
                $shipping_qty = $result5[0]->shipping_qty ?? 0;

                $query6 = "SELECT SUM(qty_del) AS accum_qty_del2
                    FROM delivery_orders
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND delivery_order_no != '{$post['delivery_order_no']}' GROUP BY sales_order_no, item_fg_id
                ";
                $result6 = $this->crud->query($query6);
                $accum_qty_del2 = $result6[0]->accum_qty_del2 ?? 0;

                // var_dump($shipping_qty);
                // var_dump($qty_so_date);
                // var_dump($total_qty_del_date);
                // var_dump($post['qty_so']);
                // var_dump($accum_qty_del2);
                // var_dump($post['qty_del']);
                // var_dump($delivery_orders->delivery_order_no);
                // die;
                
                if (@$delivery_orders->delivery_order_no != "") {

                    if (round($post['qty_so']) < round($accum_qty_del2 + $post['qty_del'])) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Qty del > Qty SO in total Please check your Accum Qty DO and Correct your Qty Del"
                        ]);
                        return;
                    }

                    $dataUpdate = array(
                        "remarks" => $post['remarks'],
                        "qty_remain" => $post['qty_remain'],
                        "qty_remain_date" => $post['qty_remain_date'],
                        "qty_del" => $post['qty_del'],
                        "stock_bal" => $post['stock_bal'],
                    );
                    // var_dump($dataUpdate);

                    if($post['qty_del'] == 0){
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no" => $post['sales_order_no']
                        ], ["remarks" => $post['remarks'],"qty_del" => $post['qty_del'],"qty_remain" => $post['qty_remain'],"stock_bal" => $post['stock_bal'],"status_scan" => 1]);   
                    } else{
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no" => $post['sales_order_no']
                        ], $dataUpdate);
                        //die(json_encode($send));
    
                        if($post['qty_so'] == ($post['qty_del']+$total_qty_del)){
                            $this->crud->update("sales_order_deliveries", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], ["status" => 1]);
                        }
                    }
    
                    if ($qty_so_date == ($post['qty_del'])) {
                        $this->crud->update("sales_order_deliveries", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 1]);
                    }

                    if ($delivery_orders->qty_del > ($post['qty_del'])) {
                        $this->crud->update("sales_order_deliveries", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 0]);
                    }

                    if($post['qty_del'] == round($shipping_qty)){
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no" => $post['sales_order_no']
                        ], ["remarks" => $post['remarks'],"qty_del" => $post['qty_del'],"qty_remain" => $post['qty_remain'],"stock_bal" => $post['stock_bal'],"status_scan" => 1]);   
                    }

                } else {
                    if(round($post['qty_so']) == round($post['qty_del']+$total_qty_del)){
                        $this->crud->update("sales_order_deliveries", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], ["status" => 1]);
                    }else{
                        $this->crud->update("sales_order_delivery_rm", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], ["status" => 1]);
                    }
    
                    if ($total_qty_del_date > 0) {
                        if ($qty_so_date < ($total_qty_del_date + $post['qty_del'])) {
                            echo json_encode([
                                "status" => "error",
                                "message" => "Qty del > Qty for this date"
                            ]);
                            return;
                        }
                    }

                    // var_dump($post['qty_so']);
                    // var_dump($accum_qty_del);
                    // var_dump($post['qty_del']);
                    // die;

                    if (round($post['qty_so']) < round($accum_qty_del + $post['qty_del'])) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Qty del > Qty SO in total Please check your Accum Qty DO and Correct your Qty Del"
                        ]);
                        return;
                    }
    
                    $dataFinal = array(
                        "sales_order" => $post['sales_order'],
                        "customer_id" => $post['customer_id'],
                        "delivery_order_date" => $post['delivery_order_date'],
                        "delivery_order_no" => $post['delivery_order_no'],
                        "delivery_date" => $post['delivery_date'],
                        "trans_type" => $post['trans_type'],
                        "remarks" => $post['remarks'],
                        "item_fg_id" => $post['item_fg_id'],
                        "customer_order_no" => $post['customer_order_no'],
                        "division" => $post['division'],
                        "sales_order_no" => $post['sales_order_no'],
                        "uom" => $post['uom'],
                        "qty_so" => $post['qty_so'],
                        "qty_sod" => $post['qty_sod'],
                        "qty_remain" => $post['qty_remain'],
                        "qty_remain_date" => $post['qty_remain_date'],
                        "qty_do" => $post['qty_do'],
                        "qty_del" => $post['qty_del'],
                        "qty_dn" => $post['qty_dn'],
                        "accum_qty_do" => $post['accum_qty_do'],
                        "stock" => $post['stock'],
                        "stock_bal" => $post['stock_bal'],
                        "njo_number" => $post['njo_number'],
    
                    );

                    if (isset($post['qty_del']) && $post['qty_del'] == 0) {
                        $dataFinal["status_scan"] = 1;  // Menambahkan status_scan ke $dataFinal
                    }

                    if (isset($post['division']) && $post['division'] == 'MTS') {
                        $dataFinal["status_scan"] = 1;  // Menambahkan status_scan ke $dataFinal
                    }
                    
                    // Proses penyimpanan data
                    $send = $this->crud->create('delivery_orders', $dataFinal);
    
                    if ($qty_so_date == ($post['qty_del']+$total_qty_del_date)) {
                        $this->db->update("sales_order_deliveries", ["status" => 1], ["item_fg_id" => $post['item_fg_id'],"sales_order_no" => $post['sales_order_no'],"trans_date" => $post['delivery_date']]);
                    }
                }
                echo $send;
            } else{
                $delivery_orders = $this->crud->read("delivery_orders", [], [
                    "delivery_order_no" => $post['delivery_order_no'],
                    "item_fg_id" => $post['item_fg_id'],
                    "sales_order_no_rm" => $post['sales_order_no']
                ]);
    
                $query = "SELECT SUM(qty_del) AS total_qty_del
                    FROM delivery_orders
                    WHERE delivery_order_no = '{$post['delivery_order_no']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no_rm = '{$post['sales_order_no']}'
                ";
                $result = $this->crud->query($query);
                $total_qty_del = $result[0]->total_qty_del ?? 0;
    
                $query2 = "SELECT qty 
                    FROM sales_order_delivery_rm
                    WHERE customer_id = '{$post['customer_id']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND trans_date = '{$post['delivery_date']}'
                ";
                $result2 = $this->crud->query($query2);
                $qty_so_date = $result2[0]->qty ?? 0;
    
                $query3 = "SELECT SUM(qty_del) AS total_qty_del_date
                    FROM delivery_orders
                    WHERE customer_order_no = '{$post['customer_order_no']}' AND item_fg_id = '{$post['item_fg_id']}' AND sales_order_no_rm = '{$post['sales_order_no']}' AND delivery_date = '{$post['delivery_date']}'
                ";
                $result3 = $this->crud->query($query3);
                $total_qty_del_date = $result3[0]->total_qty_del_date ?? 0;

                $query4 = "SELECT SUM(qty_del) AS accum_qty_del
                FROM delivery_orders
                WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no_rm = '{$post['sales_order_no']}'
                ";
                $result4 = $this->crud->query($query4);
                $accum_qty_del = $result4[0]->accum_qty_del ?? 0;

                $query5 = "SELECT SUM(qty) AS shipping_qty
                    FROM shipping_orders
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}' AND delivery_order_no = '{$post['delivery_order_no']}'
                ";
                $result5 = $this->crud->query($query5);
                $shipping_qty = $result5[0]->shipping_qty ?? 0;

                $query6 = "SELECT SUM(qty_del) AS accum_qty_del2
                    FROM delivery_orders
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no_rm = '{$post['sales_order_no']}' AND delivery_order_no != '{$post['delivery_order_no']}' GROUP BY sales_order_no, item_fg_id
                ";
                $result6 = $this->crud->query($query6);
                $accum_qty_del2 = $result6[0]->accum_qty_del2 ?? 0;
    
                // var_dump($qty_so_date);
                // var_dump($total_qty_del_date);
                // var_dump($post['qty_del']);
                // die;
                
                if (@$delivery_orders->delivery_order_no != "") {

                    if (round($post['qty_so']) < round($accum_qty_del2 + $post['qty_del'])) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Qty del > Qty SO in total Please check your Accum Qty DO and Correct your Qty Del"
                        ]);
                        return;
                    }
    
                    if ($qty_so_date == ($post['qty_del'])) {
                        $this->crud->update("sales_order_deliveries", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 1]);
                    }else{
                        $this->crud->update("sales_order_delivery_rm", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 1]);
                    }
    
                    if ($delivery_orders->qty_del > ($post['qty_del'])) {
                        $this->crud->update("sales_order_delivery_rm", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 0]);
                    }
    
                    if($post['qty_del'] == 0){
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no_rm" => $post['sales_order_no']
                        ], ["remarks" => $post['remarks'],"qty_del" => $post['qty_del'],"qty_remain" => $post['qty_remain'],"stock_bal" => $post['stock_bal'],"status_scan" => 1]);   
                    } else{
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no_rm" => $post['sales_order_no']
                        ], ["remarks" => $post['remarks'],"qty_del" => $post['qty_del'],"qty_remain_date" => $post['qty_remain_date'],"qty_remain" => $post['qty_remain'],"stock_bal" => $post['stock_bal']]);
    
                        if(round($post['qty_so']) == round($post['qty_del']+$total_qty_del)){
                            $this->crud->update("sales_order_delivery_rm", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], ["status" => 1]);
                        }
                    }

                    if($post['qty_del'] == round($shipping_qty)){
                        $send = $this->crud->update('delivery_orders', [
                            "delivery_order_no" => $post['delivery_order_no'], 
                            "item_fg_id" => $post['item_fg_id'], 
                            "sales_order_no_rm" => $post['sales_order_no']
                        ], ["remarks" => $post['remarks'],"qty_del" => $post['qty_del'],"qty_remain" => $post['qty_remain'],"stock_bal" => $post['stock_bal'],"status_scan" => 1]);   
                    }

                    
                } else {
                    if(round($post['qty_so']) == round($post['qty_del']+$total_qty_del)){
                        $this->crud->update("sales_order_delivery_rm", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], ["status" => 1]);
                    }
    
                    if ($total_qty_del_date > 0) {
                        if ($qty_so_date < ($total_qty_del_date + $post['qty_del'])) {
                            echo json_encode([
                                "status" => "error",
                                "message" => "Qty del > Qty for this date"
                            ]);
                            return;
                        }
                    }

                    if (round($post['qty_so']) < round($accum_qty_del + $post['qty_del'])) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Qty del > Qty SO in total Please check your Accum Qty DO and Correct your Qty Del"
                        ]);
                        return;
                    }
    
                    $dataFinal = array(
                        "sales_order" => $post['sales_order'],
                        "customer_id" => $post['customer_id'],
                        "delivery_order_date" => $post['delivery_order_date'],
                        "delivery_order_no" => $post['delivery_order_no'],
                        "delivery_date" => $post['delivery_date'],
                        "trans_type" => $post['trans_type'],
                        "remarks" => $post['remarks'],
                        "item_fg_id" => $post['item_fg_id'],
                        "customer_order_no" => $post['customer_order_no'],
                        "division" => $post['division'],
                        "sales_order_no_rm" => $post['sales_order_no'],
                        "uom" => $post['uom'],
                        "qty_so" => $post['qty_so'],
                        "qty_sod" => $post['qty_sod'],
                        "qty_remain" => $post['qty_remain'],
                        "qty_remain_date" => $post['qty_remain_date'],
                        "qty_do" => $post['qty_do'],
                        "qty_del" => $post['qty_del'],
                        "qty_dn" => $post['qty_dn'],
                        "accum_qty_do" => $post['accum_qty_do'],
                        "stock" => $post['stock'],
                        "stock_bal" => $post['stock_bal'],
                        "njo_number" => $post['njo_number'],
    
                    );

                    if (isset($post['qty_del']) && $post['qty_del'] == 0) {
                        $dataFinal["status_scan"] = 1;  // Menambahkan status_scan ke $dataFinal
                    }

                    if (isset($post['division']) && $post['division'] == 'MTS') {
                        $dataFinal["status_scan"] = 1;  // Menambahkan status_scan ke $dataFinal
                    }
                    
                    // Proses penyimpanan data
                    $send = $this->crud->create('delivery_orders', $dataFinal);

                    if (round($qty_so_date) == round($post['qty_del']+$total_qty_del_date)) {
                        $this->crud->update("sales_order_delivery_rm", [
                            "item_fg_id" => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date" => $post['delivery_date']
                        ], ["status" => 1]);
                    }
                }
                echo $send;
            }

            
        } else {
            show_error("Cannot process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delivery_order_no = $data['delivery_order_no'];

        $delivery_orders = $this->crud->reads("delivery_orders", [], ["delivery_order_no" => $delivery_order_no]);

        foreach ($delivery_orders as $delivery_order) {
            $item_fg_id = $delivery_order->item_fg_id;
            $sales_order_no = $delivery_order->sales_order_no;
            $sales_order_no_rm = $delivery_order->sales_order_no_rm;
            $delivery_date = $delivery_order->delivery_date;
            $sales_order = $delivery_order->sales_order;

            // var_dump($delivery_orders);

            if($sales_order == 'FG'){
                $update = $this->crud->update('sales_order_deliveries', ["item_fg_id" => $item_fg_id, "sales_order_no" => $sales_order_no, "trans_date" => $delivery_date], ["status" => 0]);
            }else{
                $update = $this->crud->update('sales_order_delivery_rm', ["item_fg_id" => $item_fg_id, "sales_order_no" => $sales_order_no_rm, "trans_date" => $delivery_date], ["status" => 0]);
            }
        }
        $send = $this->crud->delete('delivery_orders', $data);
        echo $send;
    }

    public function print_do($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);

        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 25;
        $page = ceil(count($delivery_orders) / $rows);
        //Generate QRcode
        $this->createQrcode($delivery_order_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_order->delivery_order_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 25 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(25, ($i * 25));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="40" /></th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->description . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
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
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>DELIVERY ORDER</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Delivery Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$delivery_order->delivery_order_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Order Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_order_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Customer Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%; text-align:right;">
                                    <img style="margin-right:10px;" src="' . base_url('assets/image/qrcode/' . $delivery_order->delivery_order_no . '.png') . '" width="80"/><br>
                                    <small style="font-size:10px; margin-right:16px;">' . $delivery_order->delivery_order_no . '</small><br><br>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product ID</th>
                                        <th>HS Code (INSW)</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th width="60">Qty</th>
                                        <th width="120">Sales Order No</th>
                                        <th width="120">Customer Order No</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_fg_id'] . '</td>
                                <td>' . $record['hs_code'] . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty_del'], 2, ",", ".") . '</td>
                                <td>' . $record['sales_order_no'] . '</td>
                                <td>' . $record['customer_order_no'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                        <th width="200" style="text-align:center;">DELIVER CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);

    }



    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status = @base64_decode($get['filter_status']);
        $filter_division = @base64_decode($get['filter_division']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name");
        $this->db->select('c.hs_code');
        $this->db->from('delivery_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_order_date >=', $filter_from);
            $this->db->where('a.delivery_order_date <=', $filter_to);
        }

        if (!empty($filter_sales_order_no)) {
            $this->db->where("(a.sales_order_no LIKE '%{$filter_sales_order_no}%' OR a.sales_order_no_rm LIKE '%{$filter_sales_order_no}%')", NULL, FALSE);
        }

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }

        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
        // $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('a.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.delivery_order_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>

        <center>

            <div style="float: left; font-size: 12px; text-align: left;">

                <table style="width: 100%;">

                    <tr>

                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                            <img src="' . $config->favicon . '" width="30">

                        </td>

                        <td style="font-size: 14px; text-align: left; margin:2px;">

                            <b>' . $config->name . '</b><br>

                            <small>' . $config->description . '</small>

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
                <h3>DELIVERY ORDER</h3>
            </div>
        </center>

        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Delivery Order No</th>
                <th>Delivery Order Date</th>
                <th>Delivery Date</th>
                <th>Trans Type</th>
                <th>Sales Order No</th>
                <th>Customer Order No</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>HS Code (INSW)</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty SO</th>
                <th>Qty Remain</th>
                <th>Qty DO</th>
                <th>Qty Delivery</th>
                <th>Stock</th>
                <th>Stock Balance</th>
                <th>Status</th>
            </tr>';

        $no = 1;

        foreach ($records as $data) {

            if($data['status'] == '0'){
                $status = "OPEN";
            }else{
                $status = "CLOSE";
            }

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['delivery_order_no'] . '</td>
                        <td>' . $data['delivery_order_date'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['trans_type'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['hs_code'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_so'] . '</td>
                        <td>' . $data['qty_remain'] . '</td>
                        <td>' . $data['qty_do'] . '</td>
                        <td>' . $data['qty_del'] . '</td>
                        <td>' . $data['stock'] . '</td>
                        <td>' . $data['stock_bal'] . '</td>
                        <td>' . $status . '</td>
                    </tr>';

            $no++;

        }

        $html .= '</table></body></html>';

        echo $html;

    }

}

