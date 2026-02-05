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

                    if (!$records) {
                        $this->db->select("'-' as po_no, b.request_no as receipt_no, b.request_id as receipt_id, a.label_no, a.qty, b.item_rm_id, c.number as item_number, c.name as item_name, c.uom");
                        $this->db->from('receipt_crusher_labels a');
                        $this->db->join('receipt_crusher_labels b', 'a.request_id = b.request_id');
                        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
                        $this->db->where('a.label_no', $label_no);
                        $this->db->where('a.status_issued', 0);
                        $this->db->group_by('a.label_no');
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

        $query = $this->crud->query("SELECT a.id, a.number, (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_trans_other_in, 0) + COALESCE(f.qty_trans_other_adj_in, 0) + COALESCE(g.qty_in_pur, 0)) - (COALESCE(h.qty_trans_other_out, 0) + COALESCE(i.qty_trans_other_adj_out, 0) + COALESCE(k.qty_scan_out, 0) + COALESCE(l.qty_dn_scrap, 0) + COALESCE(m.qty_issued, 0)) AS ending_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_in FROM scan_item_receipt_crusher WHERE request_date <= '$date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_in FROM transaction_other_component WHERE request_date <= '$date' AND transaction_type = 'ITEM IN' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_in FROM transaction_other_component WHERE request_date <= '$date' AND transaction_type = 'ADJ IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (
                            SELECT 
                                item_id,
                                number,
                                SUM(qty) AS qty_in_pur
                            FROM (
                                SELECT 
                                    b.id AS item_id,
                                    b.number,
                                    SUM(ic.qty) AS qty
                                FROM input_crushing ic
                                JOIN item_rm b ON ic.item_rm_id = b.id
                                LEFT JOIN item_family_subs c ON b.item_sub_family_id = c.id
                                WHERE ic.trans_date <= '$date'
                                GROUP BY b.id, b.number

                                UNION ALL

                                SELECT 
                                    pur.id AS item_id,
                                    pur.number,
                                    SUM(ic.qty) AS qty
                                FROM input_crushing ic
                                JOIN item_rm b ON ic.item_rm_id = b.id
                                JOIN item_family_subs c ON b.item_sub_family_id = c.id
                                JOIN item_rm pur ON pur.number IN ('PUR-PC','PUR-ABS','PUR-ASA','PUR-PA6','PUR-PA66','PUR-PBT','PUR-POM','PUR-PP','PUR-PVC')
                                WHERE (
                                    (c.id = 'PS005' AND pur.number = 'PUR-PC')
                                    OR (c.id = 'PS002' AND pur.number = 'PUR-ABS')
                                    OR (c.id = 'PS003' AND pur.number = 'PUR-ASA')
                                    OR (c.id = 'PS007' AND pur.number = 'PUR-PA6')
                                    OR (c.id = 'PS008' AND pur.number = 'PUR-PA66')
                                    OR (c.id = 'PS009' AND pur.number = 'PUR-PBT')
                                    OR (c.id = 'PS006' AND pur.number = 'PUR-PMMA')
                                    OR (c.id = 'PS010' AND pur.number = 'PUR-POM')
                                    OR (c.id = 'PS004' AND pur.number = 'PUR-PP')
                                    OR (c.id = 'PS001' AND pur.number = 'PUR-PVC')
                                )
                                AND ic.trans_date <= '$date'
                                GROUP BY pur.id, pur.number
                            ) combined
                            GROUP BY item_id, number
                        ) g ON a.id = g.item_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_out FROM transaction_other_component WHERE request_date <= '$date' AND transaction_type = 'ITEM OUT' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_out FROM transaction_other_component WHERE request_date <= '$date' AND transaction_type = 'ADJ OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_out FROM scan_dn_crusher WHERE created_date <= '$date' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_dn_scrap FROM dn_scrap WHERE transaction_date <= '$date' GROUP BY item_rm_id) l ON a.id = l.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$date' and type = 'Other' GROUP BY item_rm_id) m ON a.id = m.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

        if ($query && count($query) > 0) {
            $ending_stock = $query[0]->ending_stock;
        } else {
            $ending_stock = 0;
        }

        // var_dump ($ending_stock);
        // return;

        echo json_encode([
            "ending_stock" => $ending_stock
        ]);
    }

    public function saveTransfer()
    {
        $header  = $this->input->post('header'); 
        $details = json_decode($this->input->post('details'), true);

        if (empty($header) || empty($details)) {
            echo json_encode(["status" => "error", "message" => "Data header or detail is empty!"]);
            return;
        }

        // Mulai Transaksi Database
        $this->db->trans_begin();

        try {
            // === generate autonumber ===
            $trans_date = $header['transaction_date'];
            $year       = date("Y", strtotime($trans_date));
            $datenow    = date("ymd", strtotime($trans_date));

            // Gunakan FOR UPDATE untuk mengunci baris terakhir agar tidak ada ID ganda di waktu bersamaan
            $sqlGetID   = $this->db->query("SELECT MAX(RIGHT(document_no,4)) as kode FROM scan_rm_transfer WHERE YEAR(transaction_date) = '$year' FOR UPDATE");
            $rowID = $sqlGetID->row();
            $kode  = $rowID->kode;

            $urutan = ($kode == NULL) ? 1 : ((int) $kode + 1);
            $autoID = sprintf("%04s", $urutan);
            $autonumber = "WHSTR-" . $datenow . "-" . $autoID;

            $inserted = 0;
            foreach ($details as $row) {
                $dataInsert = [
                    "document_no"      => $autonumber,
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

                $this->crud->create('scan_rm_transfer', $dataInsert);
                $inserted++;

            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(["status" => "error", "message" => "Transaction Failed"]);
            } else {
                $this->db->trans_commit();
                echo json_encode([
                    "status"  => "success",
                    "message" => "Transfer Success with Document No: $autonumber ($inserted rows)"
                ]);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
