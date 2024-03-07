<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_postings extends CI_Controller
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
        $this->form_validation->set_rules('item_number', 'Product No', 'required|min_length[1]|max_length[100]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/journal_postings');
            //$this->load->view('maintenance');
        } else {
            redirect('error_access');
        }
    }

    public function readJournalType()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();

        $modul = @base64_decode($get['modul']);
        $journal_date = @base64_decode($get['journal_date']);
        $transaction_from = date("Y-m-01", strtotime($journal_date));
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $transaction_from_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $transaction_to_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));

        if ($modul == "PURCHASE INVOICING") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('purchase_invoices b', 'a.id = b.journal_type_id');
            $this->db->join('journal_postings c', 'b.number = c.document_no', 'left');
            $this->db->where('b.trans_date >=', $transaction_from);
            $this->db->where('b.trans_date <=', $transaction_to);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            //$this->db->where('b.status', 1);
            $this->db->group_by('b.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "SALES INVOICING") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('sales_invoices b', 'a.id = b.journal_type_id');
            $this->db->join('journal_postings c', 'b.number = c.document_no', 'left');
            $this->db->where('b.trans_date >=', $transaction_from);
            $this->db->where('b.trans_date <=', $transaction_to);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            //$this->db->where('b.status', 1);
            $this->db->group_by('b.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AP PAYMENT") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('ap_payments b', 'a.id = b.journal_type_id');
            $this->db->join('journal_postings c', 'b.payment_no = c.document_no', 'left');
            $this->db->where('b.payment_date >=', $transaction_from);
            $this->db->where('b.payment_date <=', $transaction_to);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            $this->db->group_by('b.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AR RECEIPT") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('ar_receipts b', 'a.id = b.journal_type_id');
            $this->db->join('journal_postings c', 'b.receipt_no = c.document_no', 'left');
            $this->db->where('b.receipt_date >=', $transaction_from);
            $this->db->where('b.receipt_date <=', $transaction_to);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            $this->db->group_by('b.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "ASSET") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('asset_categories d', 'a.id = d.journal_type_id');
            $this->db->join('asset_journals b', 'd.number = b.asset_category_number');
            $this->db->join('journal_postings c', "b.asset_no = c.document_no and b.periode = DATE_FORMAT(c.journal_date, '%Y-%m')", 'left');
            $this->db->join('asset_fixeds e', 'b.asset_no = e.number');
            $this->db->where('b.periode >=', $transaction_from_ex);
            $this->db->where('b.periode <=', $transaction_to_ex);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            $this->db->group_by('d.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "CURRENCY REVALUATION") {
            $this->db->select('a.*');
            $this->db->from('journal_types a');
            $this->db->join('journal_revaluations b', 'a.id = b.journal_type_id');
            $this->db->join('journal_postings c', 'b.number = c.document_no and b.document_no = c.invoice_no', 'left');
            $this->db->where('b.period >=', $transaction_from_ex);
            $this->db->where('b.period <=', $transaction_to_ex);
            $this->db->where('c.document_no', NULL);
            $this->db->like('a.name', $post);
            $this->db->group_by('b.journal_type_id');
            $this->db->order_by('a.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "SUPPLY MATERIAL") {
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J023');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "FINISH GOOD IN") {
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J042');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "FINISH GOOD OUT") {
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J043');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "DIRECT LABOUR"){
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J046');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "FOH"){
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J047');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "CLOSING JOURNAL"){
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->where('number', 'J048');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } else {
            $this->db->select('*');
            $this->db->from('journal_types');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function readCompany()
    {
        $get = $this->input->get();
        $modul = base64_decode($get['modul']);
        $journal_date = @base64_decode($get['journal_date']);
        $transaction_from = date("Y-m-01", strtotime($journal_date));
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $transaction_from_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $transaction_to_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $journal_type = base64_decode($get['journal_type']);

        if ($modul == "PURCHASE INVOICING") {
            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('purchase_invoices a');
            $this->db->join('journal_postings b', 'a.number = b.document_no', 'left');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('a.trans_date >=', $transaction_from);
            $this->db->where('a.trans_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 1);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "SALES INVOICING") {
            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('sales_invoices a');
            $this->db->join('journal_postings b', 'a.number = b.document_no', 'left');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.trans_date >=', $transaction_from);
            $this->db->where('a.trans_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 1);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AP PAYMENT") {
            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('ap_payments a');
            $this->db->join('journal_postings b', 'a.payment_no = b.document_no', 'left');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.payment_date >=', $transaction_from);
            $this->db->where('a.payment_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            // $this->db->where('a.status', 0);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AR RECEIPT") {
            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('ar_receipts a');
            $this->db->join('journal_postings b', 'a.receipt_no = b.document_no', 'left');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            // $this->db->where('a.status', 0);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "ASSET") {
            $this->db->select('e.supplier_name as company_id, e.supplier_name as company_name');
            $this->db->from('journal_types a');
            $this->db->join('asset_categories d', 'a.id = d.journal_type_id');
            $this->db->join('asset_journals b', 'd.number = b.asset_category_number');
            $this->db->join('journal_postings c', "b.asset_no = c.document_no and b.periode = DATE_FORMAT(c.journal_date, '%Y-%m')", 'left');
            $this->db->join('asset_fixeds e', 'b.asset_no = e.number');
            $this->db->where('b.periode >=', $transaction_from_ex);
            $this->db->where('b.periode <=', $transaction_to_ex);
            $this->db->where('a.id', $journal_type);
            $this->db->where('c.document_no', NULL);
            $this->db->group_by('e.supplier_name');
            $this->db->order_by('e.supplier_name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "CURRENCY REVALUATION") {
            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('journal_revaluations a');
            $this->db->join('journal_postings b', 'a.number = b.document_no and a.document_no = b.invoice_no', 'left');
            $this->db->join('purchase_invoices d', 'a.document_no = d.number');
            $this->db->join('suppliers c', 'd.supplier_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.modul', "PURCHASE INVOICING");
            $this->db->where('a.period >=', $transaction_from_ex);
            $this->db->where('a.period <=', $transaction_to_ex);
            $this->db->where('a.journal_type_id', $journal_type);
            // $this->db->where('a.status', 0);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $suppliers = $this->db->get()->result_array();

            $this->db->select('c.id as company_id, c.name as company_name');
            $this->db->from('journal_revaluations a');
            $this->db->join('journal_postings b', 'a.number = b.document_no and a.document_no = b.invoice_no', 'left');
            $this->db->join('sales_invoices d', 'a.document_no = d.number');
            $this->db->join('customers c', 'd.customer_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.modul', "SALES INVOICING");
            $this->db->where('a.period >=', $transaction_from_ex);
            $this->db->where('a.period <=', $transaction_to_ex);
            $this->db->where('a.journal_type_id', $journal_type);
            // $this->db->where('a.status', 0);
            $this->db->group_by('c.id');
            $this->db->order_by('c.name', 'asc');
            $customers = $this->db->get()->result_array();

            $data = array();
            if(count($suppliers) > 0){
                foreach ($suppliers as $supplier) {
                    $data[] = array(
                        "company_id" => $supplier['company_id'],
                        "company_name" => $supplier['company_name'],
                    );
                }
            }

            if(count($customers) > 0){
                foreach ($customers as $customer) {
                    $data[] = array(
                        "company_id" => $customer['company_id'],
                        "company_name" => $customer['company_name'],
                    );
                }
            }

            echo json_encode($data);
        } elseif ($modul == "SUPPLY MATERIAL") {
            $data[] = array(
                "company_id" => "",
                "company_name" => "Choose All",
            );
            echo json_encode($data);
        } elseif ($modul == "FINISH GOOD IN") {
            $data[] = array(
                "company_id" => "",
                "company_name" => "Choose All",
            );
            echo json_encode($data);
        } elseif ($modul == "FINISH GOOD OUT") {
            $data[] = array(
                "company_id" => "",
                "company_name" => "Choose All",
            );
            echo json_encode($data);
        } elseif ($modul == "CLOSING JOURNAL") {
            $data[] = array(
                "company_id" => "",
                "company_name" => "Choose All",
            );
            echo json_encode($data);
        } else {
            $this->db->select('id as company_id, name as company_name');
            $this->db->from('suppliers');
            $this->db->like('name', $post);
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } 
    }

    public function readModul()
    {
        $get = $this->input->get();
        $modul = base64_decode($get['modul']);
        $journal_date = @base64_decode($get['journal_date']);
        $transaction_from = date("Y-m-01", strtotime($journal_date));
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $transaction_from_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $transaction_to_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $journal_type = base64_decode($get['journal_type']);
        $company_id = base64_decode($get['company_id']);

        if ($modul == "PURCHASE INVOICING") {
            $this->db->select('a.number');
            $this->db->from('purchase_invoices a');
            $this->db->join('journal_postings b', 'a.number = b.document_no', 'left');
            $this->db->where('a.trans_date >=', $transaction_from);
            $this->db->where('a.trans_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 1);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "SALES INVOICING") {
            $this->db->select('a.number');
            $this->db->from('sales_invoices a');
            $this->db->join('journal_postings b', 'a.number = b.document_no', 'left');
            $this->db->where('a.trans_date >=', $transaction_from);
            $this->db->where('a.trans_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('a.customer_id', $company_id);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 1);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AP PAYMENT") {
            $this->db->select('a.payment_no as number');
            $this->db->from('ap_payments a');
            $this->db->join('journal_postings b', 'a.payment_no = b.document_no', 'left');
            $this->db->where('a.payment_date >=', $transaction_from);
            $this->db->where('a.payment_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 0);
            $this->db->group_by('a.payment_no');
            $this->db->order_by('a.payment_no', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "AR RECEIPT") {
            $this->db->select('a.receipt_no as number');
            $this->db->from('ar_receipts a');
            $this->db->join('journal_postings b', 'a.receipt_no = b.document_no', 'left');
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('a.customer_id', $company_id);
            $this->db->where('b.document_no', NULL);
            // $this->db->where('a.status', 0);
            $this->db->group_by('a.receipt_no');
            $this->db->order_by('a.receipt_no', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "ASSET") {
            $this->db->select('a.asset_no as number');
            $this->db->from('asset_journals a');
            $this->db->join('journal_postings b', "a.asset_no = b.document_no and a.periode = DATE_FORMAT(b.journal_date, '%Y-%m')", 'left');
            $this->db->join('asset_categories d', 'd.number = a.asset_category_number');
            $this->db->join('asset_fixeds e', 'a.asset_no = e.number');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.periode >=', $transaction_from_ex);
            $this->db->where('a.periode <=', $transaction_to_ex);
            $this->db->where('d.journal_type_id', $journal_type);
            $this->db->where('e.supplier_name', $company_id);
            // $this->db->where('a.status', 0);
            $this->db->group_by('a.asset_no');
            $this->db->order_by('a.asset_no', 'asc');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        } elseif ($modul == "CURRENCY REVALUATION") {
            $this->db->select('a.number');
            $this->db->from('journal_revaluations a');
            $this->db->join('journal_postings b', 'a.number = b.document_no and a.document_no = b.invoice_no', 'left');
            $this->db->join('purchase_invoices d', 'a.document_no = d.number');
            $this->db->join('suppliers c', 'd.supplier_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.modul', "PURCHASE INVOICING");
            $this->db->where('a.period >=', $transaction_from_ex);
            $this->db->where('a.period <=', $transaction_to_ex);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('c.id', $company_id);
            // $this->db->where('a.status', 0);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'asc');
            $suppliers = $this->db->get()->result_array();

            $this->db->select('a.number');
            $this->db->from('journal_revaluations a');
            $this->db->join('journal_postings b', 'a.number = b.document_no and a.document_no = b.invoice_no', 'left');
            $this->db->join('sales_invoices d', 'a.document_no = d.number');
            $this->db->join('customers c', 'd.customer_id = c.id');
            $this->db->where('b.document_no', NULL);
            $this->db->where('a.modul', "SALES INVOICING");
            $this->db->where('a.period >=', $transaction_from_ex);
            $this->db->where('a.period <=', $transaction_to_ex);
            $this->db->where('a.journal_type_id', $journal_type);
            $this->db->where('c.id', $company_id);
            // $this->db->where('a.status', 0);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'asc');
            $customers = $this->db->get()->result_array();

            $data = array();
            foreach ($suppliers as $supplier) {
                $data[] = array(
                    "number" => $supplier['number'],
                );
            }

            foreach ($customers as $customer) {
                $data[] = array(
                    "number" => $customer['number'],
                );
            }

            echo json_encode($data);
        }
    }

    public function number($journal_date)
    {
        $datenow    = "GL" . date("ym", strtotime(base64_decode($journal_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . $autoID;
    }

    public function datatablesTemp()
    {
        $get = $this->input->get();
        $journal_date = @base64_decode($get['journal_date']);
        $transaction_from = date("Y-m-01", strtotime($journal_date));
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $transaction_from_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $transaction_to_ex = date("Y-m", strtotime(@base64_decode($get['journal_date'])));
        $modul = base64_decode($get['modul']);
        $company_id = base64_decode($get['company_id']);
        $document_no = explode(",", base64_decode($get['document_no']));

        $start = strtotime($transaction_from);
        $finish = strtotime($transaction_to);

        if ($modul == "PURCHASE INVOICING") {
            $this->db->select('a.number, b.trans_date, b.invoice_no, c.name as supplier_name, b.currency, b.item_no, b.item_name, a.account_number, a.account_name, a.debit, a.credit, a.flag');
            $this->db->from('purchase_invoice_journals a');
            $this->db->join("(SELECT * FROM purchase_invoices GROUP BY number) b", "b.number = a.number");
            $this->db->join("suppliers c", "b.supplier_id = c.id");
            $this->db->where_in('a.number', $document_no);
            $this->db->order_by('a.number', 'asc');
            $this->db->order_by('a.flag', 'asc');
            $journals = $this->db->get()->result_array();

            $trans_date = "";
            $supplier_name = "";
            $currency = "";

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['number'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $this->db->select('a.trans_date, a.invoice_no, b.name as supplier_name, a.po_no, a.item_no, a.item_name, a.currency, a.account_number, c.account_name, a.account_type, a.total');
                $this->db->from('purchase_invoices a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('account_coa c', 'a.account_number = c.account_number');
                $this->db->where('a.number', $number);
                $this->db->where('a.account_number', $account_number);
                $this->db->order_by('a.trans_date', 'asc');
                $purchase_invoices = $this->db->get()->result_array();

                if ($debit > 0 || $credit > 0) {
                    if (count($purchase_invoices) > 0) {

                        $bal_original_debit = 0;
                        $bal_original_credit = 0;
                        $bal_local_debit = 0;
                        $bal_local_credit = 0;

                        foreach ($purchase_invoices as $purchase_invoice) {
                            $currency = $purchase_invoice['currency'];
                            $supplier_name = $purchase_invoice['supplier_name'];
                            $trans_date = $purchase_invoice['trans_date'];

                            $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($purchase_invoice['trans_date'])));
                            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $currency, "currency_to" => "IDR"]);

                            if ($currency != "IDR") {
                                if ($purchase_invoice['account_type'] == "DEBIT") {
                                    $original_debit = round($purchase_invoice['total'], 2);
                                    $original_credit = 0;
                                } else {
                                    $original_debit = 0;
                                    $original_credit = round($purchase_invoice['total'], 2);
                                }

                                if ($purchase_invoice['account_type'] == "DEBIT") {
                                    $local_debit = round($purchase_invoice['total'] * @$exchange->middle, 2);
                                    $local_credit = 0;
                                } else {
                                    $local_debit = 0;
                                    $local_credit = round($purchase_invoice['total'] * @$exchange->middle, 2);
                                }

                                $rates = @$exchange->middle;
                            } else {
                                if ($purchase_invoice['account_type'] == "DEBIT") {
                                    $original_debit = $purchase_invoice['total'];
                                    $original_credit = 0;
                                    $local_debit = $purchase_invoice['total'];
                                    $local_credit = 0;
                                } else {
                                    $original_debit = 0;
                                    $original_credit = $purchase_invoice['total'];
                                    $local_debit = 0;
                                    $local_credit = $purchase_invoice['total'];
                                }

                                $rates = 1;
                            }

                            $data[] = array(
                                "trans_date" => $purchase_invoice['trans_date'],
                                "document_no" => $number,
                                "invoice_no" => $purchase_invoice['invoice_no'],
                                "company_name" => $supplier_name,
                                "modul" => $modul,
                                "account_number" => $purchase_invoice['account_number'],
                                "account_name" => $purchase_invoice['account_name'],
                                "description" => $supplier_name . " | " . $purchase_invoice['po_no'] . " | " . $purchase_invoice['invoice_no'] . " | " . $purchase_invoice['item_no'] . " | " . $purchase_invoice['item_name'],
                                "currency" => $purchase_invoice['currency'],
                                "original_debit" => $original_debit,
                                "original_credit" => $original_credit,
                                "rates" => $rates,
                                "local_debit" => $local_debit,
                                "local_credit" => $local_credit,
                            );

                            $bal_original_debit += $original_debit;
                            $bal_original_credit += $original_credit;
                            $bal_local_debit += $local_debit;
                            $bal_local_credit += $local_credit;

                            $grand_original_debit += $original_debit;
                            $grand_original_credit += $original_credit;
                            $grand_local_debit += $local_debit;
                            $grand_local_credit += $local_credit;
                        }
                    } else {
                        $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($trans_date)));
                        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $currency, "currency_to" => "IDR"]);

                        if ($currency != "IDR") {
                            $original_debit = $debit;
                            $original_credit = $credit;
                            $local_debit = round($debit * @$exchange->middle, 2);
                            $local_credit = round($credit * @$exchange->middle, 2);

                            $rates = @$exchange->middle;
                        } else {
                            $original_debit = $debit;
                            $original_credit = $credit;
                            $local_debit = $debit;
                            $local_credit = $credit;

                            $rates = 1;
                        }

                        $data[] = array(
                            "trans_date" => $journal['trans_date'],
                            "document_no" => $number,
                            "invoice_no" => $journal['invoice_no'],
                            "company_name" => $journal['supplier_name'],
                            "modul" => $modul,
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "description" => $journal['supplier_name'] . " | " . $number . " | " . $journal['invoice_no'] . " | " . $journal['item_no'] . " | " . $journal['item_name'],
                            "currency" => $journal['currency'],
                            "original_debit" => $original_debit,
                            "original_credit" => $original_credit,
                            "rates" => $rates,
                            "local_debit" => $local_debit,
                            "local_credit" => $local_credit,
                        );

                        $grand_original_debit += $original_debit;
                        $grand_original_credit += $original_credit;
                        $grand_local_debit += $local_debit;
                        $grand_local_credit += $local_credit;
                    }
                }
            }

            $footer[] = array(
                "original_debit" => round($grand_original_debit, 2),
                "original_credit" => round($grand_original_credit, 2),
                "local_debit" => round($grand_local_debit, 2),
                "local_credit" => round($grand_local_debit, 2),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "SALES INVOICING") {
            $this->db->select('a.number, b.trans_date, b.so_number, b.customer_po, c.name as customer_name, b.currency, b.item_no, b.item_name, a.account_number, a.account_name, a.debit, a.credit, a.flag');
            $this->db->from('sales_invoice_journals a');
            $this->db->join("(SELECT * FROM sales_invoices GROUP BY number) b", "b.number = a.number");
            $this->db->join("customers c", "b.customer_id = c.id");
            $this->db->where_in('a.number', $document_no);
            $this->db->order_by('a.number', 'asc');
            $this->db->order_by('a.flag', 'asc');
            $journals = $this->db->get()->result_array();

            $trans_date = "";
            $customer_name = "";
            $currency = "";

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['number'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $this->db->select('a.trans_date, a.customer_po, b.name as customer_name, a.item_no, a.item_name, a.currency, a.account_number, c.account_name, a.account_type, a.total');
                $this->db->from('sales_invoices a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('account_coa c', 'a.account_number = c.account_number');
                $this->db->where('a.number', $number);
                $this->db->where('a.account_number', $account_number);
                $this->db->order_by('a.trans_date', 'asc');
                $sales_invoices = $this->db->get()->result_array();

                if ($debit > 0 || $credit > 0) {
                    if (count($sales_invoices) > 0) {

                        foreach ($sales_invoices as $sales_invoice) {
                            $currency = $sales_invoice['currency'];
                            $customer_name = $sales_invoice['customer_name'];
                            $trans_date = $sales_invoice['trans_date'];

                            $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($sales_invoice['trans_date'])));
                            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $currency, "currency_to" => "IDR"]);

                            if ($currency != "IDR") {
                                if ($sales_invoice['account_type'] == "DEBIT") {
                                    $original_debit = $sales_invoice['total'];
                                    $original_credit = 0;
                                } else {
                                    $original_debit = 0;
                                    $original_credit = $sales_invoice['total'];
                                }

                                if ($sales_invoice['account_type'] == "DEBIT") {
                                    $local_debit = round($sales_invoice['total'] * @$exchange->middle, 2);
                                    $local_credit = 0;
                                } else {
                                    $local_debit = 0;
                                    $local_credit = round($sales_invoice['total'] * @$exchange->middle, 2);
                                }

                                $rates = @$exchange->middle;
                            } else {
                                if ($sales_invoice['account_type'] == "DEBIT") {
                                    $original_debit = $sales_invoice['total'];
                                    $original_credit = 0;
                                    $local_debit = $sales_invoice['total'];
                                    $local_credit = 0;
                                } else {
                                    $original_debit = 0;
                                    $original_credit = $sales_invoice['total'];
                                    $local_debit = 0;
                                    $local_credit = $sales_invoice['total'];
                                }

                                $rates = 1;
                            }

                            $data[] = array(
                                "trans_date" => $sales_invoice['trans_date'],
                                "document_no" => $number,
                                "invoice_no" => $sales_invoice['customer_po'],
                                "company_name" => $customer_name,
                                "modul" => $modul,
                                "account_number" => $sales_invoice['account_number'],
                                "account_name" => $sales_invoice['account_name'],
                                "description" => $customer_name . " | " . $number . " | " . $sales_invoice['customer_po'] . " | " . $sales_invoice['item_no'] . " | " . $sales_invoice['item_name'],
                                "currency" => $sales_invoice['currency'],
                                "original_debit" => $original_debit,
                                "original_credit" => $original_credit,
                                "rates" => $rates,
                                "local_debit" => $local_debit,
                                "local_credit" => $local_credit,
                            );

                            $grand_original_debit += $original_debit;
                            $grand_original_credit += $original_credit;
                            $grand_local_debit += $local_debit;
                            $grand_local_credit += $local_credit;
                        }
                    } else {
                        $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($journal['trans_date'])));
                        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $journal['currency'], "currency_to" => "IDR"]);

                        if ($journal['currency'] != "IDR") {
                            $original_debit = $debit;
                            $original_credit = $credit;
                            $local_debit = round($debit * @$exchange->middle, 2);
                            $local_credit = round($credit * @$exchange->middle, 2);

                            $rates = @$exchange->middle;
                        } else {
                            $original_debit = $debit;
                            $original_credit = $credit;
                            $local_debit = $debit;
                            $local_credit = $credit;

                            $rates = 1;
                        }

                        $data[] = array(
                            "trans_date" => $journal['trans_date'],
                            "document_no" => $number,
                            "invoice_no" => $journal['customer_po'],
                            "company_name" => $journal['customer_name'],
                            "modul" => $modul,
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "description" => $journal['customer_name'] . " | " . $journal['so_number'] . " | " . $journal['customer_po'] . " | " . $journal['item_no'] . " | " . $journal['item_name'],
                            "currency" => $journal['currency'],
                            "original_debit" => $original_debit,
                            "original_credit" => $original_credit,
                            "rates" => $rates,
                            "local_debit" => $local_debit,
                            "local_credit" => $local_credit,
                        );

                        $grand_original_debit += $original_debit;
                        $grand_original_credit += $original_credit;
                        $grand_local_debit += $local_debit;
                        $grand_local_credit += $local_credit;
                    }
                }
            }

            $footer[] = array(
                "original_debit" => $grand_original_debit,
                "original_credit" => $grand_original_credit,
                "local_debit" => $grand_local_debit,
                "local_credit" => $grand_local_credit,
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "AP PAYMENT") {
            $this->db->select('a.payment_no, b.payment_date, b.purchase_invoice, b.supplier_invoice, c.name as supplier_name, b.currency, a.description, a.account_number, a.account_name, a.debit, a.credit, a.flag, a.local_debit, a.local_credit');
            $this->db->from('ap_payment_journals a');
            $this->db->join("(SELECT * FROM ap_payments GROUP BY payment_no) b", "b.payment_no = a.payment_no");
            $this->db->join("suppliers c", "b.supplier_id = c.id");
            $this->db->where_in('a.payment_no', $document_no);
            $this->db->order_by('a.payment_no', 'asc');
            $this->db->order_by('a.flag', 'asc');
            $journals = $this->db->get()->result_array();

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['payment_no'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($journal['payment_date'])));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $journal['currency'], "currency_to" => "IDR"]);

                if ($journal['currency'] != "IDR") {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = $journal['local_debit'];
                    $local_credit = $journal['local_credit'];

                    $rates = @$exchange->middle;
                } else {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = $journal['local_debit'];
                    $local_credit = $journal['local_credit'];

                    $rates = 1;
                }

                $data[] = array(
                    "trans_date" => $journal['payment_date'],
                    "document_no" => $number,
                    "invoice_no" => $journal['purchase_invoice'],
                    "company_name" => $journal['supplier_name'],
                    "modul" => $modul,
                    "account_number" => $account_number,
                    "account_name" => $account_name,
                    "description" => $journal['supplier_name'] . " | " . $number . " | " . $journal['purchase_invoice'] . " | " . $journal['supplier_invoice'],
                    "currency" => $journal['currency'],
                    "original_debit" => $original_debit,
                    "original_credit" => $original_credit,
                    "rates" => $rates,
                    "local_debit" => $local_debit,
                    "local_credit" => $local_credit,
                );

                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
            }

            $footer[] = array(
                "original_debit" => $grand_original_debit,
                "original_credit" => $grand_original_credit,
                "local_debit" => $grand_local_debit,
                "local_credit" => $grand_local_credit,
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "AR RECEIPT") {
            $this->db->select('a.receipt_no, b.receipt_date, b.sales_invoice, b.description, c.name as customer_name, b.currency, a.description, a.account_number, a.account_name, a.debit, a.credit, a.flag');
            $this->db->from('ar_receipt_journals a');
            $this->db->join("(SELECT * FROM ar_receipts GROUP BY receipt_no) b", "b.receipt_no = a.receipt_no");
            $this->db->join("customers c", "b.customer_id = c.id");
            $this->db->where_in('a.receipt_no', $document_no);
            $this->db->order_by('a.receipt_no', 'asc');
            $this->db->order_by('a.flag', 'asc');
            $journals = $this->db->get()->result_array();

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['receipt_no'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($journal['receipt_date'])));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $journal['currency'], "currency_to" => "IDR"]);

                if ($journal['currency'] != "IDR") {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = round($debit * @$exchange->middle, 2);
                    $local_credit = round($credit * @$exchange->middle, 2);

                    $rates = @$exchange->middle;
                } else {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = $debit;
                    $local_credit = $credit;

                    $rates = 1;
                }

                $data[] = array(
                    "trans_date" => $journal['receipt_date'],
                    "document_no" => $number,
                    "invoice_no" => $journal['sales_invoice'],
                    "company_name" => $journal['customer_name'],
                    "modul" => $modul,
                    "account_number" => $account_number,
                    "account_name" => $account_name,
                    "description" => $journal['customer_name'] . " | " . $number . " | " . $journal['sales_invoice'] . " | " . $journal['description'],
                    "currency" => $journal['currency'],
                    "original_debit" => $original_debit,
                    "original_credit" => $original_credit,
                    "rates" => $rates,
                    "local_debit" => $local_debit,
                    "local_credit" => $local_credit,
                );

                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
            }

            $footer[] = array(
                "original_debit" => $grand_original_debit,
                "original_credit" => $grand_original_credit,
                "local_debit" => $grand_local_debit,
                "local_credit" => $grand_local_credit,
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "ASSET") {
            $this->db->select('a.asset_no, a.trans_date, b.purchase_invoice_number, b.supplier_name, b.currency, b.name, a.account_number, a.account_name, a.debit, a.credit');
            $this->db->from('asset_journals a');
            $this->db->join("asset_fixeds b", "a.asset_no = b.number");
            $this->db->where("a.periode BETWEEN '$transaction_from_ex' and '$transaction_to_ex'");
            $this->db->where_in('a.asset_no', $document_no);
            $this->db->group_by(['asset_no', 'account_number']);
            $this->db->order_by('a.asset_no', 'asc');
            $journals = $this->db->get()->result_array();

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['asset_no'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $transmonth = date('Y-m-01', strtotime('-1 month', strtotime($journal['trans_date'])));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $journal['currency'], "currency_to" => "IDR"]);

                if ($journal['currency'] != "IDR") {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = round($debit * @$exchange->middle, 2);
                    $local_credit = round($credit * @$exchange->middle, 2);

                    $rates = @$exchange->middle;
                } else {
                    $original_debit = $debit;
                    $original_credit = $credit;
                    $local_debit = $debit;
                    $local_credit = $credit;

                    $rates = 1;
                }

                $data[] = array(
                    "trans_date" => $journal['trans_date'],
                    "document_no" => $number,
                    "invoice_no" => $journal['purchase_invoice_number'],
                    "company_name" => $journal['supplier_name'],
                    "modul" => $modul,
                    "account_number" => $account_number,
                    "account_name" => $account_name,
                    "description" => $journal['supplier_name'] . " | " . $number . " | " . $journal['purchase_invoice_number'] . " | " . $journal['name'],
                    "currency" => $journal['currency'],
                    "original_debit" => $original_debit,
                    "original_credit" => $original_credit,
                    "rates" => $rates,
                    "local_debit" => $local_debit,
                    "local_credit" => $local_credit,
                );

                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
            }

            $footer[] = array(
                "original_debit" => $grand_original_debit,
                "original_credit" => $grand_original_credit,
                "local_debit" => $grand_local_debit,
                "local_credit" => $grand_local_credit,
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "CURRENCY REVALUATION") {
            $this->db->select('a.number, a.trans_date, a.document_no, a.account_number, a.account_name, a.debit, a.credit,
                (CASE WHEN d.name IS NULL THEN e.name ELSE d.name END) as company_name');
            $this->db->from('journal_revaluations a');
            $this->db->join("purchase_invoices b", "a.document_no = b.number", "left");
            $this->db->join("sales_invoices c", "a.document_no = c.number", "left");
            $this->db->join("suppliers d", "b.supplier_id = d.id", "left");
            $this->db->join("customers e", "c.customer_id = e.id", "left");
            $this->db->where_in('a.number', $document_no);
            $this->db->group_by(['a.number', 'a.account_number', 'a.document_no']);
            $this->db->order_by('a.number', 'asc');
            $journals = $this->db->get()->result_array();

            $original_debit = 0;
            $original_credit = 0;
            $local_debit = 0;
            $local_credit = 0;

            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;

            $data = array();
            foreach ($journals as $journal) {
                $number = $journal['number'];
                $account_number = $journal['account_number'];
                $account_name = $journal['account_name'];
                $debit = $journal['debit'];
                $credit = $journal['credit'];

                $original_debit = $debit;
                $original_credit = $credit;
                $local_debit = $debit;
                $local_credit = $credit;

                $rates = 1;
                $data[] = array(
                    "trans_date" => $journal['trans_date'],
                    "document_no" => $number,
                    "invoice_no" => $journal['document_no'],
                    "company_name" => $journal['company_name'],
                    "modul" => $modul,
                    "account_number" => $account_number,
                    "account_name" => $account_name,
                    "description" => $journal['company_name'] . " | " . $number . " | " . $journal['document_no'],
                    "currency" => "IDR",
                    "original_debit" => $original_debit,
                    "original_credit" => $original_credit,
                    "rates" => $rates,
                    "local_debit" => $local_debit,
                    "local_credit" => $local_credit,
                );

                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
            }

            $footer[] = array(
                "original_debit" => $grand_original_debit,
                "original_credit" => $grand_original_credit,
                "local_debit" => $grand_local_debit,
                "local_credit" => $grand_local_credit,
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "SUPPLY MATERIAL") {
            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                a.id, a.number, a.name, c.name as uom, b.name as prodfam, b.id as prodfam_id, COALESCE(d.qty, 0) as qty, COALESCE(d.amount, 0) as amount
            FROM items a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN uom c ON a.uom_id = c.id
            LEFT JOIN (SELECT item_id, SUM(qty) as qty, SUM(amount) as amount FROM inventory_rm WHERE trans_date between '$transaction_from' and '$transaction_to' and trans_type = 'ISSUED' GROUP BY item_id) d ON a.id = d.item_id
            WHERE b.number = '002'
            GROUP BY a.id
            ORDER BY a.number");

            $data = array();
            $total_amount_out = 0;
            foreach ($itemReceipts as $itemReceipt) {
                $total_amount_out += abs($itemReceipt->amount);
            }

            $this->db->select('a.id, a.account_number, b.account_name');
            $this->db->from('journal_types a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number');
            $this->db->where('number', 'J023');
            $account = $this->db->get()->row();

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "INVENTORY RM (OUT) - DEBIT",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512001",
                "account_name" => "COST OF GOOD MANUFACTURING - MATEIRAL",
                "description" => "DEBIT TOTAL SUPPLY MATERIAL",
                "currency" => "IDR",
                "original_debit" => round($total_amount_out, 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round($total_amount_out, 2),
                "local_credit" => 0,
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "INVENTORY RM (OUT) - CREDIT",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => @$account->account_number,
                "account_name" => @$account->account_name,
                "description" => "CREDIT TOTAL SUPPLY MATERIAL",
                "currency" => "IDR",
                "original_debit" => 0,
                "original_credit" => round($total_amount_out, 2),
                "rates" => 1,
                "local_debit" => 0,
                "local_credit" => round($total_amount_out, 2),
            );

            $footer[] = array(
                "original_debit" => round($total_amount_out, 2),
                "original_credit" => round($total_amount_out, 2),
                "local_debit" => round($total_amount_out, 2),
                "local_credit" => round($total_amount_out, 2),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "FINISH GOOD IN") {
            $records = $this->crud->query("SELECT item_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_type = 'SCAN FG' and trans_date between '$transaction_from' and '$transaction_to' GROUP BY item_id");

            $this->db->select('a.id, a.account_number, b.account_name');
            $this->db->from('journal_types a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number');
            $this->db->where('number', 'J042');
            $account = $this->db->get()->row();

            $total_amount_fg_in = 0;
            $total_inventory_rm = 0;
            $total_final_labor = 0;
            $total_final_foh = 0;
            foreach ($records as $record) {
                $total_amount_fg_in += ($record->amount);
                $total_inventory_rm += ($record->direct_material);
                $total_final_labor += ($record->direct_labor);
                $total_final_foh += ($record->direct_foh);
            }

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "INVENTORY FG (IN) - FG IN",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => $account->account_number,
                "account_name" => $account->account_name,
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round($total_amount_fg_in, 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round($total_amount_fg_in, 2),
                "local_credit" => 0,
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "INVENTORY RM (OUT) - FG IN",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512001",
                "account_name" => "COST OF GOOD MANUFACTURING - MATERIAL",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => 0,
                "original_credit" => abs(round($total_inventory_rm, 2)),
                "rates" => 1,
                "local_debit" => 0,
                "local_credit" => abs(round($total_inventory_rm, 2)),
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "DIRECT LABOR TOTAL - FG IN",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512002",
                "account_name" => "COST OF GOOD MANUFACTURING - DIRECT LABOR",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => 0,
                "original_credit" => abs(round($total_final_labor, 2)),
                "rates" => 1,
                "local_debit" => 0,
                "local_credit" => abs(round($total_final_labor, 2)),
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "DIRECT LABOR FOH - FG IN",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512003",
                "account_name" => "COST OF GOOD MANUFACTURING - FACTORY",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => 0,
                "original_credit" => abs(round($total_final_foh, 2)),
                "rates" => 1,
                "local_debit" => 0,
                "local_credit" => abs(round($total_final_foh, 2)),
            );

            $footer[] = array(
                "original_debit" => round($total_amount_fg_in, 2),
                "original_credit" => abs(round($total_inventory_rm + $total_final_labor + $total_final_foh, 2)),
                "local_debit" => round($total_amount_fg_in, 2),
                "local_credit" => abs(round($total_inventory_rm + $total_final_labor + $total_final_foh, 2)),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "FINISH GOOD OUT") {
            $records = $this->crud->query("SELECT item_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_fg WHERE trans_type = 'DELIVERY NOTE' and type_sales = 'SALES' and trans_date between '$transaction_from' and '$transaction_to' GROUP BY item_id");

            $total_amount_fg_in = 0;
            $total_inventory_rm = 0;
            $total_final_labor = 0;
            $total_final_foh = 0;
            foreach ($records as $record) {
                $total_amount_fg_in += ($record->amount);
                $total_inventory_rm += ($record->direct_material);
                $total_final_labor += ($record->direct_labor);
                $total_final_foh += ($record->direct_foh);
            }

            $this->db->select('a.id, a.account_number, b.account_name');
            $this->db->from('journal_types a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number');
            $this->db->where('number', 'J043');
            $account = $this->db->get()->row();

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "DIRECT MATERIAL (OUT)",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4511001",
                "account_name" => "COST OF GOOD SOLD - LOCAL - MATERIAL",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round(abs($total_inventory_rm), 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round(abs($total_inventory_rm), 2),
                "local_credit" => 0,
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "DIRECT LABOR TOTAL - FG OUT",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4511002",
                "account_name" => "COST OF GOOD SOLD - LOCAL - DIRECTLABOR",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round(abs($total_final_labor), 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round(abs($total_final_labor), 2),
                "local_credit" => 0,
            );

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "DIRECT LABOR FOH - FG OUT",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4511003",
                "account_name" => "COST OF GOOD SOLD - LOCAL - OVERHEAD",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round(abs($total_final_foh), 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round(abs($total_final_foh), 2),
                "local_credit" => 0,
            );

            $this->db->select('a.id, a.account_number, b.account_name');
            $this->db->from('journal_types a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number');
            $this->db->where('number', 'J042');
            $account = $this->db->get()->row();

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "INVENTORY FG (OUT) - FG OUT",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => $account->account_number,
                "account_name" => $account->account_name,
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => 0,
                "original_credit" => abs(round($total_amount_fg_in, 2)),
                "rates" => 1,
                "local_debit" => 0,
                "local_credit" => abs(round($total_amount_fg_in, 2)),
            );

            $footer[] = array(
                "original_debit" => abs(round(($total_inventory_rm + $total_final_labor + $total_final_foh),2)),
                "original_credit" => abs(round($total_amount_fg_in, 2)),
                "local_debit" => abs(round(($total_inventory_rm + $total_final_labor + $total_final_foh),2)),
                "local_credit" => abs(round($total_amount_fg_in, 2)),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "DIRECT LABOUR") {
            $labours = $this->crud->query("SELECT account_number, account_name, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE journal_date BETWEEN '$transaction_from' and '$transaction_to' and account_number LIKE '%4521%' GROUP BY account_number");

            $total_labour = 0;
            foreach ($labours as $labour) {
                $total_labour += ($labour->local_debit + $labour->local_credit);
            }

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "COSTING DIRECT LABOUR (COGM)",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512002",
                "account_name" => "COST OF GOOD MANUFACTURING - DIRECT LABOR",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round($total_labour, 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round($total_labour, 2),
                "local_credit" => 0,
            );
            
            $total_labour_credit = 0;
            foreach ($labours as $labour) {
                 $data[] = array(
                    "trans_date" => $transaction_to,
                    "document_no" => "COSTING DIRECT LABOUR (".$labour->account_number.")",
                    "invoice_no" => "-",
                    "company_id" => "ALL",
                    "company_name" => "ALL",
                    "modul" => $modul,
                    "account_number" => $labour->account_number,
                    "account_name" => $labour->account_name,
                    "description" => "-",
                    "currency" => "IDR",
                    "original_debit" => 0,
                    "original_credit" => ($labour->local_debit + $labour->local_credit),
                    "rates" => 1,
                    "local_debit" => 0,
                    "local_credit" => ($labour->local_debit + $labour->local_credit),
                );

                $total_labour_credit += ($labour->local_debit + $labour->local_credit);
            }

            $footer[] = array(
                "original_debit" => round(($total_labour),2),
                "original_credit" => round($total_labour_credit, 2),
                "local_debit" => round(($total_labour),2),
                "local_credit" => round($total_labour_credit, 2),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "FOH") {
            $labours = $this->crud->query("SELECT account_number, account_name, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE journal_date BETWEEN '$transaction_from' and '$transaction_to' and account_number LIKE '%4531%' GROUP BY account_number");

            $total_labour = 0;
            foreach ($labours as $labour) {
                $total_labour += ($labour->local_debit - abs($labour->local_credit));
            }

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "COSTING FOH (COGM)",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "4512003",
                "account_name" => "COST OF GOOD MANUFACTURING - FACTORY",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => round($total_labour, 2),
                "original_credit" => 0,
                "rates" => 1,
                "local_debit" => round($total_labour, 2),
                "local_credit" => 0,
            );
            
            $total_labour_debit = $total_labour;
            $total_labour_credit = 0;
            foreach ($labours as $labour) {
                if(($labour->local_debit - abs($labour->local_credit)) > 0){
                    $labor_debit = 0;
                    $labor_credit = abs($labour->local_debit - abs($labour->local_credit));
                }else{
                    $labor_credit = 0;
                    $labor_debit = abs($labour->local_debit - abs($labour->local_credit));
                }

                 $data[] = array(
                    "trans_date" => $transaction_to,
                    "document_no" => "COSTING FOH (".$labour->account_number.")",
                    "invoice_no" => "-",
                    "company_id" => "ALL",
                    "company_name" => "ALL",
                    "modul" => $modul,
                    "account_number" => $labour->account_number,
                    "account_name" => $labour->account_name,
                    "description" => "-",
                    "currency" => "IDR",
                    "original_debit" => $labor_debit,
                    "original_credit" => $labor_credit,
                    "rates" => 1,
                    "local_debit" => $labor_debit,
                    "local_credit" => $labor_credit,
                );

                $total_labour_debit += $labor_debit;
                $total_labour_credit += $labor_credit;
            }

            $footer[] = array(
                "original_debit" => round(($total_labour_debit),2),
                "original_credit" => round($total_labour_credit, 2),
                "local_debit" => round(($total_labour_debit),2),
                "local_credit" => round($total_labour_credit, 2),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        } elseif ($modul == "CLOSING JOURNAL") {
            $accounts = $this->crud->query("SELECT account_number, account_name FROM account_coa WHERE `status` = '1' ORDER BY account_number asc");

            $data = array();
            $total_debit = 0;
            $total_credit = 0;
            foreach ($accounts as $account) {
                $trial_balance = $this->crud->read("trial_balances", [], ["period" => date("Ym", strtotime($journal_date)), "account_number" => $account->account_number]);
                
                if(@$trial_balance->ending_debit > 0 || @$trial_balance->ending_credit > 0){
                    if(@$trial_balance->ending_debit > 0){
                        $debit = 0;
                        $credit = $trial_balance->ending_debit;
                    }else{
                        $debit = @$trial_balance->ending_credit;
                        $credit = 0;
                    }

                    $data[] = array(
                        "trans_date" => $transaction_to,
                        "document_no" => "CLOSING JOURNAL (DEBIT)",
                        "invoice_no" => "-",
                        "company_id" => "ALL",
                        "company_name" => "ALL",
                        "modul" => $modul,
                        "account_number" => $account->account_number,
                        "account_name" => $account->account_name,
                        "description" => "-",
                        "currency" => "IDR",
                        "original_debit" => $debit,
                        "original_credit" => $credit,
                        "rates" => 1,
                        "local_debit" => $debit,
                        "local_credit" => $credit,
                    );

                    $total_debit += $debit;
                    $total_credit += $credit;
                }
            }

            if(($total_debit - $total_credit) > 0){
                $earn_debit = 0;
                $earn_credit = abs($total_debit - $total_credit);
            }else{
                $earn_debit = abs($total_debit - $total_credit);
                $earn_credit = 0;
            }

            $data[] = array(
                "trans_date" => $transaction_to,
                "document_no" => "CLOSING JOURNAL (DEBIT)",
                "invoice_no" => "-",
                "company_id" => "ALL",
                "company_name" => "ALL",
                "modul" => $modul,
                "account_number" => "3091200",
                "account_name" => "RETAINED EARNING",
                "description" => "-",
                "currency" => "IDR",
                "original_debit" => $earn_debit,
                "original_credit" => $earn_credit,
                "rates" => 1,
                "local_debit" => $earn_debit,
                "local_credit" => $earn_credit,
            );

            $footer[] = array(
                "original_debit" => round(($total_debit + $earn_debit),2),
                "original_credit" => round(($total_credit + $earn_credit), 2),
                "local_debit" => round(($total_debit + $earn_debit),2),
                "local_credit" => round(($total_credit + $earn_credit), 2),
            );

            $result['total'] = count($data);
            $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
            echo json_encode($result);
        }

    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
            $filter_modul = base64_decode($this->input->get('filter_modul'));
            $filter_voucher = base64_decode($this->input->get('filter_voucher'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.journal_date, a.number, a.journal_type_id, b.name as journal_type_name, a.modul, a.remarks, a.currency, a.rates, a.posting,
                a.created_by, a.created_date, a.updated_by, a.updated_date, a.approved, a.approved_to, a.approved_by, a.approved_date,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit');
            $this->db->from('journal_postings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
            }
            $this->db->like('a.journal_type_id', $filter_journal_type);
            $this->db->like('a.modul', $filter_modul);
            $this->db->like('a.number', $filter_voucher);
            $this->db->group_by('a.number');
            $this->db->order_by('a.journal_date', 'asc');

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

    public function datatablesCheck(){
        $post = $this->input->post();

        $journal_date = $post['journal_date'];
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $modul = $post['modul'];
        $company_id = $post['company_id'];

        if(!empty($post['document_no'])){
            $document_no = explode(",", $post['document_no']);
        }else{
            $document_no = array();
        }

        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('journal_date', $transaction_to);
        $this->db->where('modul', $modul);
        if(count($document_no) > 0){
            $this->db->where_in('document_no', $document_no);
        }
        $totalRows = $this->db->count_all_results('', false);

        if($totalRows > 0){
            echo 1;
        }else{
            echo 0;
        }

    }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('number'));
        $filters = json_decode($this->input->post('filterRules'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('journal_date', 'asc');
        $this->db->order_by('account_number', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            $data[] = array(
                "trans_date" => $record['trans_date'],
                "document_no" => $record['document_no'],
                "invoice_no" => $record['invoice_no'],
                "company_name" => $record['company_name'],
                "description" => $record['description'],
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "currency" => $record['currency'],
                "original_debit" => $record['original_debit'],
                "original_credit" => $record['original_credit'],
                "rates" => $record['rates'],
                "local_debit" => $record['local_debit'],
                "local_credit" => $record['local_credit'],
            );

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function datatableUpdates()
    {
        $number = base64_decode($this->input->get('number'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            array_push($data, $record);

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);    
    }

    public function create()
    {
        if ($this->input->post()) {

            $post = $this->input->post();
            $period = date("Ym", strtotime($post['trans_date']));

            if(empty($post['id'])){
                if($post['modul'] == "CLOSING JOURNAL"){
                    $trial_balance = $this->crud->reads("trial_balances", [], [
                        "period" => $period, 
                        "account_number" => $post['account_number']
                    ]);

                    if($post['original_debit'] > 0){
                        $local_debit = (@$trial_balance->local_debit + $post['original_debit']);
                        $local_credit = (@$trial_balance->local_credit + $post['original_credit']);
                    }else{
                        $local_debit = (@$trial_balance->local_debit + $post['original_debit']);
                        $local_credit = (@$trial_balance->local_credit + $post['original_credit']);
                    }

                    $send = $this->crud->update('trial_balances', [
                        "period" => $period, 
                        "account_number" => $post['account_number']
                    ], [
                        "local_debit" => $local_debit,
                        "local_credit" => $local_credit,
                        "ending_debit" => 0,
                        "ending_credit" => 0,
                    ]);
                }

                $send = $this->crud->create('journal_postings', $post);
            }else{
                $send = $this->crud->update('journal_postings', ["id" => $post['id']], $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('journal_postings', $data);
        echo $send;
    }

    public function upload(){
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        $datenow    = "GL" . date("ym");
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        
        $number = $datenow . $autoID;

        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $number,
                'journal_date' => $data->val($i, 2),
                'journal_type_number' => $data->val($i, 3),
                'modul' => $data->val($i, 4),
                'document_no' => $data->val($i, 5),
                'invoice_no' => $data->val($i, 6),
                'company_name' => $data->val($i, 7),
                'trans_date' => $data->val($i, 8),
                'description' => $data->val($i, 9),
                'account_number' => $data->val($i, 10),
                'account_name' => $data->val($i, 11),
                'currency' => $data->val($i, 12),
                'original_debit' => $data->val($i, 13),
                'original_credit' => $data->val($i, 14),
                'rates' => $data->val($i, 15),
                'local_debit' => $data->val($i, 16),
                'local_credit' => $data->val($i, 17)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed(){
        @unlink('excel/failed/journal_postings.txt');
    }

    public function uploadcreateFailed(){
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/journal_postings.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed(){
        $file = "excel/failed/journal_postings.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate(){
        if ($this->input->post()) {
            $data = $this->input->post('data');
            //Cek Process Number
            $journal_types = $this->crud->read('journal_types', [], ["number" => $data['journal_type_number']]);

            if (empty($journal_types->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Journal Type Code " . $data['journal_type_number'] . " Not Found", "theme" => "error"));
            } else {
                $postFinal = array(
                    'number' => $data['number'],
                    'journal_date' => $data['journal_date'],
                    'journal_type_id' => $journal_types->id,
                    'modul' => $data['modul'],
                    'document_no' => $data['document_no'],
                    'invoice_no' => $data['invoice_no'],
                    'company_name' => $data['company_name'],
                    'trans_date' => $data['trans_date'],
                    'description' => $data['description'],
                    'account_number' => $data['account_number'],
                    'account_name' => $data['account_name'],
                    'currency' => $data['currency'],
                    'original_debit' => $data['original_debit'],
                    'original_credit' => $data['original_credit'],
                    'rates' => $data['rates'],
                    'local_debit' => $data['local_debit'],
                    'local_credit' => $data['local_credit']
                );

                $send   = $this->crud->create('journal_postings', $postFinal);
                echo $send;
            }
        }
    }

    public function print_voucher($number)
    {
        $number = base64_decode($number);
        $journal_total = $this->crud->reads('journal_postings', [], ["number" => $number]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($journal_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$number, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $number . '</title>
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
                            <p>Display pages for 15 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as journal_type_name');
            $this->db->from('journal_postings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->like('a.number', $number);
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_general_ledger . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_general_ledger . '</td>
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
                                    <h3>GENERAL LEDGER VOUCHER</h3>
                                </center>
                                <div style="float:left; width:49%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">General Ledger No</td>
                                            <td width="10">:</td>
                                            <td><b>' . $number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Journal Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['journal_date'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Journal Type</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['journal_type_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Company Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['company_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Modul</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['modul'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Document No</th>
                                        <th rowspan="2">Account No</th>
                                        <th rowspan="2">Account Name</th>
                                        <th rowspan="2">Description</th>
                                        <th colspan="3">Original Currency</th>
                                        <th colspan="3">Local Currency</th>
                                    </tr>
                                    <tr>
                                        <th>Currency</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Rates</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>';
            $original_debit = 0;
            $original_credit = 0;
            $rates = 0;
            $local_debit = 0;
            $local_credit = 0;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['document_no'] . '</td>
                                <td>' . $record['account_number'] . '</td>
                                <td>' . $record['account_name'] . '</td>
                                <td>' . $record['description'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['original_debit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['original_credit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['rates'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['local_debit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['local_credit'], 2) . '</td>
                            </tr>';

                $original_debit += $record['original_debit'];
                $original_credit += $record['original_credit'];
                $rates += $record['rates'];
                $local_debit += $record['local_debit'];
                $local_credit += $record['local_credit'];
                $no++;
            }

            $html .= '  <tr>
                            <th style="text-align:right" colspan="6">SUB TOTAL</th>
                            <th style="text-align:right;">' . @number_format($original_debit, 2) . '</th>
                            <th style="text-align:right;">' . @number_format($original_credit, 2) . '</th>
                            <th style="text-align:right;">-</th>
                            <th style="text-align:right;">' . @number_format($local_debit, 2) . '</th>
                            <th style="text-align:right;">' . @number_format($local_credit, 2) . '</th>
                        </tr>
                    </table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>
                </div>
            </div>';

        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=posting_journals_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));
        $filter_voucher = base64_decode($this->input->get('filter_voucher'));

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_postings a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
        }
        $this->db->like('a.journal_type_id', $filter_journal_type);
        $this->db->like('a.modul', $filter_modul);
        $this->db->like('a.number', $filter_voucher);
        $this->db->order_by('a.journal_date', 'asc');
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('a.document_no', 'asc');
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
                            <small>POSTING JOURNAL</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Voucher No</th>
                <th rowspan="2">Journal Date</th>
                <th rowspan="2">Journal Type</th>
                <th rowspan="2">Modul</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Company Name</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th colspan="3">Original Debit</th>
                <th colspan="3">Local Debit</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Rates</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['journal_date'] . '</td>
                    <td>' . $data['journal_type_name'] . '</td>
                    <td>' . $data['modul'] . '</td>
                    <td>' . $data['document_no'] . '</td>
                    <td>' . $data['invoice_no'] . '</td>
                    <td>' . $data['company_name'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['description'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . number_format($data['original_debit'], 4) . '</td>
                    <td>' . number_format($data['original_credit'], 4) . '</td>
                    <td>' . number_format($data['rates'], 2) . '</td>
                    <td>' . number_format($data['local_debit'], 4) . '</td>
                    <td>' . number_format($data['local_credit'], 4) . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
