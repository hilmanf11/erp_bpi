<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_journal_inventory extends CI_Model {

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('crud');
    }

    private function _generate_module_id() 
    {
        $date = date('Ymd');
        $prefix = $date;
        
        $this->db->select_max('id', 'last_id');
        $this->db->like('id', $prefix, 'after');
        $query = $this->db->get('journal_inventory_modules')->row();
        
        if ($query->last_id) {
            // Ambil 6 digit terakhir, lalu tambah 1
            $last_increment = (int) substr($query->last_id, -6);
            $new_increment = str_pad($last_increment + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $new_increment = '000001';
        }
        
        return $prefix . $new_increment;
    }

    public function save_modul_master($data, $is_edit = false)
    {
        if ($is_edit) {
            $this->db->where('id', $data['id']);
            return $this->db->update('journal_inventory_modules', $data);
        } else {
            // Generate ID hanya jika data baru
            $data['id'] = $this->_generate_module_id();
            return $this->db->insert('journal_inventory_modules', $data);
        }
    }

}
