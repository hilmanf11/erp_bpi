<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_rm_transfer extends CI_Controller
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
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/scan_rm_transfer');
        } else {
            redirect('error_access');
        }
    }
    public function readArea()
    {
        $data = $this->crud->query("SELECT DISTINCT area FROM warehouse_locations WHERE `status` = '0' ORDER BY area ASC");
        echo json_encode($data);
    }
    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');
            $this->db->select('b.po_no, b.receipt_no, b.receipt_id, a.label_no, a.qty, b.item_rm_id, c.number as item_number, c.name as item_name, c.uom');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->where('a.label_no', $label_no);
            $this->db->where('a.status_issued', 0);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->select("'-' as po_no, b.request_no as receipt_no, b.request_id as receipt_id, a.label_no, a.qty, b.item_rm_id, c.number as item_number, c.name as item_name, c.uom");
                $this->db->from('bpm_labels a');
                $this->db->join('bpm b', 'a.request_id = b.request_id');
                $this->db->join('item_rm c', 'b.item_rm_id = c.id');
                $this->db->where('a.label_no', $label_no);
                $this->db->where('a.status_issued', 0);
                $this->db->group_by('a.label_no');
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select("'-' as po_no, '-' as receipt_no, '-' as receipt_id,a.label_no, a.qty, a.item_rm_id, b.number as item_number, b.name as item_name, b.uom");
                    $this->db->from('new_barcode a');
                    $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                    $this->db->where('a.label_no', $label_no);
                    $this->db->where('a.status_issued', 0);
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
    // public function datatables($label_no = "")
    // {
    //     $date = date("Y-m-d");
    //     $purchase_order_label = $this->crud->read('purchase_order_labels', [], ["label_no" => base64_decode($label_no)]);
    //     //Select Query
    //     $this->db->select('a.label_no, b.receipt_no, b.bc_kind, b.bc_document, b.bc_date, b.po_no, d.number as item_number, d.name as item_name, d.uom, a.qty, a.created_by, a.created_date');
    //     $this->db->from('scan_rm_transfer a');
    //     $this->db->join('purchase_order_labels c', 'a.label_no = c.label_no');
    //     $this->db->join('item_rm d', 'b.item_rm_id = d.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.status', 0);
    //     $this->db->like('a.created_date', $date);
    //     $this->db->where('a.receipt_id', @$purchase_order_label->receipt_id);
    //     $this->db->group_by('a.label_no');
    //     //Total Data
    //     $totalRows = $this->db->count_all_results('', false);
    //     //Get Data Array
    //     $records = $this->db->get()->result_array();

    //     if (!$records) {
    //         $return_material_labels = $this->crud->read('return_material_labels', [], ["label_no" => base64_decode($label_no)]);
    //         $this->db->select('a.label_no, b.return_no as receipt_no, a.po_no, d.number as item_number, d.name as item_name, d.uom, a.qty, a.created_by, a.created_date');
    //         $this->db->from('scan_rm_transfer a');
    //         $this->db->join('return_materials b', 'a.receipt_id = b.return_id and a.receipt_no = b.return_no');
    //         $this->db->join('return_material_labels c', 'a.label_no = c.label_no');
    //         $this->db->join('item_rm d', 'b.item_rm_id = d.id');
    //         $this->db->where('a.deleted', 0);
    //         $this->db->where('a.status', 0);
    //         $this->db->like('a.created_date', $date);
    //         $this->db->where('a.receipt_id', @$return_material_labels->return_id);
    //         $this->db->group_by('a.label_no');
    //         //Total Data
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();

    //         if (!$records) {
    //             $new_barcode = $this->crud->read('new_barcode', [], ["label_no" => base64_decode($label_no)]);
    //             $this->db->select('a.label_no, d.number as item_number, d.name as item_name, d.uom, a.qty, a.created_by, a.created_date');
    //             $this->db->from('new_barcode a');
    //             $this->db->join('item_rm d', 'a.item_rm_id = d.id');
    //             $this->db->where('a.label_no', @$new_barcode->label_no);
    //             $this->db->where('a.deleted', 0);
    //             $this->db->where('a.status', 0);
    //             // $this->db->like('a.created_date', $date);
    //             // $this->db->group_by('a.label_no');
    //             //Total Data
    //             $totalRows = $this->db->count_all_results('', false);
    //             //Get Data Array
    //             $records = $this->db->get()->result_array();
    //         }
    //     }

    //     //Mapping Data
    //     $result['total'] = $totalRows;
    //     $result = array_merge($result, ['rows' => $records]);
    //     echo json_encode($result);
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             $item_receipts = $this->crud->read("scan_rm_transfer", [], ["label_no" => $post['label_no']]);
    //             if (!$item_receipts) {
    //                 $dataFinal = array(
    //                     //field
    //                     "label_no" => $post['label_no'],
    //                     "receipt_no" => $post['receipt_no'],
    //                     "receipt_id" => $post['receipt_id'],
    //                     "po_no" => $post['po_no'],
    //                     "item_rm_id" => $post['item_rm_id'],
    //                     "qty" => $post['qty'],
    //                 );

    //                 $send   = $this->crud->create('scan_rm_transfer', $dataFinal);
    //                 // if ($send) {
    //                 //     $update   = $this->crud->update('purchase_order_labels', ["label_no" => $post['label_no']], ["status" => 1]);
    //                 //     $update   = $this->crud->update('return_material_labels', ["label_no" => $post['label_no']], ["status" => 1]);
    //                 //     $update   = $this->crud->update('new_barcode', ["label_no" => $post['label_no']], ["status" => 1]);
    //                 //     echo $send;
    //                 // }

    //                 echo $send;
    //             } else {
    //                 echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
    //             }
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function checkLabel()
    {
        $label_no   = $this->input->post('label_no');
        $transfer_from = $this->input->post('transfer_from');

        $last = $this->db->select('transfer_to')
                        ->where('label_no', $label_no)
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get('scan_rm_transfer')
                        ->row();

        if ($last) {
            $valid = ($last->transfer_to == $transfer_from);
        } else {
            $valid = true;
        }

        echo json_encode(['valid' => $valid]);
    }

    public function checkEndingBalance()
    {
        $item_rm_id = $this->input->post('item_rm_id');
        $date     = $this->input->post('cutoff');

        if (empty($date)) {
            $date = date("Y-m-d");
        }

        $query = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
            (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

        if ($query && count($query) > 0) {
            $ending_stock = $query[0]->ending_stock;
        } else {
            $ending_stock = 0;
        }

        echo json_encode([
            "ending_stock" => $ending_stock
        ]);
    }


    public function saveTransfer()
    {
        $header  = $this->input->post('header'); 
        $details = json_decode($this->input->post('details'), true);

        if (empty($header) || empty($details)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Data header atau detail tidak ada!"
            ]);
            return;
        }

        // === generate autonumber (document_no) ===
        $trans_date = $header['transaction_date'];
        $year       = date("Y", strtotime($trans_date));
        $datenow    = date("ymd", strtotime($trans_date));

        $sqlGetID   = $this->db->query("SELECT MAX(RIGHT(document_no,4)) as kode FROM scan_rm_transfer WHERE YEAR(transaction_date) = '$year'");
        $rowID = $sqlGetID->row();
        $kode  = $rowID->kode;

        $urutan = ($kode == NULL) ? 1 : ((int) $kode + 1);
        $autoID = sprintf("%04s", $urutan);
        $autonumber = "WHSTR-" . $datenow . "-" . $autoID;

        // === insert detail dengan document_no sama ===
        $inserted = 0;
        foreach ($details as $row) {
            $dataInsert = [
                "document_no"      => $autonumber, // <--- tambahkan ini
                "transfer_from"    => $header['transfer_from'],
                "transfer_to"      => $header['transfer_to'],
                "transaction_date" => $header['transaction_date'],
                "cutoff"           => $header['cutoff'],
                "division"         => $header['division'],
                "ship_by"          => $header['ship_by'],
                "remarks"          => $header['remarks'],
                "label_no"         => $row['label_no'],
                "receipt_no"       => $row['receipt_no'],
                "receipt_id"       => $row['receipt_id'],
                "po_no"            => $row['po_no'],
                "item_rm_id"       => $row['item_rm_id'],
                "qty"              => $row['qty']
            ];

            if ($this->crud->create('scan_rm_transfer', $dataInsert)) {
                $inserted++;
            }
        }

        if ($inserted > 0) {
            echo json_encode([
                "status"  => "success",
                "message" => "Transfer Succes with Document No: $autonumber ($inserted row)"
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Gagal menyimpan data"
            ]);
        }
    }
}
