<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AutoPostingJournal extends CI_Model {

    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * Auto Posting Journal
     * @param string $modul Modul Name
     * @param string $document_no Document Number (Receipt No, Request No, etc)
     */
    public function index($modul, $document_no) 
    {
        if (empty($modul) || empty($document_no)) return false;

        // Check if the document has been journaled before
        if ($this->_is_already_posted($modul, $document_no)) {
            log_message('debug', "Auto Posting: $document_no for module $modul has been posted. Skip.");
            return true; 
        }

        switch ($modul) {
            case "PURCHASE ORDER RECEIPT":
                return $this->_process_por($document_no);
                break;

            case "BPM":
                // return $this->_process_bpm($document_no);
                break;

            case "ADJ STO (IN)":
                // return $this->_process_adj_sto_in($document_no);
                break;
            
            default:
                log_message('error', "AutoPosting: Module $modul not found.");
                return false;
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

    // PURCHASE ORDER RECEIPT
    private function _process_por($receipt_no) 
    {
        // Debit: Raw Material Injection
        $acc_debit  = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
        // Credit: Accrual Raw Materials
        $acc_credit = $this->db->get_where('account_coa', ['account_number' => '220.190.00'])->row();

        if (!$acc_debit || !$acc_credit) return false;

        // Get Data POR (Query Detail per Item)
        $this->db->select("
            a.receipt_no, a.receipt_date, a.po_no,
            b.name as supplier_name, b.currency, c.name as item_name,
            ((a.qty_receipt2 * f.price) * (1 - (COALESCE(f.discount, 0) / 100))) as amount_orig
        ", FALSE);
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('purchase_orders f', "a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id", 'left');
        $this->db->where('a.receipt_no', $receipt_no);
        $records = $this->db->get()->result_array();

        if (empty($records)) return false;

        // Prepare Data & Voucher Number
        $this->db->trans_start();
        $voucher_no = $this->_generate_voucher_no($records[0]['receipt_date']);
        $total_orig_all = 0;
        $total_local_all = 0;

        foreach ($records as $row) {
            $rate = $this->_get_internal_rate($row['receipt_date'], $row['currency']);
            $amt_orig = (float)$row['amount_orig'];
            $amt_local = round($amt_orig * $rate, 2);

            // INSERT DEBIT (Per Item)
            $this->db->insert('journal_inventory', [
                'number'          => $voucher_no,
                'journal_date'    => $row['receipt_date'],
                'document_no'     => $row['receipt_no'],
                'account_number'  => $acc_debit->account_number,
                'description'     => "Auto: " . $row['item_name'] . " | " . $row['receipt_no'],
                'original_debit'  => $amt_orig,
                'original_credit' => 0,
                'local_debit'     => $amt_local,
                'local_credit'    => 0,
                'rates'           => $rate,
                'modul'           => "PURCHASE ORDER RECEIPT",
                'created_date'    => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->username,
            ]);

            $total_orig_all += $amt_orig;
            $total_local_all += $amt_local;
        }

        // INSERT CREDIT (Accumulation per Document)
        $this->db->insert('journal_inventory', [
            'number'          => $voucher_no,
            'journal_date'    => $records[0]['receipt_date'],
            'document_no'     => $records[0]['receipt_no'],
            'account_number'  => $acc_credit->account_number,
            'description'     => "Auto: Hutang Temp | " . $records[0]['receipt_no'],
            'original_debit'  => 0,
            'original_credit' => $total_orig_all,
            'local_debit'     => 0,
            'local_credit'    => $total_local_all,
            'rates'           => $this->_get_internal_rate($records[0]['receipt_date'], $records[0]['currency']),
            'modul'           => "PURCHASE ORDER RECEIPT",
            'created_date'    => date('Y-m-d H:i:s'),
            'created_by'      => $this->session->username,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
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
}