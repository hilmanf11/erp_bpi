<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Transaction_rm extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/transaction_rm');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriod()
    {
        $records = $this->crud->query("SELECT `period` FROM transaction_rm WHERE `status` = '0' GROUP BY `period`");
        echo json_encode($records);
    }

    public function readWp($period)
    {
        $records = $this->crud->query("SELECT workorder FROM transaction_rm WHERE `status` = '0' and `period` = '$period' GROUP BY workorder");
        echo json_encode($records);
    }

    public function readRequestNo($period, $workorder)
    {
        $workorder = base64_decode($workorder);
        $records = $this->crud->query("SELECT request_no FROM transaction_rm WHERE status = '0' and `period` = '$period' and workorder = '$workorder' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readRequestNos()
    {
        $records = $this->crud->query("SELECT request_no FROM transaction_rm WHERE status = '0' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readItemRm()
    {
        $post = $this->input->post('q') ? $this->input->post('q') : "";
        $division = $this->input->post('division');
        $item_category_id = $this->input->post('item_category_id');

        $where = "(`number` LIKE '%$post%' OR `name` LIKE '%$post%')";

        if (!empty($division)) {
            $where .= " AND division = '$division'"; 
        }
        if (!empty($item_category_id)) {
            $where .= " AND item_category_id = '$item_category_id'";
        }

        $records = $this->crud->query("SELECT id, number, name, uom 
                                       FROM item_rm 
                                       WHERE $where 
                                       ORDER BY `number` ASC");
                                       
        echo json_encode($records);
    }

    public function readPorItems()
    {
        $trans_type = $this->input->post('trans_type');
        
        if ($trans_type == 'REPLACEMENT NG FROM SUPPLIER') {
            $request_no_return = $this->input->post('request_no_return');
            
            $query = "SELECT 
                        a.item_rm_id, 
                        b.number as item_number, 
                        b.name as item_name, 
                        b.uom, 
                        a.specification, 
                        a.qty as qty_convertion, 
                        a.lotno,
                        
                        -- LOGIKA AKUMULASI: Qty Retur dikurangi Total Qty Replacement sebelumnya
                        (a.qty - COALESCE((
                            SELECT SUM(r.qty) 
                            FROM transaction_rm r 
                            WHERE r.transaction_type = 'REPLACEMENT NG FROM SUPPLIER' 
                              AND r.request_no_return = a.request_no 
                              AND r.item_rm_id = a.item_rm_id
                              AND r.deleted = 0
                        ), 0)) as max_qty,
                        
                        a.qty_mpq as mpq,
                        a.po_no,
                        0 as qty_label
                      FROM transaction_rm a
                      JOIN item_rm b ON a.item_rm_id = b.id
                      WHERE a.request_no = '$request_no_return' AND a.status = '0'";
                      
        } else {
            $receipt_no = $this->input->post('receipt_no');
            
            $query = "SELECT 
                        a.item_rm_id, 
                        b.number as item_number, 
                        b.name as item_name, 
                        b.uom, 
                        a.specification, 
                        a.qty_receipt as qty_convertion,
                        a.qty_receipt2 as max_qty,
                        a.qty_mpq as mpq,
                        a.po_no,
                        a.lotno,
                        e.division,
                        d.id as item_category_id,
                        0 as qty_label -- Skenario RETURN 0
                      FROM purchase_order_receipts a
                      JOIN item_rm b ON a.item_rm_id = b.id
                      JOIN purchase_orders c ON a.po_no = c.po_no and a.item_rm_id = c.item_rm_id
                      JOIN item_categories d ON b.item_category_id = d.id
                      JOIN purchase_requests e ON c.request_no = e.request_no
                      WHERE a.receipt_no = '$receipt_no'
                      GROUP BY a.receipt_id";
        }
                  
        $records = $this->crud->query($query);
        
        $final_data = [];
        foreach ($records as $r) {
            if ($trans_type == 'REPLACEMENT NG FROM SUPPLIER') {
                if ($r->max_qty > 0) {
                    $r->qty = $r->max_qty; 
                    
                    if ($r->mpq > 0) {
                        $r->qty_label = ceil($r->qty / $r->mpq);
                    } else {
                        $r->qty_label = 0;
                    }
                    
                    $final_data[] = $r;
                }
            } else {
                $r->qty = $r->max_qty;
                $final_data[] = $r;
            }
        }
        
        echo json_encode($final_data);
    }

    public function readProduct($product_family_id)
    {
        $records = $this->crud->query("SELECT b.number, b.name, b.id FROM transaction_rm a JOIN item_rm b ON a.item_rm_id = b.id WHERE b.item_family_id = '$product_family_id' GROUP BY b.id ORDER BY b.number asc");
        echo json_encode($records);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT number, name, id FROM item_rm WHERE status = '0' and `number` like '%$post%' or `name` like '$post'
        GROUP BY id ORDER BY number asc");
        echo json_encode($records);
    }

    public function readType()
    {
        $records = $this->crud->query("SELECT * FROM transactions WHERE status = '0'");
        echo json_encode($records);
    }

    public function readReceipts()
    {
        $supplier_id = $this->input->get('supplier_id');
        $query = "SELECT receipt_no FROM purchase_order_receipts WHERE `status` = '0'";
        if (!empty($supplier_id)) {
            $query .= " AND supplier_id = '$supplier_id'"; 
        }
        $query .= " GROUP BY receipt_no ORDER BY created_date desc";
        $records = $this->crud->query($query);
        echo json_encode($records);
    }

    public function readReturnTransactions()
    {
        $supplier_id = $this->input->get('supplier_id');
        $query = "SELECT a.request_no, a.purchase_order_receipt 
                  FROM transaction_rm a
                  WHERE a.transaction_type = 'RETURN NG TO SUPPLIER' AND a.status = '0'";
                  
        if (!empty($supplier_id)) {
            $query .= " AND a.supplier_id = '$supplier_id'"; 
        }
        
        $records = $this->crud->query($query);
        echo json_encode($records);
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM transaction_rm WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "TSN-" . $datenow . "-" . $autoID;
    }

    public function datatableUpdate($request_no){
        $request_no = base64_decode($request_no);

        // Select Query
        $this->db->select('a.*, 
                           b.number as item_number, 
                           b.name as item_name, 
                           b.uom,
                           a.qty_mpq as mpq,
                           a.qty_label');
        $this->db->from('transaction_rm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.request_no', $request_no);

        // Total Data
        $totalRows = $this->db->count_all_results('', false);
        
        // Get Data Array
        $records = $this->db->get()->result_array();
        
        // Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            // $filter_period = $this->input->get('filter_period');
            // $filter_workorder   = $this->input->get('filter_workorder');
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_product_family = $this->input->get('filter_product_family');
            // $filter_kanban_date = $this->input->get('filter_kanban_date');
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_transaction_type = $this->input->get('filter_transaction_type');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10; 
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];

            if ($id === "0") {
                //Select Query
                $this->db->select('a.*, c.number as item_number, c.name as item_name, c.uom');
                $this->db->from('transaction_rm a');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                if($filter_product_family != ""){
                    $this->db->where('c.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                // if($filter_kanban_date != ""){
                //     $this->db->where('a.request_date', $filter_kanban_date);
                // }
                if (!empty($filter_from) && !empty($filter_to)) {
                    $this->db->where("a.request_date >=", $filter_from);
                    $this->db->where("a.request_date <=", $filter_to);
                }
                if($filter_transaction_type != ""){
                    $this->db->where('a.transaction_type', $filter_transaction_type);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.request_no', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {

                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "period" => $record['period'],
                        "item_rm_id" => $record['item_rm_id'],
                        "workorder" => $record['workorder'],
                        "transaction_type" => $record['transaction_type'],
                        "transaction_kind" => $record['transaction_kind'],
                        "transaction_id" => $record['transaction_id'],
                        "remarks" => $record['remarks'],

                        "supplier_id" => $record['supplier_id'],
                        "purchase_order_receipt" => $record['purchase_order_receipt'],
                        "request_no_return" => $record['request_no_return'],
                        "division" => $record['division'],
                        "item_category_id" => $record['item_category_id'],

                        "state" => "closed"
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
                $this->db->from('transaction_rm a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.request_no', $id);
                $this->db->group_by('a.id');
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                if (!empty($filter_from) && !empty($filter_to)) {
                    $this->db->where("a.request_date >=", $filter_from);
                    $this->db->where("a.request_date <=", $filter_to);
                }
                $this->db->order_by('a.request_no', 'DESC');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {

                    $arr[] = array(
                        "id" => $record['id'],
                        "request_no" => $record['request_no'],
                        "request_id" => $record['request_id'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "period" => $record['period'],
                        "wp" => $record['wp'],
                        "workorder" => $record['workorder'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "qty" => $record['qty'],
                        "qty_mpq" => $record['qty_mpq'],
                        "qty_label" => $record['qty_label'],
                        "uom" => $record['uom'],
                        "specification" => $record['specification'],
                        "status" => $record['status'],
                        "remarks" => $record['remarks'],
                        "transaction_type" => $record['transaction_type'],
                        "transaction_kind" => $record['transaction_kind'],
                        "request_no_return" => $record['request_no_return'],
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date']
                    );
                }
                $result = !empty($arr) ? $arr : [];
                echo json_encode($result);
            }
        }
    }

    public function request_id($request_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(request_id) as kode FROM transaction_rm WHERE request_id like '%$request_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        
        if ($kode == NULL) {
            $autoID = sprintf("%03s", 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $request_no . "-" . $autoID;
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
            
    //         if (isset($post['qty']) && $post['qty'] <= 0) {
    //             echo json_encode(["status" => true, "message" => "Skipped"]); 
    //             return;
    //         }

    //         $post['request_id'] = $this->request_id($post['request_no']);

    //         if (!empty($_FILES['attachment']['name'])) {
    //             $attachment = $this->crud->upload('attachment', ["pdf", "jpg", "png", "jpeg"], 'assets/documents/transaction_rm/', ["request_no" => $post['request_no']], "transaction_rm", "attachment");
    //             $post['attachment'] = $attachment;
    //         }

    //         $datas = $this->crud->reads('transaction_rm', [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id'], "specification" => $post['specification']]);

    //         if (count($datas) > 0) {
    //             $send = $this->crud->update('transaction_rm', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], $post);
    //             echo $send;
    //         } else {
    //             $send = $this->crud->create('transaction_rm', $post);
    //             echo $send;
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            
            if (isset($post['qty']) && $post['qty'] <= 0) {
                echo json_encode(["status" => true, "message" => "Skipped"]); 
                return;
            }

            $post['request_id'] = $this->request_id($post['request_no']);

            if (!empty($_FILES['attachment']['name'])) {
                $attachment = $this->crud->upload('attachment', ["pdf", "jpg", "png", "jpeg"], 'assets/documents/transaction_rm/', ["request_no" => $post['request_no']], "transaction_rm", "attachment");
                $post['attachment'] = $attachment;
            }

            $datas = $this->crud->reads('transaction_rm', [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id'], "specification" => $post['specification']]);

            if (count($datas) > 0) {
                unset($post['id']); 
                $send = $this->crud->update('transaction_rm', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], $post);
                $this->_check_and_close_return($post);
                echo $send;
                
            } else {
                $trans_type = strtoupper($post['transaction_type'] ?? '');
                
                if ($trans_type === 'RETURN NG TO SUPPLIER') {
                    $send = $this->crud->create('transaction_rm', $post);
                    echo $send;
                    
                } else {
                    $send = $this->crud->createNotApproval('transaction_rm', $post);
                    $this->_check_and_close_return($post);
                    
                    echo $send;
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    private function _check_and_close_return($post)
    {
        $trans_type = strtoupper($post['transaction_type'] ?? '');
        
        if ($trans_type === 'REPLACEMENT NG FROM SUPPLIER') {
            
            $request_no_return = $post['request_no_return'] ?? '';
            $item_rm_id = $post['item_rm_id'] ?? '';

            if (!empty($request_no_return) && !empty($item_rm_id)) {
                
                $sqlReturn = $this->db->query("SELECT SUM(qty) as total_return FROM transaction_rm WHERE request_no = '$request_no_return' AND item_rm_id = '$item_rm_id' AND deleted = 0");
                $total_return = $sqlReturn->row()->total_return ?? 0;

                $sqlReplace = $this->db->query("SELECT SUM(qty) as total_replace FROM transaction_rm WHERE request_no_return = '$request_no_return' AND item_rm_id = '$item_rm_id' AND transaction_type = 'REPLACEMENT NG FROM SUPPLIER' AND deleted = 0");
                $total_replace = $sqlReplace->row()->total_replace ?? 0;

                if ($total_return > 0 && $total_replace >= $total_return) {
                    $this->db->where('request_no', $request_no_return);
                    $this->db->where('item_rm_id', $item_rm_id);
                    $this->db->where('transaction_type', 'RETURN NG TO SUPPLIER');
                    $this->db->update('transaction_rm', ['status' => 1]); 
                }
            }
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $id = $data['id'];

        $trans = $this->db->get_where('transaction_rm', ['id' => $id])->row();

        if (!$trans) {
            echo json_encode(["title" => "Error", "message" => "Data not found", "theme" => "error"]);
            return;
        }

        if ($trans->status == 1) {
            echo json_encode(["title" => "Failed", "message" => "Cannot delete closed transaction", "theme" => "error"]);
            return;
        }

        $trans_type = strtoupper($trans->transaction_type);

        if ($trans_type === 'REPLACEMENT NG FROM SUPPLIER') {
            
            if (!empty($trans->request_id)) {
                $this->crud->delete('transaction_labels', ["request_id" => $trans->request_id]);
            }
        }

        $send = $this->crud->delete('transaction_rm', ["id" => $id]);
        $this->crud->delete('wip_balances', ["request_no" => $trans->request_no, "item_rm_id" => $trans->item_rm_id]);

        if ($trans_type === 'REPLACEMENT NG FROM SUPPLIER' && !empty($trans->request_no_return)) {
            $this->_check_and_open_return($trans);
        }

        echo $send;
    }

        private function _check_and_open_return($trans)
    {
        $request_no_return = $trans->request_no_return;
        $item_rm_id = $trans->item_rm_id;

        $sqlReturn = $this->db->query("SELECT SUM(qty) as total_return FROM transaction_rm WHERE request_no = '$request_no_return' AND item_rm_id = '$item_rm_id' AND deleted = 0");
        $total_return = $sqlReturn->row()->total_return ?? 0;

        $sqlReplace = $this->db->query("SELECT SUM(qty) as total_replace FROM transaction_rm WHERE request_no_return = '$request_no_return' AND item_rm_id = '$item_rm_id' AND transaction_type = 'REPLACEMENT NG FROM SUPPLIER' AND deleted = 0");
        $total_replace = $sqlReplace->row()->total_replace ?? 0;

        if ($total_return > 0 && $total_replace < $total_return) {
            $this->db->where('request_no', $request_no_return);
            $this->db->where('item_rm_id', $item_rm_id);
            $this->db->where('transaction_type', 'RETURN NG TO SUPPLIER');
            
            // Kembalikan status menjadi 0 (Open)
            $this->db->update('transaction_rm', ['status' => 0]); 
        }
    }

    public function readSpec()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');
            $trans_type = @$this->input->post('trans_type');

            $date = ($trans_date == "") ? date("Y-m-d") : $trans_date;

            if($trans_type == 'BPB'){
                $records = $this->crud->query("
                    SELECT 
                        a.id AS item_rm_id,
                        a.number,
                        spec_data.specification,

                        (
                            COALESCE(scan_in.qty_scan_in, 0)
                            + COALESCE(os.qty_os_rm, 0)
                            + COALESCE(trans_in.qty_trans_rm_in, 0)
                            + COALESCE(ret.return_qty, 0)
                            + COALESCE(bpm.qty_scan_bpm, 0)
                            - COALESCE(issued.qty_issued, 0)
                            - COALESCE(trans_out.qty_trans_rm_out, 0)
                        ) AS begin_stock

                    FROM item_rm a

                    LEFT JOIN (
                        SELECT item_rm_id, specification
                        FROM (
                            SELECT item_rm_id, specification
                            FROM purchase_order_receipts
                            WHERE receipt_date <= '$date'
                            UNION
                            SELECT item_rm_id, specification
                            FROM transaction_rm
                            WHERE request_date <= '$date'
                        ) spec
                        GROUP BY item_rm_id, specification
                    ) spec_data ON spec_data.item_rm_id = a.id

                    LEFT JOIN (
                        SELECT b.item_rm_id, b.specification, SUM(a.qty) AS qty_scan_in
                        FROM scan_item_receipts a
                        JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                        WHERE b.receipt_date <= '$date'
                        GROUP BY b.item_rm_id, b.specification
                    ) scan_in ON scan_in.item_rm_id = a.id AND scan_in.specification = spec_data.specification

                    LEFT JOIN (
                        SELECT item_rm_id, SUM(qty) AS qty_os_rm
                        FROM os_rm
                        WHERE trans_date <= '$date'
                        GROUP BY item_rm_id
                    ) os ON os.item_rm_id = a.id

                    LEFT JOIN (
                        SELECT item_rm_id, specification, SUM(qty) AS qty_trans_rm_in
                        FROM transaction_rm
                        WHERE request_date <= '$date' AND transaction_kind = 'IN'
                        GROUP BY item_rm_id, specification
                    ) trans_in ON trans_in.item_rm_id = a.id AND trans_in.specification = spec_data.specification

                    LEFT JOIN (
                        SELECT a.item_rm_id, SUM(c.qty) as return_qty
                        FROM return_materials a
                        JOIN return_material_labels b ON a.return_id = b.return_id
                        JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                        WHERE a.return_date <= '$date'
                        GROUP BY a.item_rm_id
                    ) ret ON ret.item_rm_id = a.id

                    LEFT JOIN (
                        SELECT item_rm_id, SUM(qty) AS qty_scan_bpm
                        FROM scan_item_bpm
                        WHERE DATE_FORMAT(request_date, '%Y-%m-%d') <= '$date'
                        GROUP BY item_rm_id
                    ) bpm ON bpm.item_rm_id = a.id

                    LEFT JOIN (
                        SELECT item_rm_id, SUM(qty) AS qty_issued
                        FROM issued_material_details
                        WHERE created_date <= '$date'
                        GROUP BY item_rm_id
                    ) issued ON issued.item_rm_id = a.id

                    LEFT JOIN (
                        SELECT item_rm_id, specification, SUM(qty) AS qty_trans_rm_out
                        FROM transaction_rm
                        WHERE request_date <= '$date' AND transaction_kind = 'OUT'
                        GROUP BY item_rm_id, specification
                    ) trans_out ON trans_out.item_rm_id = a.id AND trans_out.specification = spec_data.specification

                    WHERE a.id = '$item_rm_id'
                    GROUP BY a.id, a.number, spec_data.specification
                    HAVING begin_stock > 0
                    ORDER BY a.number
                ");
            }else{
                $records = $this->crud->query("SELECT spec_data.specification
                    FROM item_rm a
                    LEFT JOIN (
                        SELECT item_rm_id, specification
                        FROM (
                            SELECT item_rm_id, specification
                            FROM purchase_order_receipts
                            WHERE receipt_date <= '$date'
                            UNION
                            SELECT item_rm_id, specification
                            FROM transaction_rm
                            WHERE request_date <= '$date'
                        ) specs
                        GROUP BY item_rm_id, specification
                    ) spec_data ON spec_data.item_rm_id = a.id
                    WHERE a.id = '$item_rm_id'
                    GROUP BY a.id, a.number, spec_data.specification
                    ORDER BY a.number
                ");
            }

            

            echo json_encode($records);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=transaction_rm_$format.xls");
        }
        // $filter_period = $this->input->get('filter_period');
        // $filter_workorder   = $this->input->get('filter_workorder');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_product_family = $this->input->get('filter_product_family');
        // $filter_kanban_date = $this->input->get('filter_kanban_date');
        $filter_from  = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_transaction_type = $this->input->get('filter_transaction_type');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('transaction_rm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->like("a.period", $filter_period);
        // $this->db->like("a.workorder", $filter_workorder);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if($filter_product_family != ""){
            $this->db->where('b.item_family_id', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('a.item_rm_id', $filter_product_no);
        }
        // if($filter_kanban_date != ""){
        //     $this->db->where('a.request_date', $filter_kanban_date);
        // }
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where("a.request_date >=", $filter_from);
            $this->db->where("a.request_date <=", $filter_to);
        }
        if($filter_transaction_type != ""){
            $this->db->where('a.transaction_type', $filter_transaction_type);
        }
        $this->db->order_by('a.request_no', 'DESC');
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
                                <small>TRANSACTION RM</small>
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
                <th>Request No</th>
                <th>Request Date</th>
                <th>Requester</th>
                <th>Type</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['transaction_type'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_label($request_no)
    {
        // 1. Config & Decode Parameter
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $request_no = base64_decode($request_no);
        
        // Tarik semua item yang ada di request_no ini
        $rm_datas = $this->crud->reads('transaction_rm', [], ["request_no" => $request_no, "deleted" => 0]);
        $request_ids = array();

        // 2. Looping Generate Label
        foreach($rm_datas as $rm_data){
            // var_dump($rm_data);
            // return;

            $request_id = $rm_data->request_id;
            $qty_rm = $rm_data->qty;
            
            // Cek apakah label untuk request_id ini sudah pernah di-generate sebelumnya
            $rm_label = $this->crud->reads('transaction_labels', [], ["request_id" => $request_id, "deleted" => 0]);
            
            // Jika belum ada label DAN qty_label > 0 (Artinya ini transaksi Replacement, bukan Return)
            if (!$rm_label && $rm_data->qty_label > 0) {

                for ($i = 0; $i < $rm_data->qty_label; $i++) {
                    // Generate Label ID (Format: {request_id}0001, dst)
                    $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM transaction_labels WHERE request_id = '$request_id'");
                    $rowID = $sqlGetID->row();
                    $label = $rowID->kode;
                    
                    if ($label == NULL) {
                        $autoID = $request_id . sprintf("%04s", 1);
                    } else {
                        $urutan = (int) substr($label, -4);
                        $autoID = $request_id . sprintf("%04s", $urutan + 1);
                    }
                    
                    // Kalkulasi Qty per Lembar Label berdasarkan MPQ
                    if ($qty_rm > $rm_data->qty_mpq && $rm_data->qty_mpq > 0) {
                        $qty = $rm_data->qty_mpq;
                    } else {
                        $qty = $qty_rm;
                    }
                    
                    $date = new DateTime($rm_data->request_date);
                    $p_month = $date->format('m'); 
                    $p_year = $date->format('y');
                    
                    // Simpan Label ke tabel transaction_labels
                    $arrLabel = [
                        "request_id" => $request_id,
                        "request_no" => $request_no,
                        "label_no"   => $autoID,
                        "qty"        => $qty,
                        "p_month"    => $p_month,
                        "p_year"     => $p_year,
                        "lot_no"      => $rm_data->lotno 
                    ];

                    $this->crud->create('transaction_labels', $arrLabel);
                    
                    // Kurangi sisa qty yang belum dibuatkan label
                    $qty_rm = ($qty_rm - $rm_data->qty_mpq);
                }
            }
            
            // Masukkan ke dalam array untuk ditarik query cetak di bawah
            if ($rm_data->qty_label > 0) {
                $request_ids[] = $request_id;
            }
        }
        
        // 3. Render HTML Cetak Barcode (Hanya jika ada request_ids)
        if (!empty($request_ids)) {
            // Gunakan alias b.request_date as receipt_date agar variabel di HTML bawaan Anda tidak perlu banyak dirubah
            $this->db->select('a.*, b.request_date as receipt_date, c.number, c.name, d.location, d.area, c.color, c.uom, b.lotno');
            $this->db->from('transaction_labels a');
            $this->db->join('transaction_rm b', 'a.request_id = b.request_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where_in('a.request_id', $request_ids);
            $this->db->order_by('a.label_no', 'asc');
            
            $records = $this->db->get()->result_object();
        } else {
            $records = [];
        }

        $html = '<html>
                    <head>
                        <title>' . $request_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        
        if ($records) {
            $html .= '<div style="width: 55mm;">';
            foreach ($records as $record) {
                $padding = "padding:3mm 5mm 3mm 3mm;";
                
                // Generate QRcode Image
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                
                $html .= '  <div style="max-width: 50mm; max-height:40mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>   
                                        <th colspan="3" style="font-size:8px; text-align:center;">
                                            <img src="' . base_url('assets/image/bpi_logo.png') . '" width="10" style="float: left; margin-right: 5px;">
                                            <b>' . $config->name . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:35px;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number . '</b></small>
                                                <br>
                                                <b style="font-size:9px;">' . $record->name . " - " .$record->color.'</b>
                                            </div>
                                            
                                            <div style="float:right;">
                                                <small style="font-size:14px;"><b>' . $record->p_month . '</b></small><small style="font-size:10px;"><b>' ." - ". $record->p_year . '</b></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small style="font-size:10px">Quantity<br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b></small>
                                            <small style="font-size:13px; float: right;"><b>'. $record->uom . '</b></small>
                                            </th>
                                        <th style="text-align:left">
                                            <small style="font-size:7px">Location</small><br>
                                            <small style="font-size:7px">Lot No. </small><b style="font-size:8px;">' . $record->lotno . '</b>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $record->receipt_date . '</b>
                                             
                                            </div>
                                            <div style="display: inline-block; float:right;">
                                                <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: right; margin-right: 5px; margin-top: 5px;">
                                            </div>
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Label No :</small><br>
                                                <b style="font-size:7px;">' . $record->label_no . '</b>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <small style="font-size:4px">QC Passed By : ' . $this->session->username . '</small><br>
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="55"/>

                                        </th>
                                    </tr>
                                </table>
                            </div>';
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            // Tampilan jika data tidak ditemukan ATAU jika transaksi adalah Return (qty_label = 0)
            $html .= "<br><br><br><center><h3>Data not found or labels are not required for this transaction type.</h3></center>";
        }
        
        die($html);
    }

    public function print_label_grid($encoded_request_id)
    {
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $request_id = base64_decode($encoded_request_id);
        
        $rm_data = $this->crud->read('transaction_rm', [], ["request_id" => $request_id, "deleted" => 0]);
        
        if (!$rm_data) {
            die("Data not found");
        }

        $request_no = $rm_data->request_no;
        $qty_rm = $rm_data->qty;
        
        $rm_label = $this->crud->reads('transaction_labels', [], ["request_id" => $request_id, "deleted" => 0]);
        
        if (!$rm_label && $rm_data->qty_label > 0) {
            for ($i = 0; $i < $rm_data->qty_label; $i++) {
                
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM transaction_labels WHERE request_id = '$request_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                
                if ($label == NULL) {
                    $autoID = $request_id . sprintf("%04s", 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $request_id . sprintf("%04s", $urutan + 1);
                }
                
                if ($qty_rm > $rm_data->qty_mpq && $rm_data->qty_mpq > 0) {
                    $qty = $rm_data->qty_mpq;
                } else {
                    $qty = $qty_rm;
                }
                
                $date = new DateTime($rm_data->request_date);
                $p_month = $date->format('m'); 
                $p_year = $date->format('y');
                
                $arrLabel = [
                    "request_id" => $request_id,
                    "request_no" => $request_no,
                    "label_no"   => $autoID,
                    "qty"        => $qty,
                    "p_month"    => $p_month,
                    "p_year"     => $p_year,
                    "lot_no"      => $rm_data->lotno 
                ];

                $this->crud->create('transaction_labels', $arrLabel);
                
                $qty_rm = ($qty_rm - $rm_data->qty_mpq);
            }
        }
        
        $this->db->select('a.*, b.request_date as receipt_date, c.number, c.name, d.location, d.area, c.color, c.uom, b.lotno');
        $this->db->from('transaction_labels a');
        $this->db->join('transaction_rm b', 'a.request_id = b.request_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.request_id', $request_id);
        $this->db->order_by('a.label_no', 'asc');
        
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $request_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        
        if ($records) {
            $html .= '<div style="width: 55mm;">';
            foreach ($records as $record) {
                $padding = "padding:3mm 5mm 3mm 3mm;";
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                
                $html .= '  <div style="max-width: 50mm; max-height:40mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>   
                                        <th colspan="3" style="font-size:8px; text-align:center;">
                                            <img src="' . base_url('assets/image/bpi_logo.png') . '" width="10" style="float: left; margin-right: 5px;">
                                            <b>' . $config->name . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:35px;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number . '</b></small>
                                                <br>
                                                <b style="font-size:9px;">' . $record->name . " - " .$record->color.'</b>
                                            </div>
                                            <div style="float:right;">
                                                <small style="font-size:14px;"><b>' . $record->p_month . '</b></small><small style="font-size:10px;"><b>' ." - ". $record->p_year . '</b></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small style="font-size:10px">Quantity<br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b></small>
                                            <small style="font-size:13px; float: right;"><b>'. $record->uom . '</b></small>
                                        </th>
                                        <th style="text-align:left">
                                            <small style="font-size:7px">Location</small><br>
                                            <small style="font-size:7px">Lot No. </small><b style="font-size:8px;">' . $record->lotno . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block; float:right;">
                                                <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: right; margin-right: 5px; margin-top: 5px;">
                                            </div>
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Label No :</small><br>
                                                <b style="font-size:7px;">' . $record->label_no . '</b>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <small style="font-size:4px">QC Passed By : ' . $this->session->username . '</small><br>
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="55"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or labels are not required for this transaction type.</h3></center>";
        }
        die($html);
    }
}
