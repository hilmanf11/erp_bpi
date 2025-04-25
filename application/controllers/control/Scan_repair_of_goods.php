<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_repair_of_goods extends CI_Controller
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
        $this->form_validation->set_rules('checksheet_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/scan_repair_of_goods');
        } else {
            redirect('error_access');
        }
    }

    public function getDocumentNo()
    {
        if ($this->input->get()) {
            $document_no = $this->input->get('document_no');
            $this->db->select('*');
            $this->db->from('repair_of_goods');
            $this->db->where('status', 0);
            $this->db->where('document_no', $document_no);
            $this->db->group_by('document_no');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getDocumentNos()
    {
        if ($this->input->get()) {
            $document_no = $this->input->get('document_no');
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
            $this->db->from('scan_repair_of_goods a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.document_no', $document_no);
           $this->db->order_by('a.item_fg_id', 'ASC');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }


    // public function checkPendingDocument()
    // {
    //     $today = date('Y-m-d');
    //     $document_no = $this->input->get('document_no'); // Ambil document_no yang sedang di-scan
    //     $this->db->select('document_no, trans_date');
    //     $this->db->from('wip_receipts');
    //     $this->db->where('status', 0);
    //     $this->db->where('trans_date <', $today);
    //     $this->db->order_by('trans_date', 'ASC'); // Urutkan berdasarkan tanggal transaksi secara ascending
    //     $pendingDocs = $this->db->get()->result_array();

    //     // Jika ada dokumen yang belum di-scan
    //     if (!empty($pendingDocs)) {
    //         $firstPendingDoc = $pendingDocs[0]; // Dokumen dengan trans_date paling kecil
            
    //         // Jika dokumen yang di-scan adalah dokumen pertama yang tersisa
    //         if ($firstPendingDoc['document_no'] == $document_no) {
    //             // Tidak perlu meminta password, lanjutkan
    //             $result['success'] = true;
    //         } else {
    //             // Jika ada dokumen sebelumnya yang belum di-scan, minta password
    //             $result['error'] = "Please scan the previous document number " . $firstPendingDoc['document_no'];
    //         }
    //     } else {
    //         // Tidak ada dokumen yang belum di-scan
    //         $result['success'] = true;
    //     }

    //     echo json_encode($result);
    // }

    // public function getDocumentNo()
    // {
    //     if ($this->input->get()) {
    //         $document_no = $this->input->get('document_no');

    //         $this->db->select('COALESCE(f.checksheet_number, h.checksheet_number) as checksheet_number, b.wo_no, d.number as item_number, d.name as item_name, d.uom, COALESCE(SUM(g.qty), 0) as qty, g.created_by, g.created_date, g.checksheet_label');
    //         $this->db->from('wip_receipts a');
    //         $this->db->join('checksheets b', 'a.checksheet_number = b.number');
    //         $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
    //         $this->db->join('item_fg d', 'a.item_fg_id = d.id');
    //         $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number','left');
    //         $this->db->join('wip_receipt_labels h', 'a.checksheet_number = h.checksheet_number','left');
    //         $this->db->join('scan_item_receipts_fg g', 'a.checksheet_number = g.checksheet_number and (f.checksheet_label = g.checksheet_label or h.checksheet_label = g.checksheet_label)', 'left');
    //         $this->db->where('a.document_no', $document_no);
    //         $this->db->group_by('COALESCE(f.checksheet_number, h.checksheet_number)');

    //         $totalRows = $this->db->count_all_results('', false);
    //         $records = $this->db->get()->result_array();

    //         $result['total'] = $totalRows;
    //         $result['rows'] = $records;
    //         echo json_encode($result);
    //     }
    // }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $document_no = $this->input->post('document_no');
            // $checksheet_number = $this->input->post('checksheet_number');

            $item = $this->crud->read('repair_of_goods', [], ["document_no" => $document_no]);
            $item_fg_id = $item->item_fg_id;

            $this->db->select('f.checksheet_number, f.checksheet_label, c.so_number, b.wo_no, COALESCE(f.qty, 0) as qty, e.document_no, c.lot_no, d.number as item_number, d.name as item_name, d.id as item_fg_id');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('repair_of_goods e', 'd.id = e.item_fg_id','left');
            $this->db->join('scan_item_receipts_fg f', 'a.checksheet_number = f.checksheet_number');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->where('f.checksheet_label', $checksheet_label);
            $this->db->group_by('f.checksheet_label');

            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if(!$records){
                $this->db->select('f.checksheet_number, f.checksheet_label, c.so_number, b.wo_no, COALESCE(f.qty, 0) as qty, e.document_no, c.lot_no, d.number as item_number, d.name as item_name');
                $this->db->from('wip_receipts a');
                $this->db->join('checksheets b', 'a.checksheet_number = b.number');
                $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
                $this->db->join('item_fg d', 'a.item_fg_id = d.id');
                $this->db->join('repair_of_goods e', 'd.id = e.item_fg_id','left');
                $this->db->join('scan_item_receipts_fg f', 'a.checksheet_number = f.checksheet_number');
                $this->db->where('a.item_fg_id', $item_fg_id);
                $this->db->where('f.checksheet_label', $checksheet_label);
                $this->db->group_by('f.checksheet_label');

                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if(!$records){
                    $this->db->select('COALESCE(a.qty, 0) as qty, a.label_no as checksheet_label, a.item_fg_id, a.packing_date , "NBFG" as type, a.lot_no');
                    $this->db->from('new_barcode_fg a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->where('a.label_no', $checksheet_label);
                    $this->db->group_by('a.label_no');

                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                }
            }


            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function datatables2($checksheet_numbers)
    // {
    //     $checksheet_number = base64_decode($checksheet_numbers);
    //     if ($this->input->post()) {
    //         $filters = json_decode($this->input->post('filterRules'));
    //         $page = $this->input->post('page');
    //         $rows = $this->input->post('rows');
    //         //Pagination 1-10
    //         $page   = isset($page) ? intval($page) : 1;
    //         $rows   = isset($rows) ? intval($rows) : 10;
    //         $offset = ($page - 1) * $rows;
    //         $result = array();
    //         //Select Query
    //         $this->db->select('*');
    //         $this->db->from('scan_item_receipts_fg');
    //         $this->db->where('checksheet_number', $checksheet_number);
    //         $this->db->order_by('checksheet_label', 'ASC');
    //         if (@count($filters) > 0) {
    //             foreach ($filters as $filter) {
    //                 $this->db->like($filter->field, $filter->value);
    //             }
    //         }
    //         //Total Data
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Limit 1 - 10
    //         $this->db->limit($rows, $offset);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();

    //             $item_receipts_fg = $this->crud->read("scan_item_receipts_fg", [], ["checksheet_number" => $post['checksheet_number'], "checksheet_label" => $post['checksheet_label']]);
    //             $checksheet = $this->crud->read("checksheets", [], ["number" => $post['checksheet_number']]);

    //             if (!$item_receipts_fg) {
    //                 $send   = $this->crud->create('scan_item_receipts_fg', $post);
    //                 echo $send;
    //             } else {
    //                 echo json_encode(array("title" => "Available", "message" => "Data Receipt FG has been Scanning", "theme" => "error"));
    //             }
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
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                
                $item_receipts_fg = $this->crud->read("scan_repair_of_goods", [], ["checksheet_number" => $post['checksheet_number'], "checksheet_label" => $post['checksheet_label']]);
                $repair_of_goods = $this->crud->read("repair_of_goods", [], ["document_no" => $post['document_no'], "item_fg_id" => $post['item_fg_id']]);
                $rog_qty = $repair_of_goods->qty; //ok
                $document_no = $post['document_no'];
                $item_fg_id = $post['item_fg_id'];

                $this->db->select('sum(a.qty) as total_qty');
                $this->db->from('scan_repair_of_goods a');
                $this->db->where('a.document_no', $document_no);
                $this->db->where('a.item_fg_id', $item_fg_id);
                $records = $this->db->get()->result_array();

                $total_qty = 0;

                // Memeriksa apakah ada hasil dari query
                if (!empty($records)) {
                    $total_qty = $records[0]['total_qty']; // Ambil total_qty dari hasil
                }

                if(($total_qty + $post['qty']) > $rog_qty) {
                    echo json_encode(array("title" => "Error", "message" => "Qty Scan > Qty Repair", "theme" => "error"));
                } else {
                
                    if (!$item_receipts_fg) {
                        $send = $this->crud->create('scan_repair_of_goods', $post);
                        if(round($total_qty + $post['qty']) == round($rog_qty)){
                            $this->crud->update('repair_of_goods', ["document_no" => $post['document_no'], "item_fg_id" => $post['item_fg_id']], ["status" => "1"]);
                        }
                        echo $send;
                    } else {
                        echo json_encode(array("title" => "Available", "message" => "Data Receipt FG has been Scanning", "theme" => "error"));
                    }
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }


    // public function checkPassword()
    // {
    //     $inputPassword = base64_decode($this->input->post('password'));
    //     // var_dump($inputPassword);
    //     $sessionPassword = 'SCM01@23#';

    //     if ($inputPassword === $sessionPassword) {
    //         echo json_encode(['success' => true]);
    //     } else {
    //         echo json_encode(['success' => false]);
    //     }
    // }

}
