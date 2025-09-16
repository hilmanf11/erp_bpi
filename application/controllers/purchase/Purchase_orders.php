<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_orders extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[100]'); //item_number
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purchase_orders', ["name" => $post]);
        echo json_encode($send);
    }

    public function readCategories()
    {
        $records = $this->crud->query("SELECT `number`, id FROM item_categories WHERE status = '0'");
        echo json_encode($records);
    }

    // public function readPono()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $supplier_id = $this->input->get('supplier_id');

    //     $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
    //     $this->db->from('purchase_orders a');
    //     $this->db->join('suppliers b', 'a.supplier_id = b.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.status', 0);
    //     $this->db->like('a.supplier_id', $supplier_id);
    //     $this->db->like('a.po_no', $post);
    //     $this->db->group_by('a.po_no');
    //     $this->db->order_by('a.created_date', 'desc');
        
    //     $records = $this->db->get()->result_object();
    //     echo json_encode($records);
    // }

    public function readPono()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = $this->input->get('supplier_id');

        // Ambil semua po_no yang memiliki item dengan price = 1
        $this->db->select('po_no');
        $this->db->from('purchase_orders');
        $this->db->where('status_price', "Incomplete");
        $po_with_price_one = $this->db->get()->result_array();
        $exclude_po_nos = array_column($po_with_price_one, 'po_no');

        // Query utama
        $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);

        // Filter berdasarkan inputan
        if (!empty($supplier_id)) {
            $this->db->like('a.supplier_id', $supplier_id);
        }

        if (!empty($post)) {
            $this->db->like('a.po_no', $post);
        }

        // Tambahkan pengecualian untuk po_no yang price-nya 1
        if (!empty($exclude_po_nos)) {
            $this->db->where_not_in('a.po_no', $exclude_po_nos);
        }

        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');

        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readPonos()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
    
        $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->like('a.po_no', $post);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');
        
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readTotalPo()
    {
        $item_id = $this->input->post('item_rm_id');
        $this->db->select('item_rm_id, SUM(qty) as qty');
        $this->db->from('purchase_orders');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where('item_rm_id', $item_id);
        $this->db->group_by('item_rm_id');
        $records = $this->db->get()->row();

        echo json_encode($records);
    }

    public function completePo()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $update = $this->db->update('purchase_orders', ["status" => 2], ["id" => $id]);// , "qty" => 0
        echo $update;
    }

    public function uncompletePo()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $update = $this->db->update('purchase_orders', ["status" => 0], ["id" => $id]);// , "qty" => 0
        echo $update;
    }

    public function checkStatus()
    {
        $po_no = $this->input->post('po_no');
        $this->db->select('status');
        $this->db->from('purchase_orders');
        $this->db->where('po_no', $po_no);
        // $this->db->where('status', 1);
        $record = $this->db->get()->row_array();

        echo json_encode($record);
    }

    public function checkTotalSub()
    {
        $po_no = $this->input->post('po_no');
        $this->db->select('total_sub');
        $this->db->from('purchase_orders');
        $this->db->where('po_no', $po_no);
        $record = $this->db->get()->row_array();

        echo json_encode($record);
    }

    public function checkPassword()
    {
        $inputPassword = base64_decode($this->input->post('password'));
        $sessionPassword = $this->session->password;

        if ($inputPassword === $sessionPassword) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_from_update = $this->input->get('filter_from_update');
            $filter_to_update   = $this->input->get('filter_to_update');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_suppliers = $this->input->get('filter_suppliers');
            $filter_categories = $this->input->get('filter_categories');
            $filter_status = $this->input->get('filter_status');
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
                $this->db->select('a.po_no, a.request_no, a.total_dp,
                    a.po_date,
                    d.name as supplier_name,
                    d.id as supplier_id,
                    e.uom_default as uom,
                    a.month_1,
                    a.month_2,
                    a.month_3,
                    a.month_4,
                    a.discount,
                    d.currency, 
                    SUM(a.qty) as qty, 
                    SUM(a.price) as price, 
                    SUM(a.total) as total_price,
                    a.status,
                    COUNT(a.status) as total_status,
                    COUNT(a.approved_to) as total_approved_to,
                    COUNT(a.status_price) as total_status_price,
                    f.max_status as status_pi,
                    a.total_sub,
                    a.approved_to, 
                    a.approved_by, 
                    a.approved_date, 
                    a.total_vat, 
                    a.income_tax, 
                    a.income_total, 
                    a.disc_pr,
                    a.taxes, 
                    a.notes,
                    a.discount_total,
                    h.total_status_complete,
                    i.total_status_open,
                    g.total_status_close,
                    j.number as category_code,
                    k.total_approved_to_checking,
                    l.total_approved_to_approved,
                    m.status_price_complete,
                    n.status_price_incomplete');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('(SELECT po_no, MIN(status) AS max_status FROM purchase_order_receipts GROUP BY po_no) f', 'a.po_no = f.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_complete FROM purchase_orders WHERE status = 2 GROUP BY po_no) h', 'a.po_no = h.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_open FROM purchase_orders WHERE status = 0 GROUP BY po_no) i', 'a.po_no = i.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status_price) as status_price_complete FROM purchase_orders WHERE status_price = "Complete" GROUP BY po_no) m', 'a.po_no = m.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status_price) as status_price_incomplete FROM purchase_orders WHERE status_price = "Incomplete" GROUP BY po_no) n', 'a.po_no = n.po_no', 'left');
                $this->db->join('item_categories j', 'b.item_category_id = j.id','left');
                $this->db->join('(SELECT po_no, COUNT(approved_to) as total_approved_to_checking FROM purchase_orders WHERE approved_to != "" || approved_to = NULL GROUP BY po_no) k', 'a.po_no = k.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(approved_to) as total_approved_to_approved FROM purchase_orders WHERE approved_to = "" || approved_to = NULL GROUP BY po_no) l', 'a.po_no = l.po_no', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                if ($filter_from_update != "" or $filter_to_update != "") {
                    $this->db->where('DATE(a.updated_date) >=', $filter_from_update);
                    $this->db->where('DATE(a.updated_date) <=', $filter_to_update);
                }
                $this->db->like('a.po_no', $filter_po_no);
                $this->db->like('d.id', $filter_suppliers);
                $this->db->like('b.item_category_id', $filter_categories);
                $this->db->like('a.status', $filter_status);
                $this->db->group_by('a.po_no');

                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('a.po_no', 'DESC');
                $this->db->order_by('a.po_date', 'DESC');
                $this->db->order_by('a.status', 'ASC');
                
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {
                    // if ($record['total_status'] == $record['total_status_close']) {
                    //     $status = "1";
                    // } elseif ($record['status'] == 2) {
                    //     $status = "2";
                    // } elseif ($record['total_status_complete'] >= 1) {
                    //     $status = "2";
                    // } elseif ($record['total_status'] == $record['total_status_complete']) {
                    //     $status = "2";
                    // } elseif ($record['status'] == 0) {
                    //     $status = "0";
                    // } else {
                    //     $status = "0";
                    // }

                    // if ($record['total_status'] == $record['total_status_close']) {
                    //     $status = "1";
                    // }elseif ($record['total_status'] == $record['total_status_complete']) {
                    //     $status = "2";
                    // } else {
                    //     $status = "0";
                    // }

                    if ($record['total_status'] == $record['total_status_open']) {
                        $status = "0";
                    } elseif ($record['total_status'] == $record['total_status_close']) {
                        $status = "1";
                    } elseif ($record['total_status'] == $record['total_status_complete']) {
                        $status = "2";
                    } elseif ($record['total_status_open'] >= 1) {
                        $status = "0";
                    } elseif ($record['total_status_complete'] >= 1) {
                        $status = "2";
                    } elseif ($record['total_status_close'] >= 1) {
                        $status = "1";
                    } else {
                        $status = "0";
                    }

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

                    if ($record['total_status_price'] == $record['status_price_complete']) {
                        $status_price = "Complete";
                    } elseif ($record['total_status_price'] == $record['status_price_incomplete']) {
                        $status_price = "Incomplete";
                    } elseif ($record['status_price_incomplete'] >= 1) {
                        $status_price = "Incomplete";
                    } else {
                        $status_price = "Complete";
                    }

                    $arr[] = array(
                        "id" => $record['po_no'],
                        "po_no" => $record['po_no'],
                        "request_no" => $record['request_no'],
                        "po_date" => $record['po_date'],
                        "uom" => $record['uom'],
                        "currency" => $record['currency'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "status" => $status,
                        "status_pi" => $record['status_pi'],
                        "status1" => $record['total_status'],
                        "status2" => $record['total_status_close'],
                        "status_complete" => $record['total_status_complete'],
                        "total_dp" => $record['total_dp'],
                        "total_sub" => $record['total_sub'],
                        "income_tax" => $record['income_tax'],
                        "income_total" => $record['income_total'],
                        "total_price" => $record['total_price'],
                        "total_vat" => $record['total_vat'],
                        "taxes" => $record['taxes'], 
                        "disc_pr" => $record['disc_pr'],
                        "discount_total" => $record['discount_total'],
                        "total_grand" => ($record['total_price'] + $record['total_vat']) - $record['total_dp'] - $record['income_total'] - $record['discount_total'],
                        "state" => "closed",
                        "category_code" => $record['category_code'],
                        "notes" => $record['notes'],
                        "approved_to" => $approved_to,
                        "status_price" => $status_price,
                        "status_price_complete" => $record['status_price_complete'],
                        "status_price_incomplete" => $record['status_price_incomplete'],
                        // "approved_by" => $record['approved_by'],
                        // "approved_date" => $record['approved_date'],
                        "total_checking" => $record['total_approved_to_checking'],
                        "total_approved" => $record['total_approved_to_approved'],
                        "total_sub" => $record['total_sub'],
                        "datatable" => 1
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    b.number as item_number,
                    b.name as item_name,
                    e.uom_default as uom,
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency, 
                    e.mpq, 
                    e.moq,
                    f.max_status as status_pi,
                    a.price,
                    a.status, 
                    g.number as category_code, 
                    (a.qty * a.price) as total_price');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id= c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('(SELECT po_no, item_rm_id, MAX(status) AS max_status FROM purchase_order_receipts GROUP BY po_no, item_rm_id) f', 'a.po_no = f.po_no and a.item_rm_id = f.item_rm_id', 'left');
                $this->db->join('item_categories g', 'b.item_category_id = g.id','left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                $this->db->like('a.po_no', $id);
                $this->db->like('d.id', $filter_suppliers);
                $this->db->like('b.item_category_id', $filter_categories);
                $this->db->like('a.status', $filter_status);
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_no', 'DESC');
                $records = $this->db->get()->result_array();

                echo json_encode($records);
            }
        }
    }

    public function datatable_updates()
    {
        $po_no = base64_decode($this->input->get('po_no'));
        $this->db->select('a.*,  
            b.number as item_number, 
            b.name as item_name,
            b.uom,
            d.id as supplier_id, 
            d.number as supplier_number, 
            d.name as supplier_name,
            c.name as category_name,
            e.mpq, 
            e.moq,
            d.vat_status,
            ((a.qty * a.price)-(a.qty * a.price * (a.discount/100))) as amount,
            d.currency');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $obj = array();
        foreach ($records as $record) {
            $total_sub += $record['amount'];
            array_push($obj, $record);
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 2);
        die(json_encode($arr));

        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $items = $this->crud->read('item_rm', [], ['id' => $post['item_rm_id']]); //item_number
                $categorys = $this->crud->read('item_categories', [], ['id' => $items->item_category_id]);
                $suppliers = $this->crud->read('suppliers', [], ["id" => $post['supplier_id']]);
                $supplier_items = $this->crud->read('supplier_items', [], ["item_rm_id" => $items->id, "supplier_id" => $post['supplier_id']]);
                $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id']]);
                $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $post['request_no']]);
                $config = $this->crud->read("config");

                $divisions = $purchaseRequests->division;
                $datenow2    = $divisions .$categorys->number. date("ym");
                $datenow    = $divisions .$categorys->number. date("y");
                $currentYear = date("Y");
                $sqlGetID   = $this->db->query("SELECT max(po_no) as kode FROM purchase_orders WHERE po_no like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;

                if ($kode == NULL) {
                    $autoID = "0001";

                    $po_no = "PO" . $datenow2 . "-" . $autoID;
                } else {
                    if ($purchaseOrder) {
                        $po_no = $purchaseOrder->po_no;
                    } else {
                        $urutan = (int)substr($kode, -4);
                        $urutan++;
                        $autoID = sprintf("%04s", $urutan);

                        $po_no = "PO" . $datenow2 . "-" . $autoID;
                       
                    }
                }

                if ($suppliers->vat_status == "VAT") {
                    $taxes = $config->tax;
                } else {
                    $taxes = 0;
                }

                $data = array(
                    "supplier_id" => $post['supplier_id'],
                    "item_rm_id" => $items->id,
                    "request_no" => $post['request_no'],
                    "request_date" => $post['request_date'],
                    "request_name" => $post['request_name'],
                    "po_date" => $post['po_date'],
                    "po_no" => $po_no,
                    "po_name" => $this->session->name,
                    "delivery_date" => $post['delivery_date'],
                    "qty" => $post['qty'],
                    "discount" => $post['discount'],
                    "discount_nominal" => $post['discount_nominal'],
                    "price" => $post['price'],
                    "currency" => $post['currency'],
                    "total" => $post['total'],
                    "taxes" => $post['taxes'],
                    "type" => $post['type'],
                    "remarks" => $post['remarks'],
                    "notes" => $post['notes'],
                    "month_1" => $post['month_1'],
                    "month_2" => $post['month_2'],
                    "month_3" => $post['month_3'],
                    "month_4" => $post['month_4'],
                    "total_sub" => $post['total_sub'],
                    "status_price" => $post['status_price'],
                );

                $send = $this->crud->create('purchase_orders', $data);
                $this->db->where('request_no', $post['request_no']);
                $this->db->where('item_rm_id', $items->id);
                $this->db->update("purchase_requests", ["status" => 1]);
                echo json_encode($send);
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $items = $this->crud->read('item_rm', [], ['id' => $post['item_rm_id']]);
            $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id'], "item_rm_id" => $items->id]);
            $qty = @$purchaseOrder->qty;
            $supplier_id = @$purchaseOrder->supplier_id;
            $price = @$purchaseOrder->price;
            $month_1 = @$purchaseOrder->month_1;
            $month_2 = @$purchaseOrder->month_2;
            $month_3 = @$purchaseOrder->month_3;
            $month_4 = @$purchaseOrder->month_4;
            $discount = @$purchaseOrder->discount;
            $discount_nominal = @$purchaseOrder->discount_nominal;

            if($qty == $post['qty'] && $supplier_id == $post['supplier_id'] && $price == $post['price'] && $month_1 == $post['month_1'] && $month_2 == $post['month_2'] && $month_3 == $post['month_3'] && $month_4 == $post['month_4'] && $discount == $post['discount'] && $discount_nominal == $post['discount_nominal']){
                //Dokumentasi : update tidak meminta Approval
                        $purchase_orders = $this->db->update('purchase_orders',["supplier_id" => $post['supplier_id'],
                        "qty" => $post['qty'],
                        "discount" => $post['discount'],
                        "discount_nominal" => $post['discount_nominal'],
                        "po_date" => $post['po_date'],
                        "price" => $post['price'],
                        "total" => $post['total'],
                        "taxes" => $post['taxes'],
                        "delivery_date" => $post['delivery_date'],
                        "remarks" => $post['remarks'],
                        "notes" => $post['notes'],
                        "month_1" => $post['month_1'],
                        "month_2" => $post['month_2'],
                        "month_3" => $post['month_3'],
                        "month_4" => $post['month_4'],
                        "total_sub" => $post['total_sub'],
                        "disc_pr" => $post['disc_pr'],
                        "discount_total" => $post['discount_total'],
                        "income_tax" => $post['income_tax'],
                        "income_total" => $post['income_total'],
                        "total_dp" => $post['total_dp'],
                        "total_grand" => $post['total_grand'],
                        "total_vat" => $post['total_vat'],
                        "total_dpp" => $post['total_dpp'],
                        "revision" => (@$purchaseOrder->revision + 1)
                    ],["request_no" => $post['request_no'], "item_rm_id" => $items->id]);
                //
            } else{
                $purchase_orders = $this->crud->update('purchase_orders',["request_no" => $post['request_no'],"item_rm_id" => $items->id],
                    [   "supplier_id" => $post['supplier_id'],
                        "qty" => $post['qty'],
                        "discount" => $post['discount'],
                        "discount_nominal" => $post['discount_nominal'],
                        "po_date" => $post['po_date'],
                        "price" => $post['price'],
                        "total" => $post['total'],
                        "taxes" => $post['taxes'],
                        "delivery_date" => $post['delivery_date'],
                        "remarks" => $post['remarks'],
                        "notes" => $post['notes'],
                        "month_1" => $post['month_1'],
                        "month_2" => $post['month_2'],
                        "month_3" => $post['month_3'],
                        "month_4" => $post['month_4'],
                        "total_sub" => $post['total_sub'],
                        "disc_pr" => $post['disc_pr'],
                        "discount_total" => $post['discount_total'],
                        "income_tax" => $post['income_tax'],
                        "income_total" => $post['income_total'],
                        "total_dp" => $post['total_dp'],
                        "total_grand" => $post['total_grand'],
                        "total_vat" => $post['total_vat'],
                        "total_dpp" => $post['total_dpp'],
                        "status_price" => $post['status_price'],
                        "revision" => (@$purchaseOrder->revision + 1)
                    ]
                );
            }

            $purchase_requests = $this->db->update('purchase_requests',["qty" => $post['qty']],["request_no" => $post['request_no'], "item_rm_id" => $items->id]);

            echo $purchase_orders;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update_approval()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('signatures', [], $post);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_orders', $data);
        $update = $this->crud->update('purchase_requests', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $send;
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $po_date = $this->input->post('po_date');
        $p_date_start = date("Y-m-d", strtotime($po_date . "+1 month"));
        $p_date_to = date('Y-m-d', strtotime('+4 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    public function print_po($po_no)
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_orders->supplier_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();
        $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_orders"]);
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);

        $po_period = $purchase_orders->po_date;
        $month = date('m', strtotime($po_period));

        $currentYear = date("y");
        $currentMonth = (int)$month;

        // Fungsi untuk mengatur bulan dan tahun jika melebihi 12 bulan
        function getForecastMonth($bulan_array, $currentMonth, $monthIncrement, $currentYear) {
            $newMonth = ($currentMonth + $monthIncrement) % 12;
            $newYear = $currentYear + floor(($currentMonth + $monthIncrement) / 12);
            
            if ($newMonth == 0) {
                $newMonth = 12;
                $newYear--;
            }
            
            return $bulan_array[$newMonth] . "-" . $newYear;
        }

        $bulan_array = array(
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        );

        $month_1 = getForecastMonth($bulan_array, $currentMonth, 1, $currentYear);
        $month_2 = getForecastMonth($bulan_array, $currentMonth, 2, $currentYear);
        $month_3 = getForecastMonth($bulan_array, $currentMonth, 3, $currentYear);
        $month_4 = getForecastMonth($bulan_array, $currentMonth, 4, $currentYear);

       
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
        
        
        if($purchase_orders->approved == 0){
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($purchase_orders->approved == 1) {
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($purchase_orders->approved == 2) {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($purchase_orders->approved == 3) {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '';
        } else {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '<img src="' . base_url('assets/image/qrcode/' . $user_3->name . '.png') . '" width="80"/>';
        }
        
        
        //Config Page
        $rows = 15;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
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
        $judul = "PURCHASE ORDER"; 
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.id as item_id, b.number as item_number, b.name as item_name, b.uom, c.currency, a.price, b.description, a.month_1, a.month_2, a.month_3, a.month_4, 
            b.item_category_id as category_id, b.item_family_id as family_id');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }

            foreach ($records as $row) {
                if ($row['price'] == 1) {
                    $judul = "DRAFT PURCHASE ORDER";
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                                    <small>NO : ' . @$purchase_orders->po_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$supplier->name . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            PO Date:<br><b>' . date("d F Y", strtotime($purchase_orders->po_date)) . '</b><br>
                                            Revision:<br><b>' . $purchase_orders->revision . '</b><br>
                                            Revision Date:<br><b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms:<br><b>' . $supplier->payment_term . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Address</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->address . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Reff No</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$purchase_orders->request_no . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->attention . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Phone</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->telp . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Fax</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->fax . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Email</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->email . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2" width="30" style="text-align:center;">No</th>
                                        <th rowspan="2" width="150" style="text-align:center;">Product No</th>
                                        <th rowspan="2" width="150" style="text-align:center;">Product Name</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Specification</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Uom</th>
                                        
                                        <th rowspan="2" width="50" style="text-align:center;">Unit<br>Price</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Currency</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Discount</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Amount</th>
                                        <th rowspan="2" width="80" style="text-align:center;">Delivery<br>Date</th>
                                        <th colspan="4" width="80" style="text-align:center;">Forecast</th>

                                        <tr>
                                            <th width="80" style="text-align:center;">'.$month_1.'</th>
                                            <th width="80" style="text-align:center;">'.$month_2.'</th>
                                            <th width="80" style="text-align:center;">'.$month_3.'</th>
                                            <th width="80" style="text-align:center;">'.$month_4.'</th>
                                        </tr>
                                        
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $subtotal += ($record['qty'] * $record['price']);
                if ($record['currency'] != "IDR") {
                    $digits = 4;
                } else {
                    $digits = 2;
                }

                if (($record['category_id'] == 'C01' && $record['family_id'] == 'P27') || ($record['category_id'] == 'C07' && $record['family_id'] == 'P31')) {
                    $number = $record['item_id'];
                    $name = '-';
                } else {
                    $number = $record['item_number'];
                    $name = $record['item_name'];
                }
                
                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $number . '</td>
                                <td><span style="font-size:10px;">' . $name . '</span></td>
                                <td style="text-align:center;">' . $record['description'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty'], 2) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                
                                <td style="text-align:right;">' . number_format($record['price'], 2) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:center;">' . $record['discount_nominal'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                                <td style="text-align:center;">' . $record['delivery_date'] . '</td>
                                <td style="text-align:center;">' . $record['month_1'] . '</td>
                                <td style="text-align:center;">' . $record['month_2'] . '</td>
                                <td style="text-align:center;">' . $record['month_3'] . '</td>
                                <td style="text-align:center;">' . $record['month_4'] . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {

                $this->db->select('a.item_rm_id, a.remarks, b.number as item_number, b.name as item_name');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.po_no', base64_decode($po_no));
                $this->db->order_by('b.number', 'asc');
                // $this->db->limit(8, ($i * 8));
                $remarks = $this->db->get()->result_array();

                // foreach ($remarks as $remark) {
                //     if ($remark['remarks'] != "") {
                //         $html .= $remark['item_number'] . " &nbsp; (" . $remark['remarks'] . ") <br>";
                //     }
                // }

                $note_content = []; // Menampung remarks yang valid

                foreach ($remarks as $remark) {
                   if (!empty($remark['remarks'])) { //berubah
                        if (($record['category_id'] == 'C01' && $record['family_id'] == 'P27') || ($record['category_id'] == 'C07' && $record['family_id'] == 'P31')) {
                            $note_content[] = $remark['item_rm_id'] . " &nbsp; (" . $remark['remarks'] . ")";
                        }else{
                            $note_content[] = $remark['item_number'] . " &nbsp; (" . $remark['remarks'] . ")";
                        }
                    }
                }

                if (empty($note_content)) {
                    $note_content[] = $record['notes'];
                }

                $html .= '  <tr>
                            <td style="vertical-align: top; text-align:left;" colspan="6" rowspan="8">
                                <b>Note :</b> <br>' . implode('<br>', $note_content) . '
                            </td>
                        </tr>';


                if ($supplier->vat_status == "VAT") {
                    $tax = $supplier->vat;
                } else {
                    $tax = 0;
                }

                
                $html .= '  </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Sub Total</th>
                                <th style="text-align:right;">' . number_format($record['total_sub'], 2) . '</th>
                            </tr>
                             <tr>
                                <th style="text-align:left" colspan="2">Disc % (' . $record['disc_pr'] . '%)</th>
                                <th style="text-align:right;">' . number_format($record['discount_total'], 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">DPP</th>
                                <th style="text-align:right;">' . number_format($record['total_dpp'], 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">VAT (' . $tax . '%)</th>
                                <th style="text-align:right;">' . number_format($record['total_vat'], 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Income Tax % (' . $record['income_tax'] . '%)</th>
                                <th style="text-align:right;">' . number_format($record['income_total'], 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Down Payment</th>
                                <th style="text-align:right;">' . number_format($record['total_dp'], 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($record['total_grand'], 2) . '</th>
                            </tr>
                        </table>';
                        
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
                            <th colspan="3" width="200" style="text-align:center;">PT. BANSHU PLASTIC INDONESIA</th>
                        </tr>
                        <tr>
                            <th width="200" style="text-align:center;">Approved By</th>
                            <th width="200" style="text-align:center;">Checked By</th>
                            <th width="200" style="text-align:center;">Prepared By</th>
                        </tr>
                        <tr>
                            <th style="height:100px;">'. $users_3. '</th>
                            <th style="height:100px;">'. $users_2. '</th>
                            <th style="height:100px;">'. $users_1. '</th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;">' . $user_3->name . '</th>
                            <th style="height:20px; text-align:center;">' . $user_2->name . '</th>
                            <th style="height:20px; text-align:center;">' . $user_1->name . '</th>
                        </tr>
                        <tr>
                            <th width="200" style="text-align:center;">President Director</th>
                            <th width="200" style="text-align:center;">General Manager</th>
                            <th width="200" style="text-align:center;">Assistant Manager</th>
                        </tr>
                    </table>
                        <div style="text-align:left; font-size: 15px; margin-top: 20px; border: none;">
                            <i>Electronic Auto Generating Approval No Need Signature</i>
                        </div>
                </div>
            </div>

            <div style="font-size:12px; margin-top:20px;">
                <tr>
                    <td>Term & Condition</td>
                </tr>
            </div>

            <table style="width:100%; font-size:12px; margin-top:20px;">
                <tr>
                    <td width="20">1.</td>
                    <td>Please sign, stamp & reply email to : mcl@banshuplastic.com.</td>
                </tr>
                <tr>
                    <td width="20"></td>
                    <td>Maximum one day after PO received.</td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Please mention the Purchase Order Number in the </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Shipping & Billing Document.</td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Please make sure delivery date is same with Purchase Order.</td>
                </tr>
            </table>

            <div style="width:50%;">
                <table style="margin-top:20px; font-size:12px;">
                    <tr>
                        <th width="200" style="text-align:center;">Supplier Name</th>
                    </tr>
                    <tr>
                        <th width="200" style="text-align:center; font-size:10px;">Received</th>
                    </tr>
                    <tr>
                        <th style="height:80px;"></th>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">_________________________</th>
                    </tr>
                </table>
            </div>

            </div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_orders_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_from_update = $this->input->get('filter_from_update');
        $filter_to_update   = $this->input->get('filter_to_update');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_suppliers = $this->input->get('filter_suppliers');
        $filter_categories = $this->input->get('filter_categories');
        $filter_status = $this->input->get('filter_status');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, 
            b.number as item_number, 
            b.name as item_name,
            b.uom,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, 
            e.mpq, 
            e.moq');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        if ($filter_from_update != "" or $filter_to_update != "") {
            $this->db->where('a.updated_date >=', $filter_from_update);
            $this->db->where('a.updated_date <=', $filter_to_update);
        }
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('d.id', $filter_suppliers);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.po_date', 'DESC');
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
                                <small>PURCHASE ORDER</small>
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
                    <th width="20">No</th>
                    <th>PO No</th>
                    <th>PO Period</th>
                    <th>PO Name</th>
                    <th>Supplier</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Currency</th>
                    <th>Uom</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            if ($data['currency'] != "IDR") {
                $digits = 4;
            } else {
                $digits = 2;
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['po_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_number'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['mpq'], 2) . '</td>
                        <td>' . number_format($data['moq'], 2) . '</td>
                        <td>' . number_format($data['qty'], 2, ",", ".") . '</td>
                        <td>' . number_format($data['price'], 4, ',', '.') . '</td>
                        <td>' . number_format(($data['qty'] * $data['price']), 2, ",", ".") . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    
}
