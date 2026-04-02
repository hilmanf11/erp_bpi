<?php
date_default_timezone_set("Asia/Jakarta");
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_reconciliation extends CI_Model 
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('crud');
    }

    // Increment ID 
    public function autoid($table)
    {
        $date = date("Ymd");
        
        $this->db->select_max('id', 'kode');
        $this->db->like('id', $date, 'after');
        $query = $this->db->get($table);
        $row = $query->row();

        if ($row->kode == NULL) {
            // Jika belum ada data hari ini, mulai dari YYYYMMDD000001
            return (int) ($date . "000001");
        } else {
            // Jika sudah ada, cukup tambah 1
            return (int) $row->kode + 1;
        }
    }

    /**
     * Menghapus data lama jika ada berdasarkan filter
     * Untuk replace data ketika upload ulang
     */
    public function replace_existing_data($filters) 
    {
        // DB Transaction (Pak Angga)
        $this->db->trans_start();

        $where_clause = [
            'bank_account'   => $filters['bank_account'],
            'account_number' => $filters['account_number'],
            'start_date'     => $filters['from'],
            'end_date'       => $filters['to']
        ];

        // Get data lama untuk log sebelum dihapus
        $this->db->where($where_clause);
        $this->db->where("DATE_FORMAT(posting_date, '%Y-%m-%d') BETWEEN '{$filters['from']}' AND '{$filters['to']}'");
        $query = $this->db->get('bank_reconciliation');
        $dataBefore = $query->result();

        if ($query->num_rows() > 0) {
            // Hapus dengan kriteria yang sama persis
            $this->db->where($where_clause);
            $this->db->where("DATE_FORMAT(posting_date, '%Y-%m-%d') BETWEEN '{$filters['from']}' AND '{$filters['to']}'");
            $this->db->delete('bank_reconciliation');

            $affected = $this->db->affected_rows();
            
            $this->db->trans_complete();

            if ($this->db->trans_status() !== FALSE && $affected > 0) {
                $this->crud->logs("Delete", json_encode($dataBefore), 'bank_reconciliation');
                return ["title" => "Info", "message" => "Existing data found and replaced.", "theme" => "success"];
            }
        }

        $this->db->trans_complete();
        return ["title" => "Info", "message" => "No existing data to delete.", "theme" => "info"];
    }

    // INSERT BATCH
    public function batch_insert_with_log($datas) 
    {
        $results = [];
        
        // Get Auto ID
        $next_id = $this->autoid('bank_reconciliation');

        // DB Transaction (Pak Angga)
        $this->db->trans_begin();

        try {
            foreach ($datas as $index => $row) 
            {
                $row['id'] = $next_id;

                $send = $this->db->insert('bank_reconciliation', $row);
                if (!$send) {
                    // Jika insert gagal (false), ambil error dari DB
                    $db_error = $this->db->error();
                    throw new Exception("DB Error on row " . ($index + 1) . ": " . $db_error['message']);
                }

                $results[] = [
                    "row"     => $index + 1,
                    "theme"   => "success",
                    "title"   => "Good Job",
                    "message" => "Data Saved Successfully"
                ];

                $next_id++; 
            }

            // If not ok then rollback
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new Exception("Transaction status failed. Rollback.");
            } else {
                $this->db->trans_commit();
            }

        } catch (Throwable $t) { 
            $this->db->trans_rollback();            
            $results = [[
                "row"     => "SYSTEM",
                "theme"   => "error",
                "title"   => "Fatal Error / Rollback",
                "message" => $t->getMessage() . " in " . $t->getFile() . " on line " . $t->getLine()
            ]];
        }

        return $results;
    }
}