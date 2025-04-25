<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_raw_materials extends CI_Controller
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
        $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/sto_raw_materials');
        } else {
            redirect('error_access');
        }
    }

    public function readsDivision()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('divisions', ["name" => $post]);
        echo json_encode($send);
    }

    public function readsNotfg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_categories WHERE name LIKE '%$post%'AND number != 'FG'");
        // $send = $this->crud->reads('item_categories', ["name" => $post]);
        echo json_encode($send);
    }

    public function readsCategory($item_category_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_familys', ["name" => $post],["item_category_id" => $item_category_id]);
        
        echo json_encode($send);
    }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            $this->db->select('a.label_no, a.qty, b.item_rm_id, c.uom');
            $this->db->from('purchase_order_labels a');
            $this->db->join('scan_item_receipts d', 'a.label_no = d.label_no','left');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->where('a.label_no', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select("a.label_no, a.qty, a.item_rm_id, b.uom");
                $this->db->from('new_barcode a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('scan_item_receipts c', 'a.label_no = c.label_no','left');
                $this->db->where('a.label_no', $label_no);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if(!$records){
                    $this->db->select('a.label_no, a.qty, b.item_rm_id, c.uom');
                    $this->db->from('barcode_divides a');
                    $this->db->join('new_barcode b', 'a.reff = b.label_no');
                    $this->db->join('item_rm c', 'b.item_rm_id = c.id');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.label_divided', $label_no);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if (!$records) {
                        $this->db->select('a.label_no, a.qty, c.item_rm_id, d.uom, "barcode_divides_por" as type');
                        $this->db->from('barcode_divides a');
                        $this->db->join('purchase_order_labels b', 'a.reff = b.receipt_id');
                        $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
                        $this->db->join('item_rm d', 'c.item_rm_id = d.id');
                        $this->db->where('a.deleted', 0);
                        $this->db->where('a.label_divided', $label_no);
                        $this->db->group_by('a.label_divided');
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
                    }
                }
            }
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    
    public function datatables($label_no = "")
    {
        $date = date("Y-m-d");
        $period_month = $this->input->get('period_month');
        $period_year = $this->input->get('period_year');
        $filter_items = $this->input->get('filter_items');
        $user = $this->session->username;

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 20;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, c.division, 
        d.name as category, e.name as product_family, f.location, a.created_by, a.created_date');
        $this->db->from('sto_raw_materials a');
        $this->db->join('purchase_order_labels b', 'a.label_no = b.label_no','left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id','left');
        $this->db->join('item_categories d', 'a.category_id = d.id','left');
        $this->db->join('item_familys e', 'a.product_family_id = e.id','left');
        $this->db->join('warehouse_location_items f', 'a.item_rm_id = f.item_rm_id','left');

        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        // $this->db->like('a.created_date', $date);
        $this->db->where('a.created_by', @$user);

        if (!empty($period_month) && !empty($period_year)) {
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m') =", "$period_year-$period_month");
        }

        if ($filter_items != "") {
            $this->db->where('a.item_fg_id', $filter_items);
        }

        $this->db->group_by('a.label_no');
        $this->db->order_by('a.created_date', 'DESC');
        $this->db->order_by('d.name', 'ASC');
        $this->db->order_by('e.name', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        if (!$records) {
            // $new_barcode = $this->crud->read('new_barcode', [], ["label_no" => base64_decode($label_no)]); 

            $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, 
            d.name as category, e.name as product_family, f.location, a.created_by, a.created_date');
            $this->db->from('sto_raw_materials a');
            $this->db->join('new_barcode b', 'a.label_no = b.label_no','left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id','left');
            $this->db->join('item_categories d', 'a.category_id = d.id','left');
            $this->db->join('item_familys e', 'a.product_family_id = e.id','left');
            $this->db->join('warehouse_location_items f', 'a.item_rm_id = f.item_rm_id','left');

            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            // $this->db->like('a.created_date', $date);
            $this->db->where('a.created_by', @$user);
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.created_date', 'DESC');
            $this->db->order_by('d.name', 'ASC');
            $this->db->order_by('e.name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $category = $post['category_id'];
                $prod_fam = $post['product_family_id'];
                $division = $post['division'];

                $item_receipts = $this->crud->read("sto_raw_materials", [], ["label_no" => $post['label_no'],"period_month" => $post['period_month'],"period_year" => $post['period_year']]);

                $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_rm_id']]);

                $item_category = $this->crud->read("item_categories", [], ["id" => $item_rm->item_category_id]);
                $item_familys = $this->crud->read("item_familys", [], ["id" => $item_rm->item_family_id]);

                if (!$item_rm) {
                    echo json_encode(array("title" => "Not Found", "message" => "Item RM not found", "theme" => "error"));
                    return;
                }

                if ($category != $item_rm->item_category_id || $prod_fam != $item_rm->item_family_id || $division != $item_rm->division) {
                    $message = "Category or Product Family mismatch. This Label Division is {$item_rm->division} , Category is {$item_category->name} and Prod Fam is {$item_familys->name}";
                    echo json_encode(array("title" => "Mismatch","message" => $message,"theme" => "error"));
                    return;
                }

                if ($item_receipts) {
                    echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
                    return;
                }

                $send = $this->crud->create('sto_raw_materials', $post);

                if ($send) {
                    echo json_encode(array("title" => "Success", "message" => "Data has been saved", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Failed to save data", "theme" => "error"));
                }

            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    

}
