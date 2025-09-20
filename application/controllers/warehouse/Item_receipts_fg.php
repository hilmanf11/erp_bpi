<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_receipts_fg extends CI_Controller
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
            $this->load->view('warehouse/item_receipts_fg');
        } else {
            redirect('error_access');
        }
    }

    // public function getDocumentNo()
    // {
    //     if ($this->input->get()) {
    //         $document_no = $this->input->get('document_no');
    //         $this->db->select('COALESCE(f.checksheet_number, h.checksheet_number) as checksheet_number, c.wo_no, d.number as item_number, d.name as item_name, d.uom, COALESCE(SUM(g.qty), 0) as qty, g.created_by, g.created_date, g.checksheet_label');
    //         $this->db->from('wip_receipts a');
    //         $this->db->join('checksheets b', 'a.checksheet_number = b.number');
    //         $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
    //         $this->db->join('item_fg d', 'c.item_fg_id = d.id');
    //         $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number','left');
    //         $this->db->join('wip_receipt_labels h', 'a.checksheet_number = h.checksheet_number','left');
    //         $this->db->join('scan_item_receipts_fg g', 'a.checksheet_number = g.checksheet_number and (f.checksheet_label = g.checksheet_label or h.checksheet_label = g.checksheet_label)' , 'left');
    //         $this->db->where('a.document_no', $document_no);
    //         $this->db->group_by('COALESCE(f.checksheet_number, h.checksheet_number)');

    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    // public function getDocumentNo()
    // {
    //     if ($this->input->get()) {
    //         $document_no = $this->input->get('document_no');

    //         // Cek apakah ada document_no dengan trans_date sebelum hari ini yang statusnya masih 0
    //         $today = date('Y-m-d');
    //         $this->db->select('document_no, trans_date');
    //         $this->db->from('wip_receipts');
    //         $this->db->where('trans_date <', $today);
    //         $this->db->where('status', 0);
    //         $this->db->order_by('trans_date', 'ASC');
    //         $pendingDocs = $this->db->get()->result_array();

    //         if (!empty($pendingDocs)) {
    //             // Jika ada document_no sebelumnya yang belum di-scan, tampilkan pesan
    //             $result['error'] = "Please scan the previous document number" . $pendingDocs[0]['document_no'];
    //             echo json_encode($result);
    //             return;
    //         }

    //         // Jika tidak ada doc sebelumnya yang belum di-scan, lanjutkan pemrosesan normal
    //         $this->db->select('COALESCE(f.checksheet_number, h.checksheet_number) as checksheet_number, c.wo_no, d.number as item_number, d.name as item_name, d.uom, COALESCE(SUM(g.qty), 0) as qty, g.created_by, g.created_date, g.checksheet_label');
    //         $this->db->from('wip_receipts a');
    //         $this->db->join('checksheets b', 'a.checksheet_number = b.number');
    //         $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
    //         $this->db->join('item_fg d', 'c.item_fg_id = d.id');
    //         $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number','left');
    //         $this->db->join('wip_receipt_labels h', 'a.checksheet_number = h.checksheet_number','left');
    //         $this->db->join('scan_item_receipts_fg g', 'a.checksheet_number = g.checksheet_number and (f.checksheet_label = g.checksheet_label or h.checksheet_label = g.checksheet_label)' , 'left');
    //         $this->db->where('a.document_no', $document_no);
    //         $this->db->group_by('COALESCE(f.checksheet_number, h.checksheet_number)');

    //         $totalRows = $this->db->count_all_results('', false);
    //         // Get Data Array
    //         $records = $this->db->get()->result_array();
    //         // Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

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

    public function checkPendingDocument()
    {
        $today = date('Y-m-d');
        $document_no = $this->input->get('document_no'); // Ambil document_no yang sedang di-scan

        // CEK APAKAH DOCUMENT INI SUDAH SELESAI SEMUA
        $this->db->select('COUNT(*) as total_rows, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as closed_rows');
        $this->db->from('wip_receipts');
        $this->db->where('document_no', $document_no);
        $checkStatus = $this->db->get()->row();

        if ($checkStatus && $checkStatus->total_rows > 0 && $checkStatus->total_rows == $checkStatus->closed_rows) {
            // Semua baris status = 1 => sudah close
            echo json_encode([
                'error' => "Document number $document_no is already closed."
            ]);
            return;
        }

        $this->db->select('document_no, trans_date');
        $this->db->from('wip_receipts');
        $this->db->where('status', 0);
        $this->db->order_by('trans_date', 'ASC'); // Urutkan berdasarkan tanggal transaksi secara ascending
        $pendingDocs = $this->db->get()->result_array();

        if (!empty($pendingDocs)) {
            // Ambil trans_date yang paling kecil dari dokumen-dokumen yang belum di-scan
            $firstPendingDate = $pendingDocs[0]['trans_date'];

            // Cari semua dokumen dengan trans_date yang sama dengan yang paling kecil
            $pendingSameDateDocs = array_filter($pendingDocs, function($doc) use ($firstPendingDate) {
                return $doc['trans_date'] == $firstPendingDate;
            });

            // Cek apakah document_no yang di-scan ada di antara dokumen yang memiliki tanggal paling kecil
            $isDocumentAllowed = false;
            foreach ($pendingSameDateDocs as $doc) {
                if ($doc['document_no'] == $document_no) {
                    $isDocumentAllowed = true;
                    break;
                }
            }

            // Jika document_no yang di-scan adalah salah satu dari dokumen dengan tanggal paling kecil, lanjutkan tanpa password
            if ($isDocumentAllowed) {
                $result['success'] = true;
            } else {
                // Jika ada dokumen sebelumnya yang belum di-scan, minta password
                $result['error'] = "Please scan the previous document number " . $pendingSameDateDocs[0]['document_no'];
            }
        } else {
            // Tidak ada dokumen yang belum di-scan
            $result['success'] = true;
        }

        echo json_encode($result);
    }

    public function getDocumentNo()
    {
        if ($this->input->get()) {
            $document_no = $this->input->get('document_no');

            $this->db->select('a.checksheet_number, b.wo_no, d.number as item_number, d.name as item_name, 
            d.uom, COALESCE(SUM(g.qty), 0) as qty, g.created_by, g.created_date, g.checksheet_label, a.qty as qty_wip, (CASE 
                WHEN COALESCE(SUM(g.qty), 0) = a.qty THEN "CLOSE"
                ELSE "OPEN"
            END) as status');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number','left');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id','left');
            $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number','left');
            $this->db->join('wip_receipt_labels h', 'a.checksheet_number = h.checksheet_number','left');
            $this->db->join('scan_item_receipts_fg g', 'a.checksheet_number = g.checksheet_number and (f.checksheet_label = g.checksheet_label or h.checksheet_label = g.checksheet_label)', 'left');
            $this->db->where('a.document_no', $document_no);
            $this->db->group_by('COALESCE(f.checksheet_number, h.checksheet_number)');

            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            $result['total'] = $totalRows;
            $result['rows'] = $records;
            echo json_encode($result);
        }
    }


    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $document_no = $this->input->post('document_no');
            // $checksheet_number = $this->input->post('checksheet_number');

            $this->db->select('f.checksheet_number, f.checksheet_label, c.so_number, b.wo_no, b.packing_date, COALESCE(f.qty, 0) as qty, a.item_fg_id');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('wip_receipt_labels f', 'a.checksheet_number = f.checksheet_number');
            $this->db->where('a.document_no', $document_no);
            $this->db->where('f.checksheet_label', $checksheet_label);
            $this->db->group_by('f.checksheet_label');

            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if(!$records){
                $this->db->select('f.checksheet_number, f.checksheet_label, c.so_number, b.wo_no, b.packing_date, COALESCE(f.qty, 0) as qty, a.item_fg_id');
                $this->db->from('wip_receipts a');
                $this->db->join('checksheets b', 'a.checksheet_number = b.number','left');
                $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
                $this->db->join('item_fg d', 'a.item_fg_id = d.id');
                $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number');
                $this->db->where('a.document_no', $document_no);
                $this->db->where('f.checksheet_label', $checksheet_label);
                $this->db->group_by('f.checksheet_label');

                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if(!$records){
                    $this->db->select('COALESCE(a.qty, 0) as qty, a.label_no as checksheet_label, a.item_fg_id, a.packing_date , "NBFG" as type, "-" as wo_no, "" as checksheet_number');
                    $this->db->from('new_barcode_fg a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->where('a.label_no', $checksheet_label);
                    $this->db->group_by('a.label_no');

                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if(!$records){
                        $this->db->select('a.label_divided as checksheet_label, a.qty, c.item_fg_id, "BDL" as type');
                        $this->db->from('barcode_divides_fg a');
                        $this->db->join('wip_receipt_labels b', 'a.reff = b.checksheet_label');
                        $this->db->join('checksheets c', 'b.checksheet_number = c.number');
                        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
                        $this->db->where('a.label_divided', $checksheet_label);

                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();

                        if(!$records){
                            $this->db->select('a.label_divided as checksheet_label, a.qty, c.item_fg_id, "BDB" as type');
                            $this->db->from('barcode_divides_fg a');
                            $this->db->join('wip_receipt_boxs b', 'a.reff = b.checksheet_label');
                            $this->db->join('checksheets c', 'b.checksheet_number = c.number');
                            $this->db->join('item_fg d', 'c.item_fg_id = d.id');
                            $this->db->where('a.label_divided', $checksheet_label);

                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();

                            if(!$records){
                                $this->db->select('a.label_divided as checksheet_label, a.qty, b.item_fg_id, "NBBD " as type');
                                $this->db->from('barcode_divides_fg a');
                                $this->db->join('new_barcode_fg b', 'a.reff = b.label_no');
                                $this->db->join('item_fg d', 'b.item_fg_id = d.id');
                                $this->db->where('a.label_divided', $checksheet_label);

                                $totalRows = $this->db->count_all_results('', false);
                                $records = $this->db->get()->result_array();
                            }
                        }
                    }
                }
            }


            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatables2($checksheet_numbers)
    {
        $checksheet_number = base64_decode($checksheet_numbers);
        if ($this->input->post()) {
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
            $this->db->from('scan_item_receipts_fg');
            $this->db->where('checksheet_number', $checksheet_number);
            $this->db->order_by('checksheet_label', 'ASC');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
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

    public function checkPackingDateLocked()
    {
        $packing_date = $this->input->post('packing_date');
        $checksheet_number  = $this->input->post('checksheet_number');
        $checksheet_label  = $this->input->post('checksheet_label');

        $this->db->order_by('lock_from', 'DESC');
        $lock = $this->db->get('lsb_lock')->row();

        if ($lock) {
            // Cek apakah packing_date dalam rentang lock
            if ($packing_date >= $lock->lock_from && $packing_date <= $lock->lock_to) {
                $new_date = date('Y-m-d', strtotime('+1 month', strtotime($lock->lock_from)));

                $this->db->update('checksheets', [
                    "packing_date" => $new_date,
                    "remarks" => "Adjust Packing Date"
                ], ["number" => $checksheet_number]);

                $this->db->update('new_barcode_fg', [
                    "packing_date" => $new_date,
                    "remarks" => "Adjust Packing Date"
                ], ["label_no" => $checksheet_label]);

                $this->db->update('scan_item_receipts_fg', [
                    "packing_date" => $new_date
                ], ["checksheet_label" => $checksheet_label]);

                echo json_encode([
                    "status" => true,
                    "message" => "Packing date {$packing_date} adjusted to {$new_date} because period lock {$lock->period}",
                    "new_date" => $new_date
                ]);
            } else {
                // Kalau tidak dalam rentang, biarkan packing_date
                echo json_encode([
                    "status" => false,
                    "message" => "Packing date not in lock period. Date remains the same.",
                    "new_date" => $packing_date
                ]);
            }
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Period Lock not found. Packing date remains the same.",
                "new_date" => $packing_date
            ]);
        }
    }


    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $item_receipts_fg = $this->crud->read("scan_item_receipts_fg", [], ["checksheet_number" => $post['checksheet_number'], "checksheet_label" => $post['checksheet_label']]);
                $checksheet = $this->crud->read("checksheets", [], ["number" => $post['checksheet_number']]);
                if (!$item_receipts_fg) {
                    $send = $this->crud->createNotLog('scan_item_receipts_fg', $post);
                    echo $send;

                    $this->db->select('*');
                    $this->db->from('scan_item_receipts_fg a');
                    $this->db->where('checksheet_number', $post['checksheet_number']);
                    $total_labels = $this->db->get()->result_array();

                    if (!empty($post['checksheet_number'])) {
                        if (count($total_labels) == $checksheet->label) {
                            // $this->crud->update('wip_receipts', ["checksheet_number" => $post['checksheet_number']], ["status" => 1]);
                            $this->db->update('wip_receipts',["status" => "1"], ["checksheet_number" => $post['checksheet_number']]);
                        }
                    }
            
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Receipt FG has been Scanning", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function checkPassword()
    {
        $inputPassword = base64_decode($this->input->post('password'));
        // var_dump($inputPassword);
        $sessionPassword = 'SCM01@23#';

        if ($inputPassword === $sessionPassword) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

}
