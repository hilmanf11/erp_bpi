<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autopostingjournal extends CI_Model {

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('crud');
        $this->load->model('m_journal_inventory');
    }

    /**
     * Auto Posting Journal Inventory
     * @param string $modul Modul Name
     * @param string $document_no Document Number (Receipt No, Request No, etc)
     */
    public function inventory($modul, $document_no) 
    {
        if (empty($modul) || empty($document_no)) return false;

        // Check if the document has been journaled before
        if ($this->_is_already_posted($modul, $document_no)) {
            log_message('debug', "$document_no for module $modul has been posted before. Skip.");
            return [
                'status'  => false, 
                'message' => "Skip: Document $document_no has already been posted to Journal."
            ]; 
        }

        switch ($modul) {
            case "PURCHASE ORDER RECEIPT":
                return $this->_process_por($modul, $document_no);
                break;

            case "BPM":
                return $this->_process_bpm($modul, $document_no);
                break;

            case "ADJ STO (IN)":
                // return $this->_process_adj_sto_in($document_no);
                break;
            
            default:
                log_message('error', "AutoPosting: Module $modul not found.");
                return [
                    'status'  => false, 
                    'message' => "Failed to Auto Journal: Module $modul is not found."
                ]; 
        }
    }

    // Journal Duplication Protection
    private function _is_already_posted($modul, $doc_no) 
    {
        $check = $this->db->get_where('journal_inventory', [
            'modul' => $modul,
            'document_no' => $doc_no
        ])->num_rows();
        
        return ($check > 0);
    }

    // Get Journal Types
    private function journal_type($module, $acc_no) 
    {
        $all_journal_types = $this->db->get('journal_types')->result();
        if (empty($module) || empty($acc_no)) return null;
        
        foreach ($all_journal_types as $jt) {
            if (trim($jt->module) == trim($module) && trim($jt->account_number) == trim($acc_no)) {
                return $jt->id;
            }
        }
        return null;
    }

    // Get Rate
    private function _get_internal_rate($date, $currency) {
        if ($currency == "IDR") return 1.0;
        $r = $this->db->get_where('standard_exchange_rates', [
            'currency_from' => $currency,
            'start_date <=' => $date,
            'end_date >='   => $date
        ])->row();
        return ($r) ? (float)$r->middle : 1.0;
    }

    // Get Price RM
    private function _get_price_rm($journal_date, $item_rm_id) 
    {
        $ref_date = !empty($journal_date) ? strtotime($journal_date) : time();
        $transaction_from = date("Y-m-01", $ref_date);
        $transaction_to   = date("Y-m-t", $ref_date);

        $r = $this->db->get_where('standard_price_rm', [
            'item_rm_id'    => $item_rm_id,
            'start_date <=' => $transaction_from,
            'end_date >='   => $transaction_to,
        ])->row();

        return ($r) ? (float)$r->price : 1.0;
    }

    // Get Price FG
    private function _get_price_fg($journal_date, $item_fg_id) 
    {
        $ref_date = !empty($journal_date) ? strtotime($journal_date) : time();
        $transaction_from = date("Y-m-01", $ref_date);
        $transaction_to   = date("Y-m-t", $ref_date);

        $r = $this->db->get_where('standard_price_fg', [
            'item_fg_id'    => $item_fg_id,
            'start_date <=' => $transaction_from,
            'end_date >='   => $transaction_to,
        ])->row();

        return ($r) ? (float)$r->price : 1.0;
    }

    // Auto Generate ID
    private function _generate_journal_id() 
    {
        $date = date('Ymd');
        $prefix = $date;
        
        $this->db->select_max('id', 'last_id');
        $this->db->like('id', $prefix, 'after');
        $query = $this->db->get('journal_inventory')->row();
        
        if ($query->last_id) {
            // Ambil 6 digit terakhir, lalu tambah 1
            $last_increment = (int) substr($query->last_id, -6);
            $new_increment = str_pad($last_increment + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $new_increment = '000001';
        }
        
        return $prefix . $new_increment;
    }

    // Auto Generate Voucher Number
    private function _generate_voucher_no($journal_date) 
    {
        // Check if the date needs to be decoded (if from AJAX) or used directly
        $raw_date = (base64_encode(base64_decode($journal_date, true)) === $journal_date) 
                    ? base64_decode($journal_date) 
                    : $journal_date;

        $prefix = "GLINV" . date("ym", strtotime($raw_date));
        
        $this->db->select_max('number', 'kode');
        $this->db->like('number', $prefix, 'after');
        $query = $this->db->get('journal_inventory');
        
        $row = $query->row();
        $last_number = $row->kode;

        if ($last_number == NULL) {
            $autoID = "0001";
        } else {
            $urutan = (int) substr($last_number, -4);
            $urutan++;
            $autoID = sprintf("%04d", $urutan);
        }

        return $prefix . $autoID;
    }


    /** -------------------------------------------
     * Proses Posting Journal Inventory per Modul
     ---------------------------------------------- */

    // PURCHASE ORDER RECEIPT
    private function _process_por($modul, $receipt_no) 
    {
        // DB Transactions (Pak Angga)
        $this->db->trans_begin();

        try {
            // Debit: Raw Material Injection
            $acc_debit  = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '220.190.00'])->row();

            if (!$acc_debit || !$acc_credit) {
                throw new Exception("Account COA not found (150.110.00 or 220.190.00)");
            }

            // Journal Type ID
            $debit_jt_id  = $this->journal_type("PURCHASE ORDER RECEIPT", $acc_debit->account_number);
            $credit_jt_id = $this->journal_type("PURCHASE ORDER RECEIPT", $acc_credit->account_number);

            if (!$debit_jt_id || !$credit_jt_id) {
                throw new Exception("Journal Type Account NOT FOUND for module PURCHASE ORDER RECEIPT! Please add Journal Types");
            }

            // Get status Scan POR
            $subquery = "(SELECT receipt_id, SUM(`status`) as total_scan 
                FROM purchase_order_labels 
                GROUP BY receipt_id) lbl";

            // Get Data POR (Query Detail per Item)
            $this->db->select("
                a.receipt_no as document_no, 
                a.receipt_date as trans_date, 
                a.po_no as invoice_no,
                a.item_rm_id,
                c.name as item_name, 
                b.name as supplier_name, 
                b.currency, 
                a.supplier_id,
                a.qty_receipt2 as qty, 
                f.price, 
                f.discount,
            ");
            // Rumus Total per Item (Debit)
            $this->db->select("((a.qty_receipt2 * f.price) * (1 - (COALESCE(f.discount, 0) / 100))) as item_total_original", FALSE);
            $this->db->select('lbl.total_scan');

            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('purchase_orders f', "a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id", 'left');
            $this->db->join($subquery, "a.receipt_id = lbl.receipt_id", "inner");

            // Filter & Order
            $this->db->where_in('a.receipt_no', $receipt_no);
            $this->db->where('lbl.total_scan >', 0);        // POR sudah di-scan = closed
            $this->db->where('a.print', 1);                 // POR GRN = closed
            $this->db->order_by('a.receipt_no', 'asc'); 

            $records = $this->db->get()->result_array();

            if (empty($records)) {
                throw new Exception("No records found for Receipt No: $receipt_no or document not closed (Scan/Print)");
            }

            $voucher_no = $this->_generate_voucher_no($records[0]['trans_date']);
            $total_orig_all = 0;
            $total_local_all = 0;

            foreach ($records as $row) {
                $rate = $this->_get_internal_rate($row['trans_date'], $row['currency']);
                $amount_original = (float)$row['item_total_original'];
                $amount_local = round($amount_original * $rate, 2);

                $data_debit = [
                    'id'              => $this->_generate_journal_id(),
                    'number'          => $voucher_no,
                    'journal_date'    => $row['trans_date'],
                    "journal_type_id" => $debit_jt_id,
                    'trans_date'      => date('Y-m-d'),
                    'document_no'     => $row['document_no'],
                    'invoice_no'      => $row['invoice_no'],
                    'account_number'  => $acc_debit->account_number,
                    'account_name'    => $acc_debit->account_name,
                    'description'     => $row['item_name'] . " | " . $row['document_no'] . " | " . $row['supplier_name'],
                    'original_debit'  => $amount_original,
                    'original_credit' => 0,
                    'local_debit'     => $amount_local,
                    'local_credit'    => 0,
                    'rates'           => $rate,
                    'currency'        => $row['currency'],
                    'company_name'    => $row['supplier_name'],
                    'company_id'      => $row['supplier_id'],
                    'modul'           => $modul,
                    'remarks'         => "Auto Posting Journal",
                    'created_date'    => date('Y-m-d H:i:s'),
                    'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
                ];

                if (!$this->db->insert('journal_inventory', $data_debit)) {
                    $db_error = $this->db->error();
                    throw new Exception("Database Error (Debit): " . $db_error['message']);
                }

                $total_orig_all += $amount_original;
                $total_local_all += $amount_local;
            }

            // INSERT CREDIT
            $data_credit = [
                'id'              => $this->_generate_journal_id(),
                'number'          => $voucher_no,
                'journal_date'    => $records[0]['trans_date'],
                "journal_type_id" => $credit_jt_id,
                'trans_date'      => date('Y-m-d'),
                'document_no'     => $records[0]['document_no'],
                'invoice_no'      => $records[0]['invoice_no'],
                'account_number'  => $acc_credit->account_number,
                'account_name'    => $acc_credit->account_name,
                'description'     => $records[0]['document_no'] . " | " . $records[0]['supplier_name'],
                'original_debit'  => 0,
                'original_credit' => $total_orig_all,
                'local_debit'     => 0,
                'local_credit'    => $total_local_all,
                'rates'           => $this->_get_internal_rate($records[0]['trans_date'], $records[0]['currency']),
                'currency'        => $records[0]['currency'],
                'company_name'    => $records[0]['supplier_name'],
                'company_id'      => $records[0]['supplier_id'],
                'modul'           => $modul,
                'remarks'         => "Auto Posting Journal",
                'created_date'    => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
            ];

            if (!$this->db->insert('journal_inventory', $data_credit)) {
                $db_error = $this->db->error();
                throw new Exception("Database Error (Credit): " . $db_error['message']);
            }

            // Jika semua oke, commit
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction Failed");
            } else {
                $this->db->trans_commit();
                return ['status' => true, 'message' => 'Success'];
            }

        } catch (Exception $e) {
            // Rollback jika terjadi error
            $this->db->trans_rollback();
            $msg = "Error: " . $e->getMessage();
            log_message('error', $msg);
            return ['status' => false, 'message' => $msg];
        }
    }

    // BPM - SCAN BPM
    private function _process_bpm($modul, $document_no) 
    {
        $this->db->trans_begin();

        try {
            // Debit: Raw Material Injection
            $acc_debit  = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '150.210.00'])->row();

            if (!$acc_debit || !$acc_credit) {
                throw new Exception("Account COA not found (150.110.00 or 150.210.00)");
            }

            // Journal Type ID
            $debit_jt_id  = $this->journal_type($modul, $acc_debit->account_number);
            $credit_jt_id = $this->journal_type($modul, $acc_credit->account_number);

            if (!$debit_jt_id || !$credit_jt_id) {
                throw new Exception("Journal Type Account NOT FOUND for module $modul! Please add Journal Types");
            }

            // Get BPM
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom, COALESCE(SUM(c.qty),0) as qty_actual');
            $this->db->select("'' as supplier_id, '' as supplier_name");
            $this->db->select("'IDR' as currency");

            $this->db->from('bpm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('scan_item_bpm c', 'a.request_id = c.request_id and a.item_rm_id = c.item_rm_id','left');

            $this->db->where('a.status', 1); // BPM status=closed
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $document_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.request_id', 'ASC');

            $records = $this->db->get()->result_array();

            if (empty($records)) {
                throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
            }

            $voucher_no      = $this->_generate_voucher_no($records[0]['request_date']);
            $currency        = "IDR"; // default
            $total_orig_all  = 0;
            $total_local_all = 0;

            foreach ($records as $row) 
            {
                $rate  = $this->_get_internal_rate($row['request_date'], $currency);
                $price = $this->_get_price_rm($row['request_date'], $row['item_rm_id']);

                $amount_original = (float)$row['qty'] * $price;
                $amount_local = round($amount_original * $rate, 2);

                $data_debit = [
                    'id'              => $this->_generate_journal_id(),
                    'number'          => $voucher_no,
                    'journal_date'    => $row['request_date'],
                    "journal_type_id" => $debit_jt_id,
                    'trans_date'      => date('Y-m-d'),
                    'document_no'     => $row['request_no'],
                    'invoice_no'      => $row['request_id'],
                    'account_number'  => $acc_debit->account_number,
                    'account_name'    => $acc_debit->account_name,
                    'description'     => $row['item_name'] . " | " . $row['request_no'] . " | " . $row['supplier_name'],
                    'original_debit'  => $amount_original,
                    'original_credit' => 0,
                    'local_debit'     => $amount_local,
                    'local_credit'    => 0,
                    'rates'           => $rate,
                    'currency'        => $currency,
                    'company_name'    => $row['supplier_name'],
                    'company_id'      => $row['supplier_id'],
                    'modul'           => $modul,
                    'remarks'         => "Auto Posting Journal",
                    'created_date'    => date('Y-m-d H:i:s'),
                    'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
                ];

                if (!$this->db->insert('journal_inventory', $data_debit)) {
                    $db_error = $this->db->error();
                    throw new Exception("Database Error (Debit): " . $db_error['message']);
                }

                $total_orig_all += $amount_original;
                $total_local_all += $amount_local;
            }

            // INSERT CREDIT
            $data_credit = [
                'id'              => $this->_generate_journal_id(),
                'number'          => $voucher_no,
                'journal_date'    => $records[0]['request_date'],
                "journal_type_id" => $credit_jt_id,
                'trans_date'      => date('Y-m-d'),
                'document_no'     => $records[0]['request_no'],
                'invoice_no'      => $records[0]['request_id'],
                'account_number'  => $acc_credit->account_number,
                'account_name'    => $acc_credit->account_name,
                'description'     => $records[0]['request_no'] . " | " . $records[0]['supplier_name'],
                'original_debit'  => 0,
                'original_credit' => $total_orig_all,
                'local_debit'     => 0,
                'local_credit'    => $total_local_all,
                'rates'           => $this->_get_internal_rate($records[0]['request_date'], $currency),
                'currency'        => $currency,
                'company_name'    => $records[0]['supplier_name'],
                'company_id'      => $records[0]['supplier_id'],
                'modul'           => $modul,
                'remarks'         => "Auto Posting Journal",
                'created_date'    => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
            ];

            if (!$this->db->insert('journal_inventory', $data_credit)) {
                $db_error = $this->db->error();
                throw new Exception("Database Error (Credit): " . $db_error['message']);
            }

            // Jika semua oke, commit
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction Failed");
            } else {
                $this->db->trans_commit();
                return ['status' => true, 'message' => 'Success'];
            }

        } catch (Exception $e) {
            // Rollback jika terjadi error
            $this->db->trans_rollback();
            $msg = "Error: " . $e->getMessage();
            log_message('error', $msg);
            return ['status' => false, 'message' => $msg];
        }
    }

}
