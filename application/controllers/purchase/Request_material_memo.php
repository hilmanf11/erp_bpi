<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Request_material_memo extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/request_material_memo');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, e.name as supplier_name, d.mpq, d.moq, d.price');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_number = c.number');
        $this->db->join('supplier_items d', 'a.item_rm_id = d.item_rm_id');
        $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.request_no', $request_no);
        $this->db->like('d.supplier_id', $supplier_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readPoNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readLotNo()
    {
        $records = $this->crud->query("SELECT lotno FROM purchase_order_receipts WHERE `status` = '0' GROUP BY lotno ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceipt($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceipts()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE `status` = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readDocno($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readDocnos()
    {
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE `status` = '0' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE deleted = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readMemoNo()
    {
        $records = $this->crud->query("SELECT a.memo_no FROM request_materials a WHERE a.status = 'UNDER' GROUP BY a.memo_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function readPoNos()
    {
        $records = $this->crud->query("SELECT po_no FROM request_materials WHERE `status` = 'UNDER' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function checkMemo()
    {
        $memo_no = $this->input->post('memo_no');

        $this->db->select('COUNT(*) as total');
        $this->db->from('request_materials');
        $this->db->where('memo_no', $memo_no);
        $this->db->where("(approved_to IS NOT NULL AND approved_to != '')", null, false);

        $record = $this->db->get()->row_array();

        if ($record['total'] > 0) {
            echo json_encode("NO");
        } else {
            echo json_encode("YES");
        }
    }

    public function receipt_no($date = "", $type = "")
    {
        if ($date == "") {
            $datenow = date("ym");
        } else {
            $datenow = date("ym", strtotime(base64_decode($date)));
        }

        $prefix = "RMM"; // default
        if ($type == "P") {
            $prefix .= "P";
        } elseif ($type == "D") {
            $prefix .= "D";
        } else {
            $prefix .= "X";
        }

        $sqlGetID = $this->db->query("SELECT MAX(memo_no) AS kode FROM request_materials WHERE memo_no LIKE '{$prefix}-{$datenow}%'");

        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;

        if ($kode == NULL) {
            $autoID = "0001";
        } else {
            $urutan = (int) substr($kode, -4); // Ambil 4 digit terakhir
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo $prefix . "-" . $datenow . "-" . $autoID;
    }

    public function readItems()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_cutoff = $this->input->get('filter_cutoff');

        $month = date('m', strtotime($filter_to));
        $year = date('Y', strtotime($filter_to));

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $query_main = "
        SELECT * FROM (    
            SELECT 
                a.id,
                a.number, 
                a.name, 
                a.division, 
                b.name as prodfam, 
                a.uom,
                c.name as category_name,
                COALESCE(o.supplier_name,'-') as supplier_name,
                COALESCE(l.need_1, 0) as plan_supply,
                COALESCE(n.min, 0) as min,
                COALESCE(n.max, 0) as max,
                COALESCE(n.leadtime, 0) as leadtime,
                COALESCE(o.qty_os, 0) as qty_os,
                COALESCE(h.qty_issued, 0) as qty_issued,
                COALESCE(j.begin_stock) AS begin_stock,
                COALESCE(s.total_qty, 0) as max_daily_supply,
                (COALESCE(s.total_qty, 0) *  COALESCE(n.min, 0)) as min_stock, 
                (COALESCE(s.total_qty, 0) *  COALESCE(n.max, 0)) as max_stock, 
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out,
                (COALESCE(j.begin_stock) + COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) as stock_current,
                CASE 
                    WHEN ((COALESCE(j.begin_stock, 0) + 
                        COALESCE(d.qty_scan_in, 0) + 
                        COALESCE(e.qty_os_rm, 0) + 
                        COALESCE(f.qty_trans_rm_in, 0) + 
                        COALESCE(g.return_qty, 0) + 
                        COALESCE(k.qty_scan_bpm, 0)) 
                        - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))
                        ) > (COALESCE(s.total_qty, 0) * COALESCE(n.max, 0))
                        THEN 'OVER'

                    WHEN ((COALESCE(j.begin_stock, 0) + 
                        COALESCE(d.qty_scan_in, 0) + 
                        COALESCE(e.qty_os_rm, 0) + 
                        COALESCE(f.qty_trans_rm_in, 0) + 
                        COALESCE(g.return_qty, 0) + 
                        COALESCE(k.qty_scan_bpm, 0)) 
                        - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))
                        ) >= (COALESCE(s.total_qty, 0) * COALESCE(n.min, 0))
                        THEN 'OK'

                    ELSE 'UNDER'
                END AS `status`
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
            LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                            FROM item_rm a
                            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                        ) j ON a.id = j.id
            LEFT JOIN (SELECT t.item_rm_id, t.need_1
                            FROM generate_mrp_finals t
                            JOIN (
                                SELECT item_rm_id, MAX(revision) AS max_revisi
                                FROM generate_mrp_finals
                                WHERE p_month = '$month' AND p_year = '$year'
                                GROUP BY item_rm_id
                            ) x ON t.item_rm_id = x.item_rm_id AND t.revision = x.max_revisi
                            WHERE t.p_month = '$month' AND t.p_year = '$year') l ON a.id = l.item_rm_id
            LEFT JOIN (SELECT DISTINCT c.name as supplier_name,a.item_rm_id,a.share_order 
                            FROM supplier_items a
                            LEFT JOIN item_rm b ON a.item_rm_id = b.id
                            LEFT JOIN suppliers c ON a.supplier_id = c.id
                            WHERE a.share_order = 100 AND b.item_family_id IN('P01','P02','P06')
                        ) m ON a.id = m.item_rm_id
            LEFT JOIN (SELECT item_rm_id, min, max, leadtime FROM master_minmax WHERE status = 0 group by item_rm_id) n ON a.id = n.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, c.name as supplier_name, SUM(COALESCE(a.qty, 0)) - SUM(COALESCE(b.qty_receipt, 0)) AS qty_os
                            FROM (SELECT item_rm_id, po_no, SUM(qty) AS qty, supplier_id FROM purchase_orders WHERE STATUS = 0 AND po_date <= '$filter_cutoff' GROUP BY item_rm_id, po_no) a
                            LEFT JOIN (
                                    SELECT item_rm_id, po_no, SUM(qty_receipt) AS qty_receipt 
                                    FROM purchase_order_receipts 
                                    WHERE receipt_date <= '$filter_cutoff' 
                                    GROUP BY item_rm_id, po_no) b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                            JOIN suppliers c ON a.supplier_id = c.id
                            GROUP BY a.item_rm_id
                        ) o ON a.id = o.item_rm_id
            LEFT JOIN (SELECT item_rm_id, MAX(total_qty) as total_qty
                            FROM (
                                SELECT 
                                    a.item_rm_id,
                                    b.trans_date, 
                                    SUM(b.qty * a.composition) AS total_qty
                                FROM bom a 
                                JOIN production_schedules b ON a.item_fg_id = b.item_fg_id 
                                WHERE 
                                    DATE_FORMAT(b.trans_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1'
                                GROUP BY a.item_rm_id, b.trans_date
                            ) AS sub
                            GROUP BY item_rm_id
                        ) s ON a.id = s.item_rm_id

            WHERE a.item_family_id IN('P01','P02','P06')
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number
        ) as query_main
        WHERE status = 'UNDER'";

        if ($post != "") {
            $query_main .= " AND number LIKE '%" . $this->db->escape_like_str($post) . "%' ";
        }

        $query_main .= " ORDER BY supplier_name ASC";
    
        // Eksekusi query
        $records = $this->crud->query($query_main);
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from2 = $this->input->get('filter_from2');
            $filter_to2   = $this->input->get('filter_to2');;
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_memo_no = $this->input->get('filter_memo_no');
            $filter_part_no = $this->input->get('filter_part_no');
          
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $id = $_POST['id'];
            if ($id === "0") {
                //Select Query
                $this->db->select('a.*,
                b.uom,
                d.name as supplier_name,
                k.total_approved_to_checking,
                l.total_approved_to_approved,
                COUNT(a.approved_to) as total_approved_to');
                $this->db->from('request_materials a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('(SELECT memo_no, COUNT(approved_to) as total_approved_to_checking FROM request_materials WHERE approved_to != "" || approved_to = NULL GROUP BY memo_no) k', 'a.memo_no = k.memo_no', 'left');
                $this->db->join('(SELECT memo_no, COUNT(approved_to) as total_approved_to_approved FROM request_materials WHERE approved_to = "" || approved_to = NULL GROUP BY memo_no) l', 'a.memo_no = l.memo_no', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from2 != "" or $filter_to2 != "") {
                    $this->db->where('a.memo_date >=', $filter_from2);
                    $this->db->where('a.memo_date <=', $filter_to2);
                }
                $this->db->like('a.memo_no', $filter_memo_no);
                $this->db->like('a.po_no', $filter_po_no);
                $this->db->like('a.item_rm_id', $filter_part_no);
        
                $this->db->group_by('a.memo_no');
                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('a.po_no', 'DESC');
                $this->db->order_by('a.status', 'ASC');
                
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {

                    if ($record['total_approved_to'] == $record['total_approved_to_checking']) {
                        $approved_to = "Checking";
                    } elseif ($record['total_approved_to'] == $record['total_approved_to_approved']) {
                        $approved_to = "";
                    } elseif ($record['total_approved_to_checking'] >= 1) {
                        $approved_to = "Checking";
                    } elseif ($record['total_approved_to_approved'] >= 1) {
                        $approved_to = "";
                    } else {
                        $approved_to = "";
                    }

                    $arr[] = array(
                        "id" => $record['memo_no'],
                        "ids" => $record['id'],
                        "memo_no" => $record['memo_no'],
                        "memo_date" => $record['memo_date'],
                        "uom" => $record['uom'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "status" => "",
                        "state" => "closed",
                        "approved_to" => $approved_to,
                        "total_checking" => $record['total_approved_to_checking'],
                        "total_approved" => $record['total_approved_to_approved'],
                        "type" => $record['type'],
                        "cutoff" => $record['cutoff'],
                        "datatable" => 1
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                b.name as item_name, 
                b.number as item_number, 
                d.name as supplier_name,
                b.uom');
                $this->db->from('request_materials a');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                if ($filter_from2 != "" or $filter_to2 != "") {
                    $this->db->where('a.memo_date >=', $filter_from2);
                    $this->db->where('a.memo_date <=', $filter_to2);
                }
                $this->db->like('a.memo_no', $id);
                // $this->db->like('d.id', $filter_suppliers);
                // $this->db->like('b.item_category_id', $filter_categories);
                // $this->db->like('a.status', $filter_status);
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.memo_no', 'DESC');
                $records = $this->db->get()->result_array();

                echo json_encode($records);
            }
        }
    }

    public function datatablesTemp()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_cutoff = $this->input->get('filter_cutoff');
        $item_rm_id = explode(",", $this->input->get('item_rm_id'));
        // var_dump($item_rm_id);
        // return;

        $month = date('m', strtotime($filter_to));
        $year = date('Y', strtotime($filter_to));

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));

        $filter_item = '';
        if (!empty($item_rm_id)) {
            $escaped_ids = implode(',', array_map(function ($id) {
                return "'" . addslashes(trim($id)) . "'";
            }, $item_rm_id));
            $filter_item = " AND a.id IN ($escaped_ids)";
        }

        $query_main = "
        SELECT * FROM (    
            SELECT 
                a.id as item_rm_id,
                a.number, 
                a.name, 
                a.division, 
                b.name as prodfam, 
                a.uom,
                c.name as category_name,
                COALESCE(o.supplier_name,'-') as supplier_name,
                COALESCE(o.supplier_id,'-') as supplier_id,
                COALESCE(m.maker,'-') as maker,
                COALESCE(l.need_1, 0) as plan_supply,
                COALESCE(n.min, 0) as min,
                COALESCE(n.max, 0) as max,
                COALESCE(n.leadtime, 0) as leadtime,
                COALESCE(o.qty_os, 0) as qty_os,
                COALESCE(o.po_no, 0) as po_no,
                COALESCE(h.qty_issued, 0) as qty_issued,
                COALESCE(j.begin_stock) AS begin_stock,
                ROUND(COALESCE(s.total_qty, 0), 2) AS avg_qty_issued_per_day,
                ROUND(COALESCE(s.total_qty, 0) * COALESCE(n.min, 0), 2) AS min_stock,
                ROUND(COALESCE(s.total_qty, 0) * COALESCE(n.max, 0), 2) AS max_stock,
                ROUND((COALESCE(j.begin_stock, 0) + 
                    COALESCE(d.qty_scan_in, 0) + 
                    COALESCE(e.qty_os_rm, 0) + 
                    COALESCE(f.qty_trans_rm_in, 0) + 
                    COALESCE(g.return_qty, 0) + 
                    COALESCE(k.qty_scan_bpm, 0)) 
                    - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)), 2
                ) AS stock_current,
                ROUND((COALESCE(s.total_qty, 0) * COALESCE(n.max, 0)) -
                    ((COALESCE(j.begin_stock, 0) + 
                    COALESCE(d.qty_scan_in, 0) + 
                    COALESCE(e.qty_os_rm, 0) + 
                    COALESCE(f.qty_trans_rm_in, 0) + 
                    COALESCE(g.return_qty, 0) + 
                    COALESCE(k.qty_scan_bpm, 0)) 
                    - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))), 2
                ) AS qty,
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out,
                CASE 
                    WHEN ((COALESCE(j.begin_stock, 0) + 
                        COALESCE(d.qty_scan_in, 0) + 
                        COALESCE(e.qty_os_rm, 0) + 
                        COALESCE(f.qty_trans_rm_in, 0) + 
                        COALESCE(g.return_qty, 0) + 
                        COALESCE(k.qty_scan_bpm, 0)) 
                        - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))
                        ) > (COALESCE(s.total_qty, 0) * COALESCE(n.max, 0))
                        THEN 'OVER'

                    WHEN ((COALESCE(j.begin_stock, 0) + 
                        COALESCE(d.qty_scan_in, 0) + 
                        COALESCE(e.qty_os_rm, 0) + 
                        COALESCE(f.qty_trans_rm_in, 0) + 
                        COALESCE(g.return_qty, 0) + 
                        COALESCE(k.qty_scan_bpm, 0)) 
                        - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))
                        ) >= (COALESCE(s.total_qty, 0) * COALESCE(n.min, 0))
                        THEN 'OK'

                    ELSE 'UNDER'
                END AS `status`,
                DATE_ADD(CURRENT_DATE, INTERVAL COALESCE(n.leadtime, 0) DAY) AS request_date
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
            LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                            FROM item_rm a
                            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                        ) j ON a.id = j.id
            LEFT JOIN (SELECT t.item_rm_id, t.need_1
                            FROM generate_mrp_finals t
                            JOIN (
                                SELECT item_rm_id, MAX(revision) AS max_revisi
                                FROM generate_mrp_finals
                                WHERE p_month = '$month' AND p_year = '$year'
                                GROUP BY item_rm_id
                            ) x ON t.item_rm_id = x.item_rm_id AND t.revision = x.max_revisi
                            WHERE t.p_month = '$month' AND t.p_year = '$year') l ON a.id = l.item_rm_id
            LEFT JOIN (SELECT DISTINCT c.name as supplier_name,a.item_rm_id,a.share_order, c.id as supplier_id, a.maker
                            FROM supplier_items a
                            LEFT JOIN item_rm b ON a.item_rm_id = b.id
                            LEFT JOIN suppliers c ON a.supplier_id = c.id
                            WHERE a.share_order = 100 AND b.item_family_id IN('P01','P02','P06')
                        ) m ON a.id = m.item_rm_id
            LEFT JOIN (SELECT item_rm_id, min, max, leadtime FROM master_minmax WHERE status = 0 group by item_rm_id) n ON a.id = n.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, a.po_no, c.name as supplier_name, c.id as supplier_id, SUM(COALESCE(a.qty, 0)) - SUM(COALESCE(b.qty_receipt, 0)) AS qty_os 
                            FROM ( SELECT item_rm_id, po_no, SUM(qty) AS qty, supplier_id FROM purchase_orders WHERE STATUS = 0 AND po_date <= '$filter_cutoff' GROUP BY item_rm_id, po_no ) a 
                            LEFT JOIN ( 
                                    SELECT item_rm_id, po_no, SUM(qty_receipt) AS qty_receipt 
                                    FROM purchase_order_receipts 
                                    WHERE receipt_date <= '$filter_cutoff' 
                                    GROUP BY item_rm_id, po_no ) b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                            JOIN suppliers c ON a.supplier_id = c.id         
                            GROUP BY a.item_rm_id, a.po_no
                        ) o ON a.id = o.item_rm_id
            LEFT JOIN (SELECT item_rm_id, MAX(total_qty) as total_qty
                            FROM (
                                SELECT 
                                    a.item_rm_id,
                                    b.trans_date, 
                                    SUM(b.qty * a.composition) AS total_qty
                                FROM bom a 
                                JOIN production_schedules b ON a.item_fg_id = b.item_fg_id 
                                WHERE 
                                    DATE_FORMAT(b.trans_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1'
                                GROUP BY a.item_rm_id, b.trans_date
                            ) AS sub
                            GROUP BY item_rm_id
                        ) s ON a.id = s.item_rm_id


            WHERE a.item_family_id IN('P01','P02','P06')
            $filter_item
            GROUP BY a.id, o.po_no
            ORDER BY c.name DESC, b.name DESC, a.number
        ) as query_main
        WHERE status = 'UNDER'
        ORDER BY supplier_name ASC";
    
        // Eksekusi query
        $records = $this->crud->query($query_main);
        echo json_encode($records);
    }

    public function datatable_updates()
    {
        $memo_no = base64_decode($this->input->get('memo_no'));
        $this->db->select('a.id,
                a.item_rm_id,
                a.maker,
                a.objective,
                a.os_po as qty_os,
                a.min as min_stock,
                a.max as max_stock,
                a.act_stock as stock_current,
                a.po_no,
                a.request_date,
                a.status,
                a.qty,
                b.name,
                b.number,
                d.id as supplier_id,
                d.name as supplier_name,
                b.uom');
                $this->db->from('request_materials a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.memo_no', $memo_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             $send   = $this->crud->create('request_materials', $post);
    //             echo $send;
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $id = isset($post['id']) ? $post['id'] : ''; 
            
            $exists = false;
            if (!empty($id)) {
                $exists = $this->db->get_where('request_materials', ['id' => $id])->num_rows() > 0;
            }

            if ($exists) {
                $send = $this->crud->update('request_materials', ["id" => $id], $post);
            } else {
                if (isset($post['id'])) {
                    unset($post['id']); 
                }
                $send = $this->crud->create('request_materials', $post); 
            }

            echo $send;
        }
    }

    //UPDATE DATA
    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();
    //         var_dump($post);
    //         return;
    //         $send = $this->crud->update('request_materials', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $deleteRMM = $this->crud->delete('request_materials', ["id" => $data['id']]);
        echo $deleteRMM;
    }

    public function print_memo($memo_no)
    {
        $request_material_memo_total = $this->crud->reads('request_materials', [], ["memo_no" => base64_decode($memo_no)]);
        $request_material_memos = $this->crud->read('request_materials', [], ["memo_no" => base64_decode($memo_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $request_material_memos->supplier_id]);
        $makers = $this->crud->read('makers', [], ["name" => $request_material_memos->maker]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        $form_memo_pickup = !empty($config_iso->form_memo_pickup) ? $config_iso->form_memo_pickup : '-';
        $form_memo_delivery = !empty($config_iso->form_memo_delivery) ? $config_iso->form_memo_delivery : '-';

        $approval = $this->crud->read('approvals', [], ["table_name" => "request_materials"]);
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);

        if (!empty($approval->user_approval_2)) {
            $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        } else {
            $user_2 = (object) ["name" => ""];
        }
        
        if (!empty($approval->user_approval_3)) {
            $user_3 = $this->crud->read('users', [], ["username" => $approval->user_approval_3]);
        } else {
            $user_3 = (object) ["name" => ""];
        }
        
        
        if($request_material_memos->approved == 0){
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($request_material_memos->approved == 1) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($request_material_memos->approved == 2) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($request_material_memos->approved == 3) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '';
        } else {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '<img src="' . base_url('assets/image/qrcode/' . $user_3->name . '.png') . '" width="80"/>';
        }
        
        
        //Config Page
        $rows = 8;
        $page = ceil(count($request_material_memo_total) / $rows);
        //Generate QRcode
        $this->createQrcode($request_material_memos->memo_no, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $request_material_memos->memo_no . '</title>
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
                            </center>
                        </div>
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $judul = "MEMO PICKUP MATERIAL"; 
        $form = $form_memo_pickup;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.id as item_id, b.number as item_number, b.name as item_name, b.uom, c.name as supplier_name');
            $this->db->from('request_materials a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.memo_no', base64_decode($memo_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();

            if ($request_material_memos->updated_date != null) {
                $revision_date = $request_material_memos->updated_date;
            } else {
                $revision_date = $request_material_memos->created_date;
            }

            foreach ($records as $row) {
                if ($row['type'] == 'D') {
                    $judul = "MEMO DELIVERY MATERIAL";
                    $form = $form_memo_delivery;
                }

                if ($row['maker'] !== '' && $row['objective'] == 'maker' ) {
                    $to = @$makers->name;
                    $attention = @$makers->contact_person;
                }else if ($row['objective'] == 'supplier' ){
                    $to = @$supplier->name;
                    $attention = @$supplier->contact_person;
                }else{
                    $to = @$supplier->name;
                    $attention = @$supplier->contact_person;
                }
            }

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $form . '</td>
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
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>'.$judul.'</u></h3>
                                    <small>NO : ' . @$request_material_memos->memo_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">To</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$to . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            Memo Date:<br><b>' . date("d F Y", strtotime($request_material_memos->memo_date)) . '</b><br>
                                            Revision:<br><b>' . $request_material_memos->revision . '</b><br>
                                            Revision Date:<br><b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$attention . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">CC</td>
                                        <td width="10">:</td>
                                        <td><b></b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" width="30" style="text-align:center;">No</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Material No</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Material Name</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Pick Up <br> Qty</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Unit</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Po No</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Os Po</th>
                                            <th rowspan="2" width="100" style="text-align:center;">O/S PO <br>After Pick Up</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Unit</th>
                                        </tr>
                                    </thead>';
            $row = 0;
            foreach ($records as $record) {
                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="text-align:center;">' . $record['item_number'] . '</td>
                                <td style="text-align:center;">' . $record['item_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty'], 2) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:center;">' . $record['po_no'] . '</td>
                                <td style="text-align:center;">' . number_format($record['os_po'],2) . '</td>
                                <td style="text-align:right;">' . number_format($record['os_po'] - $record['qty'], 2) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {

                $this->db->select('a.remarks, b.number as item_number, b.name as item_name');
                $this->db->from('request_materials a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.memo_no', base64_decode($memo_no));
                $this->db->order_by('b.number', 'asc');
                $remarks = $this->db->get()->result_array();

                $note_content = []; // Menampung remarks yang valid

                foreach ($remarks as $remark) {
                    if (!empty($remark['remarks'])) {
                        $note_content[] = $remark['item_number'] . " &nbsp; (" . $remark['remarks'] . ")";
                    }
                }

                $html .= '  <tr>
                            <td style="vertical-align: top; text-align:left; height:80px;" colspan="9" rowspan="8">
                                <b>Note :</b> <br>' . implode('<br>', $note_content) . '
                            </td>
                        </tr>';
                        
            } else {
                $html .= '</table>';
            }
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            } else{
                // Memindahkan informasi approval ke sini
                $html .= '<div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                <div style="width:40%; position: absolute; right: 50px;">
                    <table id="customers" style="margin-top:20px;">
                        <tr>
                            <th width="200" style="text-align:center;">Received</th>
                            <th width="200" style="text-align:center;">Approved By</th>
                            <th width="200" style="text-align:center;">Prepared By</th>
                        </tr>
                        <tr>
                            <th style="height:100px;"></th>
                            <th style="height:100px;">'. $users_1. '</th>
                            <th style="height:100px;">'. $users_0. '</th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $user_1->name . '</th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table>
                        <div style="text-align:left; font-size: 15px; margin-top: 20px; border: none;">
                            <i>Electronic Auto Generating Approval No Need Signature</i>
                        </div>
                </div>
            </div>

            </div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }

    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=purchase_order_receipts_$format.xls");
    //     }
    //     $filter_from2 = $this->input->get('filter_from2');
    //     $filter_to2   = $this->input->get('filter_to2');;
    //     $filter_po_no = $this->input->get('filter_po_no');
    //     $filter_memo_no = $this->input->get('filter_memo_no');
    //     $filter_part_no = $this->input->get('filter_part_no');
    
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $this->db->select('
    //         a.*, 
    //         b.number as supplier_id, 
    //         b.name as supplier_name, 
    //         c.number as item_rm_id, 
    //         c.name as item_name, 
    //         d.name as item_family_name, 
    //         b.currency, 
    //         c.uom, 
    //         f.number as category_code,
    //         SUM(pol.status) as total_scan
    //     ');
    //     $this->db->from('purchase_order_receipts a');
    //     $this->db->join('suppliers b', 'a.supplier_id = b.id');
    //     $this->db->join('item_rm c', 'a.item_rm_id = c.id');
    //     $this->db->join('item_familys d', 'c.item_family_id = d.id');
    //     $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
    //     $this->db->join('item_categories f', 'c.item_category_id = f.id','left');
    //     $this->db->join('purchase_orders g', 'a.po_no = g.po_no and a.item_rm_id = g.item_rm_id','left');
    //     $this->db->join('purchase_order_labels pol', 'a.receipt_id = pol.receipt_id', 'left'); // Join tabel purchase_order_labels
    //     $this->db->where('a.deleted', 0);

    //     if ($filter_from != "" and $filter_to != "") {
    //         $this->db->where('a.receipt_date >=', $filter_from);
    //         $this->db->where('a.receipt_date <=', $filter_to);
    //     }
    //     if ($filter_supplier != "") {
    //         $this->db->where('a.supplier_id', $filter_supplier);
    //     }
    //     if ($filter_part_no != "") {
    //         $this->db->where('a.item_rm_id', $filter_part_no);
    //     }
    //     if ($filter_receipt != "") {
    //         $this->db->where('a.receipt_no', $filter_receipt);
    //     }
    //     if ($filter_doc_no != "") {
    //         $this->db->where('a.bc_document', $filter_doc_no);
    //     }
    //     if ($filter_categories != "") {
    //         $this->db->where('c.item_category_id', $filter_categories);
    //     }
    //     $this->db->like('a.po_no', $filter_po_no);

    //     $this->db->group_by('a.receipt_id'); // Kelompokkan berdasarkan receipt_id untuk menghitung SUM
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $this->db->order_by('a.receipt_date', 'DESC');

    //     $records = $this->db->get()->result_array();

    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                             <img src="' . $config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . $config->name . '</b><br>
    //                             <small>PURCHASE ORDER RECEIPT</small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:i:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //         </center>
    //         <br><br><br>
            
    //         <table id="customers" border="1">
    //         <tr>
    //             <th rowspan="2" width="20">No</th>
    //             <th rowspan="2">Receipt No</th>
    //             <th rowspan="2">Category</th>
    //             <th rowspan="2">Status POR</th>
    //             <th rowspan="2">Status Invoice</th>
    //             <th rowspan="2">Status Print GRN</th>
    //             <th rowspan="2">PO No</th>
    //             <th rowspan="2">Document</th>
    //             <th rowspan="2">Document Date</th>
    //             <th colspan="2" style="text-align:center;">Supplier</th>
    //             <th rowspan="2">Product No</th>
    //             <th rowspan="2">Product Name</th>
    //             <th rowspan="2">Qty</th>
    //             <th rowspan="2">UoM</th>
    //             <th rowspan="2">Currency</th>
    //             <th rowspan="2">Label</th>
    //         </tr>
    //         <tr>
    //             <th>ID</th>
    //             <th>Name</th>
    //         </tr>';
    //     $no = 1;
    //     foreach ($records as $data) {

    //         if ($data['total_scan'] == $data['qty_label']) {
    //             $status_por = 'CLOSED';
    //         } else {
    //             $status_por = 'OPEN';
    //         }
            
    //         if ($data['status'] == 1) {
    //             $status = 'CLOSED';
    //         } else {
    //             $status = 'OPEN';
    //         }

    //         if ($data['print'] == 1) {
    //             $print = 'CLOSED';
    //         } else {
    //             $print = 'OPEN';
    //         }

    //         $html .= '<tr>
    //                     <td style="text-align:center">' . $no . '</td>
    //                     <td>' . $data['receipt_no'] . '</td>
    //                     <td>' . $data['category_code'] . '</td>
    //                     <td>' . $status_por . '</td>
    //                     <td>' . $status . '</td>
    //                     <td>' . $print . '</td>
    //                     <td>' . $data['po_no'] . '</td>
    //                     <td>' . $data['bc_document'] . '</td>
    //                     <td>' . $data['bc_date'] . '</td>
    //                     <td>' . $data['supplier_id'] . '</td>
    //                     <td>' . $data['supplier_name'] . '</td>
    //                     <td>' . $data['item_rm_id'] . '</td>
    //                     <td>' . $data['item_name'] . '</td>
    //                     <td>' . number_format($data['qty_receipt'], 2) . '</td>
    //                     <td>' . $data['uom'] . '</td>
    //                     <td>' . $data['currency'] . '</td>
    //                     <td>' . number_format($data['qty_label']) . '</td>
    //                 </tr>';
    //         $no++;
    //     }
    //     $html .= '</table></body></html>';
    //     echo $html;
    // }
}
