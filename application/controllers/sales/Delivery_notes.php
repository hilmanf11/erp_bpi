<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_notes extends CI_Controller
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
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/delivery_notes');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $dn_number = base64_decode((string) $this->input->get('delivery_note_no'));

        $this->db->select('a.*, b.id as item_fg_id, b.name as item_name, b.number as item_number, b.uom, b.weight');
        $this->db->select('b.hs_code');
        $this->db->from('delivery_notes a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('item_boxs c', 'b.boxs = c.name', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.delivery_note_no', $dn_number);
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function datatablesTemp($delivery_order_no, $delivery_note_date)
    {
        $delivery_order_no = explode(",", base64_decode($delivery_order_no));
        $delivery_note_date = base64_decode($delivery_note_date);
        // var_dump($delivery_note_date);

        $this->db->select("a.delivery_order_no, a.njo_number,
            b.id as item_fg_id, 
            b.number as item_fg_number, 
            b.name as item_fg_name,
            (CASE WHEN c.sales_order_no is NULL THEN d.customer_order_no
            ELSE c.customer_order_no
            END) as customer_order_no,
            (CASE WHEN c.sales_order_no is NULL THEN d.sales_order_no
            ELSE c.sales_order_no
            END) as sales_order_no,
            a.trans_type, 
            a.qty_del as qty,
            (CASE
            WHEN a.delivery_date >= '$delivery_note_date' THEN 0
            ELSE 1
            END) as status_delivery,
            b.uom");
        $this->db->select('b.hs_code');
        $this->db->from('delivery_orders a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no','left');
        $this->db->join('sales_order_rm d', 'a.sales_order_no_rm = d.sales_order_no','left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.qty_del !=', 0);
        $this->db->where_in('a.delivery_order_no', $delivery_order_no);
        $this->db->group_by(array('a.delivery_order_no', 'COALESCE(a.sales_order_no, a.sales_order_no_rm)', 'b.id'));
    
        $this->db->order_by('a.delivery_order_no', 'ASC');
        $this->db->order_by('b.id', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDivision($customer_id)
    {
        $query = "
            SELECT a.division
            FROM sales_orders a
            JOIN customers b ON a.customer_id = b.id
            WHERE a.customer_id = '$customer_id'
            GROUP BY a.division
            
            UNION
    
            SELECT a.division
            FROM sales_order_rm a
            JOIN customers b ON a.customer_id = b.id
            WHERE a.customer_id = '$customer_id'
            GROUP BY a.division
        ";
        
        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function readShipping($customer_id, $division)
    {
        $division = base64_decode($division);

        $query = "
            SELECT b.address AS address_name, b.id
            FROM sales_orders a
            JOIN customer_address b ON a.customer_address_id = b.id
            WHERE a.customer_id = '$customer_id' AND a.division = '$division'
            GROUP BY b.address, b.id
            
            UNION

            SELECT b.address AS address_name, b.id
            FROM sales_order_rm a
            JOIN customer_address b ON a.customer_address_id = b.id
            WHERE a.customer_id = '$customer_id' AND a.division = '$division'
            GROUP BY b.address, b.id
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }


    // public function readDo($customer_id, $division, $customer_address)
    // {
    //     $division = base64_decode($division);
    //     $customer_address = base64_decode($customer_address);

    //     $send = $this->crud->query("SELECT DISTINCT a.delivery_order_no, a.delivery_date
    //     FROM delivery_orders a 
    //     JOIN item_fg b ON a.item_fg_id = b.id 
    //     JOIN sales_orders c ON a.item_fg_id = c.item_fg_id
    //     JOIN customers d ON c.customer_id = d.id
    //     JOIN customer_address e ON c.customer_address_id = e.id
    //     LEFT JOIN shipping_orders f ON a.delivery_order_no = f.delivery_order_no 
    //     WHERE a.status = '0' and a.customer_id = '$customer_id' and c.division = '$division' and e.id = '$customer_address'");
    //     echo json_encode($send);
    // }

    public function readDo($customer_id, $division, $customer_address)
    {
        $division = base64_decode($division);
        $customer_address = base64_decode($customer_address);

        $query = "SELECT 
            DISTINCT a.delivery_order_no, a.delivery_date
            FROM delivery_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            JOIN sales_orders c ON a.item_fg_id = c.item_fg_id
            JOIN customers d ON c.customer_id = d.id
            JOIN customer_address e ON c.customer_address_id = e.id
            -- JOIN shipping_orders f ON a.delivery_order_no = f.delivery_order_no 
            WHERE a.delivery_order_no NOT IN (
                SELECT delivery_order_no
                FROM delivery_orders
                WHERE status_scan = '0'
            )
            AND a.customer_id = '$customer_id' 
            AND c.division = '$division' 
            AND e.id = '$customer_address'
            AND a.status = '0'

            UNION 

            SELECT 
            DISTINCT a.delivery_order_no, a.delivery_date
            FROM delivery_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            JOIN sales_order_rm c ON a.item_fg_id = c.item_fg_id
            JOIN customers d ON c.customer_id = d.id
            JOIN customer_address e ON c.customer_address_id = e.id
            -- JOIN shipping_orders f ON a.delivery_order_no = f.delivery_order_no 
            WHERE a.delivery_order_no NOT IN (
                SELECT delivery_order_no
                FROM delivery_orders
                WHERE status_scan = '0'
            ) 
            AND a.customer_id = '$customer_id' 
            AND c.division = '$division'
            AND e.id = '$customer_address'
            AND a.status = '0'
        ";

        // $query = " SELECT DISTINCT 
        //     a.delivery_order_no, 
        //     a.delivery_date,
        //     COALESCE((a.qty_del - e.shipping), a.qty_del) AS remaining_qty
        // FROM delivery_orders a
        // JOIN item_fg b ON a.item_fg_id = b.id 
        // JOIN sales_orders c ON a.item_fg_id = c.item_fg_id
        // JOIN customers d ON c.customer_id = d.id
        // JOIN customer_address e ON c.customer_address_id = e.id
        // LEFT JOIN (
        //     SELECT 
        //         a.delivery_order_no, 
        //         b.item_fg_id, 
        //         COALESCE(SUM(a.qty), 0) AS shipping
        //     FROM shipping_orders a
        //     JOIN (
        //         SELECT DISTINCT checksheet_label, item_fg_id 
        //         FROM scan_item_receipts_fg
        //     ) b ON a.checksheet_label = b.checksheet_label
        //     JOIN (
        //         SELECT DISTINCT sales_order_no 
        //         FROM (
        //             SELECT sales_order_no FROM sales_orders 
        //             UNION ALL 
        //             SELECT sales_order_no FROM sales_order_rm
        //         ) combined_sales_orders
        //     ) c ON a.sales_order_no = c.sales_order_no
        //     -- WHERE a.delivery_order_no 
        //     GROUP BY a.delivery_order_no, b.item_fg_id
        // ) e ON a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id
        //  WHERE a.status = '0' 
        //  AND a.customer_id = '$customer_id' 
        //  AND c.division = '$division' 
        //  AND e.id = '$customer_address'
        //  AND a.qty_del = e.shipping

        // UNION 

        // SELECT DISTINCT 
        //     a.delivery_order_no, 
        //     a.delivery_date,
        //     COALESCE((a.qty_del - e.shipping), a.qty_del) AS remaining_qty
        // FROM delivery_orders a 
        // JOIN item_fg b ON a.item_fg_id = b.id 
        // JOIN sales_order_rm c ON a.item_fg_id = c.item_fg_id
        // JOIN customers d ON c.customer_id = d.id
        // JOIN customer_address e ON c.customer_address_id = e.id
        // LEFT JOIN (
        //     SELECT 
        //         a.delivery_order_no, 
        //         b.item_fg_id, 
        //         COALESCE(SUM(a.qty), 0) AS shipping
        //     FROM shipping_orders a
        //     JOIN (
        //         SELECT DISTINCT checksheet_label, item_fg_id 
        //         FROM scan_item_receipts_fg
        //     ) b ON a.checksheet_label = b.checksheet_label
        //     JOIN (
        //         SELECT DISTINCT sales_order_no 
        //         FROM (
        //             SELECT sales_order_no FROM sales_orders 
        //             UNION ALL 
        //             SELECT sales_order_no FROM sales_order_rm
        //         ) combined_sales_orders
        //     ) c ON a.sales_order_no = c.sales_order_no
        //     -- WHERE a.delivery_order_no 
        //     GROUP BY a.delivery_order_no, b.item_fg_id
        // ) e ON a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id
        //  WHERE a.status = '0' 
        //  AND a.customer_id = '$customer_id' 
        //  AND c.division = '$division' 
        //  AND e.id = '$customer_address'
        //  AND a.qty_del = e.shipping
        //  ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

 
    public function readDelivery_note_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_note_no, delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readDelivery_note_nos()
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_note_no, delivery_order_no FROM delivery_notes WHERE `deleted` = 0");
        echo json_encode($send);
    }

    public function readDelivery_order_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readDelivery_order_nos()
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_order_no FROM delivery_notes WHERE `deleted` = 0");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readSalesOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM delivery_notes WHERE `deleted` = 0");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM delivery_notes WHERE `deleted` = 0");
        echo json_encode($send);
    }

    public function number($delivery_note_date, $divison_number)
    {
        $divison_number = base64_decode($divison_number);
        $customer_number = base64_decode((string) $this->input->post('customer_number'));

        $numberCust = $customer_number;
        $divisions  = "DN". $divison_number;
        $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT MAX(SUBSTR(delivery_note_no, 7, 4)) as kode FROM delivery_notes WHERE `delivery_note_no` like '%$dn_no%' and division = '$divison_number'");
        $rowID      = $sqlGetID->row();
        $kode       = @$rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow;
    }

    public function number_new($customer_number, $delivery_note_date, $divison_number)
    {
        $customer_numbers = base64_decode($customer_number);
        $delivery_note_dates = base64_decode($delivery_note_date);
        $divison_numbers = base64_decode($divison_number);
        // var_dump($delivery_note_dates);
        $numberCust = $customer_numbers;
        $divisions  = "DN". $divison_numbers;
        $datenow    = date("my", strtotime((string) $delivery_note_dates));
        // var_dump($datenow);
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT MAX(SUBSTR(delivery_note_no, 7, 4)) as kode FROM delivery_notes WHERE `delivery_note_no` like '%$dn_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = @$rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow;
    }

    public function get_detail_dn_ajax() 
    {
        // Tangkap parameter dari AJAX
        $delivery_note_no = $this->input->post('delivery_note_no', TRUE);
        
        if (empty($delivery_note_no)) {
            echo json_encode(['total' => 0, 'rows' => []]);
            return;
        }

        $this->db->select('delivery_notes.id as id, delivery_note_no, delivery_order_no, item_fg_id, sales_order_no, qty, delivery_notes.status, item_fg.number as number');
        $this->db->from('delivery_notes');
        $this->db->where('delivery_note_no', $delivery_note_no);
        $this->db->join('item_fg', 'item_fg.id = delivery_notes.item_fg_id', 'left');
        
        $query = $this->db->get();
        $result = $query->result_array();
        
        $response = array(
            'total' => count($result),
            'rows'  => $result
        );
        
        // Set header JSON dan cetak output
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // public function get_valid_so_baru()
    // {
    //     $dn_no = $this->input->get('dn_no', TRUE);
    //     $item_fg_id = $this->input->get('item_fg_id', TRUE);

    //     if (empty($dn_no) || empty($item_fg_id)) {
    //         echo json_encode([]);
    //         return;
    //     }

    //     // A. Ambil data referensi dari DN saat ini
    //     $dn = $this->db->get_where('delivery_notes', array(
    //         'delivery_note_no' => $dn_no, 
    //         'item_fg_id' => $item_fg_id
    //     ))->row();

    //     if (!$dn) {
    //         echo json_encode([]);
    //         return;
    //     }

    //     // B. Cari tanggal pengiriman (Schedule) dari SO lama
    //     $old_sched = $this->db->get_where('sales_order_deliveries', array(
    //         'sales_order_no' => $dn->sales_order_no,
    //         'item_fg_id' => $item_fg_id
    //     ))->row();

    //     // Jika schedule ada, pakai tanggalnya. Jika tidak, fallback ke delivery_note_date
    //     $target_date = $old_sched ? $old_sched->trans_date : $dn->delivery_note_date;

    //     // =========================================================
    //     // C. REVISI QUERY FILTER (SUDAH TERMASUK CEK DO PENGGANTI)
    //     // =========================================================
        
    //     // 1. Tambahkan do_baru.delivery_order_no AS target_do pada SELECT
    //     $this->db->select('so.sales_order_no, do_baru.delivery_order_no AS target_do');
    //     $this->db->from('sales_orders so');
    //     $this->db->join('sales_order_deliveries sod', 'so.sales_order_no = sod.sales_order_no');
        
    //     // 2. INI KUNCINYA: Wajib join ke tabel DO untuk memastikan SO ini punya DO
    //     $this->db->join('delivery_orders do_baru', 'so.sales_order_no = do_baru.sales_order_no');

    //     $this->db->where('so.customer_id', $dn->customer_id);
    //     $this->db->where('so.item_fg_id', $item_fg_id);
    //     $this->db->where('sod.item_fg_id', $item_fg_id);
    //     $this->db->where('sod.trans_date', $target_date);
    //     $this->db->where('so.sales_order_no !=', $dn->sales_order_no); // Jangan tampilkan SO saat ini
        
    //     // 3. Pastikan DO target memiliki part/item yang sama
    //     $this->db->where('do_baru.item_fg_id', $item_fg_id);
        
    //     // Opsional Param 3 & 4
    //     $this->db->where('so.outstanding >', 0); 
        
    //     // Group by agar jika 1 SO punya banyak DO target, tidak muncul duplikat data yang mengganggu
    //     $this->db->group_by('so.sales_order_no');
    //     $valid_sos = $this->db->get()->result_array();

    //     // Kembalikan dalam bentuk JSON untuk EasyUI Combogrid
    //     $this->output
    //         ->set_content_type('application/json')
    //         ->set_output(json_encode($valid_sos));
    // }

    public function get_valid_so_baru()
    {
        $dn_no = $this->input->get('dn_no', TRUE);
        $item_fg_id = $this->input->get('item_fg_id', TRUE);
        
        $debug = $this->input->get('debug', TRUE); 

        if (empty($dn_no) || empty($item_fg_id)) {
            echo json_encode([]);
            return;
        }

        // Ambil data referensi dari DN saat ini
        $dn = $this->db->get_where('delivery_notes', array(
            'delivery_note_no' => $dn_no, 
            'item_fg_id' => $item_fg_id
        ))->row();

        if (!$dn) {
            echo json_encode([]);
            return;
        }

        
        $do_lama = $this->db->get_where('delivery_orders', array(
            'delivery_order_no' => $dn->delivery_order_no,
            'item_fg_id' => $item_fg_id
        ))->row();

        $target_date = $do_lama ? $do_lama->delivery_date : $dn->delivery_note_date;

              
        $this->db->select('so.sales_order_no, MAX(do_baru.delivery_order_no) AS target_do');
        $this->db->from('sales_orders so');
        $this->db->join('sales_order_deliveries sod', 'so.sales_order_no = sod.sales_order_no');
        
        // memastikan SO punya DO
        $this->db->join('delivery_orders do_baru', 'so.sales_order_no = do_baru.sales_order_no');

        $this->db->where('so.customer_id', $dn->customer_id);
        $this->db->where('so.item_fg_id', $item_fg_id);
        $this->db->where('sod.item_fg_id', $item_fg_id);
        $this->db->where('sod.trans_date', $target_date); 
        $this->db->where('so.sales_order_no !=', $dn->sales_order_no); 
        
        $this->db->where('do_baru.item_fg_id', $item_fg_id);
        $this->db->where('do_baru.delivery_date', $target_date);
        $this->db->where('so.outstanding >', 0); 
        
        $this->db->group_by('so.sales_order_no');

        // =========================================================
        // ALAT BANTU DEBUGGING
        // =========================================================
        if ($debug == '1') {
            echo "<b>1. Parameter Input:</b><br>";
            echo "Item ID: " . $item_fg_id . "<br>";
            echo "Customer ID: " . $dn->customer_id . "<br>";
            echo "Target Date (Trans Date): " . $target_date . "<br><br>";
            
            echo "<b>2. Raw SQL Query yang dibentuk CI3:</b><br>";
            echo $this->db->get_compiled_select(); 
            die();
        }

        $valid_sos = $this->db->get()->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($valid_sos));
    }

    public function get_labels_for_delete()
    {
        $delivery_order_no = $this->input->post('delivery_order_no', TRUE);
        $item_fg_id = $this->input->post('item_fg_id', TRUE); 

        if (empty($delivery_order_no) || empty($item_fg_id)) {
            echo json_encode(['total' => 0, 'rows' => []]);
            return;
        }

        $this->db->select('id, checksheet_label, qty');
        $this->db->from('shipping_orders');
        $this->db->where('delivery_order_no', $delivery_order_no);
        $this->db->where('item_fg_id', $item_fg_id); 
        $this->db->where('deleted', 0); 
        $this->db->order_by('created_date', 'ASC'); 

        $query = $this->db->get();
        $result = $query->result_array();

        $response = array(
            'total' => count($result),
            'rows'  => $result
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function eksekusi_ubah_so_qty()
    {
        // =================================================================
        // 1. TANGKAP INPUT (Termasuk item_id yang baru ditambahkan)
        // =================================================================
        $id_dn = $this->input->post('id_dn', TRUE);
        $dn_no = $this->input->post('dn_no', TRUE);
        $item_id = $this->input->post('item_id', TRUE); // <-- Sudah ditambahkan
        $old_so = $this->input->post('old_so', TRUE);
        $new_so = $this->input->post('new_so', TRUE);
        $new_do = $this->input->post('new_do', TRUE); 
        $old_qty = (float) $this->input->post('old_qty', TRUE);
        $new_qty = (float) $this->input->post('new_qty', TRUE);
        
        $labels_to_delete = json_decode((string) $this->input->post('labels_to_delete', TRUE), TRUE);

        if (empty($new_so)) {
            $new_so = $old_so;
        }

        $dn_row = $this->db->select('delivery_note_no')->get_where('delivery_notes', ['id' => $id_dn])->row();
        if ($blocked = $this->_journaled_block($dn_row->delivery_note_no ?? $dn_no)) {
            echo json_encode(['status' => FALSE, 'message' => $blocked['message']]);
            return;
        }

        // =================================================================
        // 2. VALIDASI CEK OVER-DELIVERY (Skenario Pindah SO atau Qty Naik)
        // =================================================================
        $so_target = $this->db->get_where('sales_orders', [
            'sales_order_no' => $new_so, 
            'item_fg_id' => $item_id
        ])->row();
            
        if ($so_target) {
            // Hitung total DN yang sudah ada di SO Target (Kecuali DN ini)
            $q_sum = $this->db->query("
                SELECT COALESCE(SUM(qty), 0) as total 
                FROM delivery_notes 
                WHERE sales_order_no = ? AND item_fg_id = ? AND id != ?
            ", [$new_so, $item_id, $id_dn])->row();
                
            $existing_dn_qty = $q_sum ? (float)$q_sum->total : 0;
            $projected_qty = $existing_dn_qty + $new_qty;
                
            // Jika melebihi kuota pesanan di Master SO, tolak!
            if ($projected_qty > (float)$so_target->qty) {
                echo json_encode([
                    'status' => FALSE, 
                    'message' => 'Gagal! Total kumulatif DN ('.$projected_qty.') akan melebihi batas pesanan di Sales Order ('.$so_target->qty.').'
                ]);
                return;
            }
        }

        // =================================================================
        // 3. MULAI DATABASE TRANSACTION (Strict Mode)
        // =================================================================
        $this->db->trans_start();

        // Kunci baris DN
        $q_dn = "SELECT * FROM delivery_notes WHERE id = " . $this->db->escape($id_dn) . " FOR UPDATE";
        $dn = $this->db->query($q_dn)->row();

        if (!$dn) {
            $this->db->trans_rollback();
            echo json_encode(['status' => FALSE, 'message' => 'Data Delivery Note tidak ditemukan.']);
            return;
        }

        $old_do = $dn->delivery_order_no;

        $do_lama_data = $this->db->get_where('delivery_orders', [
            'delivery_order_no' => $old_do,
            'item_fg_id' => $item_id
        ])->row();
        
        // Gunakan delivery_date dari DO. Jika anomali (DO tidak ditemukan), fallback ke tanggal hari ini
        $trans_date = $do_lama_data ? $do_lama_data->delivery_date : date('Y-m-d');

        $so_baru_data = $this->db->get_where('sales_orders', ['sales_order_no' => $new_so])->row();
        $new_customer_order_no = $so_baru_data ? $so_baru_data->customer_order_no : NULL;

        // =================================================================
        // 4. EKSEKUSI PENGHAPUSAN LABEL (JIKA QTY TURUN)
        // =================================================================
        if (!empty($labels_to_delete) && count($labels_to_delete) > 0) {
            $this->db->where_in('id', $labels_to_delete);
            $this->db->delete('shipping_orders'); // Hard delete sesuai permintaan
        }

        // =================================================================
        // 5. UPDATE DELIVERY NOTES (DN)
        // =================================================================
        $update_dn = [
            'sales_order_no' => $new_so,
            'qty' => $new_qty
        ];
        if ($new_so !== $old_so && !empty($new_do)) {
            $update_dn['delivery_order_no'] = $new_do;
            $update_dn['customer_order_no'] = $new_customer_order_no;
        }
        $this->db->where('id', $id_dn);
        $this->db->update('delivery_notes', $update_dn);

        // =================================================================
        // 6. UPDATE DO, SHIPPING, & SCHEDULE BERDASARKAN SKENARIO
        // =================================================================
        if ($new_so !== $old_so && !empty($old_do) && !empty($new_do)) {
            
            // --- SKENARIO A: PINDAH SO ---
            
            // DO Lama menjadi Open
            $this->db->where('delivery_order_no', $old_do);
            $this->db->where('item_fg_id', $item_id);
            $this->db->update('delivery_orders', ['status' => 0, 'status_scan' => 0]);

            // DO Baru menjadi Close
            $this->db->where('delivery_order_no', $new_do);
            $this->db->where('item_fg_id', $item_id);
            $this->db->update('delivery_orders', ['status' => 1, 'status_scan' => 1]);

            // Schedule Lama -> Open (0)
            $this->db->where('sales_order_no', $old_so);
            $this->db->where('item_fg_id', $item_id);
            $this->db->where('trans_date', $trans_date);
            $this->db->update('sales_order_deliveries', ['status' => 0]);

            // Schedule Baru -> Close (1)
            $this->db->where('sales_order_no', $new_so);
            $this->db->where('item_fg_id', $item_id);
            $this->db->where('trans_date', $trans_date);
            $this->db->update('sales_order_deliveries', ['status' => 1]);

            // Pindahkan label fisik ke DO/SO Baru
            $this->db->where('delivery_order_no', $old_do);
            $this->db->where('item_fg_id', $item_id);
            $this->db->update('shipping_orders', [
                'sales_order_no' => $new_so,
                'delivery_order_no' => $new_do,
                'customer_order_no' => $new_customer_order_no
            ]);

        } else {
            
            // --- SKENARIO B: SO TETAP (HANYA UBAH QTY) ---
            
            if ($new_qty < $old_qty) {
                // SKENARIO QTY TURUN
                // DO: Update qty_del sesuai input
                $this->db->where('delivery_order_no', $old_do);
                $this->db->where('item_fg_id', $item_id);
                $this->db->update('delivery_orders', ['qty_del' => $new_qty]);

                // Schedule: Ubah status kembali Open (0) agar bisa dibuat DN lagi
                $this->db->where('sales_order_no', $old_so);
                $this->db->where('item_fg_id', $item_id);
                $this->db->where('trans_date', $trans_date);
                $this->db->update('sales_order_deliveries', ['status' => 0]);

            } elseif ($new_qty > $old_qty) {
                // SKENARIO QTY NAIK
                // DO: Update qty_del (Scanner akan memvalidasi sisa scan labelnya sendiri)
                $this->db->where('delivery_order_no', $old_do);
                $this->db->where('item_fg_id', $item_id);
                $this->db->update('delivery_orders', ['qty_del' => $new_qty]);
                
                // Schedule: Tidak dirubah (Tetap 1 / Close)
                // Shipping Orders: Tidak disentuh
            }
        }

        // =================================================================
        // 7. KALKULASI ULANG MASTER SO (DARI AKUMULASI DYNAMIC DN)
        // =================================================================
        $so_list_to_update = array_unique([$old_so, $new_so]); 
        
        foreach ($so_list_to_update as $so_no) {
            $so_master = $this->db->query("SELECT qty FROM sales_orders WHERE sales_order_no = ? AND item_fg_id = ?", [$so_no, $item_id])->row();
            
            if ($so_master) {
                $sum_dn = $this->db->query("SELECT COALESCE(SUM(qty), 0) as total_kirim FROM delivery_notes WHERE sales_order_no = ? AND item_fg_id = ?", [$so_no, $item_id])->row();
                
                $total_delivery = $sum_dn->total_kirim;
                $outstanding = (float)$so_master->qty - (float)$total_delivery;
                $status_so = ($outstanding <= 0) ? 1 : 0;

                $this->db->where('sales_order_no', $so_no);
                $this->db->where('item_fg_id', $item_id);
                $this->db->update('sales_orders', [
                    'delivery' => $total_delivery,
                    'outstanding' => $outstanding,
                    'status' => $status_so
                ]);
            }
        }

        // =================================================================
        // 8. SELESAIKAN TRANSAKSI
        // =================================================================
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => FALSE, 'message' => 'Failed! An error occurred during database execution.']);
        } else {
            echo json_encode(['status' => TRUE, 'message' => 'Success! Document changes and logistics recalculations are complete.']);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_status_delivery = @base64_decode($get['filter_status_delivery']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_status_invoice = @base64_decode($get['filter_status_invoice']);
            $filter_trans_type = @base64_decode($get['filter_trans_type']);
            $filter_division = @base64_decode($get['filter_division']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.division,
            a.created_by, 
            a.updated_by, 
            a.updated_date,
            a.created_date,
            a.delivery_note_no, 
            a.delivery_note_date, 
            a.note, 
            a.trans_type, 
            a.status, 
            a.address_id, 
            a.delivery_order_no, 
            a.sales_order_no, 
            a.sales_order_no_rm, 
            b.name as customer_name, 
            a.police_no,
            a.origin,
            a.sailing,
            a.customer_id,
            d.address as shipping_address");
            $this->db->from('delivery_notes a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('customer_address d', 'a.address_id = d.id','left');
            $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id','left');
            $this->db->join('sales_order_rm f', '(a.sales_order_no = f.sales_order_no OR a.sales_order_no IS NULL) AND a.item_fg_id = f.item_fg_id AND a.customer_id = f.customer_id', 'left');
            // $this->db->join('sales_invoices e', 'a.delivery_note_no = e.delivery_note_no and a.item_fg_id = c.item_fg_id','left');
            // $this->db->join('sales_invoices g', 'a.delivery_note_no = g.delivery_note_no and a.item_fg_id = f.item_fg_id','left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_note_date >=', $filter_from);
                $this->db->where('a.delivery_note_date <=', $filter_to);
            }

            $this->db->where("
                (CASE 
                    WHEN c.customer_order_no IS NULL THEN f.customer_order_no 
                    ELSE c.customer_order_no 
                END) LIKE '%" . $filter_customer_order_no . "%'"
            );

            $this->db->where("
                (CASE 
                    WHEN a.sales_order_no IS NULL THEN a.sales_order_no_rm 
                    ELSE a.sales_order_no 
                END) LIKE '%" . $filter_sales_order_no . "%'"
            );

            if ($filter_status !== "") { // Pastikan filter_status tidak kosong
                $this->db->where('a.status', $filter_status);
            }

            if ($filter_status_invoice !== "") { // Pastikan filter_status tidak kosong
                $this->db->where('a.status', $filter_status_invoice);
            }

            if ($filter_trans_type !== "") { // Pastikan filter_status tidak kosong
                $this->db->where('a.trans_type', $filter_trans_type);
            }

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }

            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            // $this->db->like('a.sales_order_no', $filter_sales_order_no);
            // $this->db->like('c.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.status_delivery', $filter_status_delivery);
            // $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_note_no');
            $this->db->order_by('a.status', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode((string) $this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name ,
            COALESCE(a.sales_order_no, a.sales_order_no_rm) as sales_order_number, 
            COALESCE(e.number, g.number) as sales_invoice_no');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id','left');
            $this->db->join('sales_order_rm f', '(a.sales_order_no = f.sales_order_no OR a.sales_order_no IS NULL) AND a.item_fg_id = f.item_fg_id AND a.customer_id = f.customer_id', 'left');
            $this->db->join('sales_invoices e', 'a.delivery_note_no = e.delivery_note_no and a.item_fg_id = c.item_fg_id','left');
            $this->db->join('sales_invoices g', 'a.delivery_note_no = g.delivery_note_no and a.item_fg_id = f.item_fg_id','left');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            // $this->db->order_by('b.number', 'ASC');
            $this->db->group_by('a.delivery_note_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.customer_order_no');
            $this->db->order_by('a.delivery_order_no');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode((string) $this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, a.address_id');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customer_address d', 'a.address_id = d.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    //CREATE DATA
    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();

    //         $delivery_notes = $this->crud->read("delivery_notes", [], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']]);
    //         $sales_order = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no']]);

    //         if (@$delivery_notes->delivery_order_no != "") {
    //             $send = $this->crud->update('delivery_notes', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], $post);
    //         } else {
    //             if($sales_order !== NULL){
    //                 $send = $this->crud->create('delivery_notes', $post);
    //             }else{
    //                 $dataFinal = array(
    //                     "customer_id" => $post['customer_id'],
    //                     "delivery_note_date" => $post['delivery_note_date'],
    //                     "delivery_note_no" => $post['delivery_note_no'],
    //                     "division" => $post['division'],
    //                     "address_id" => $post['address_id'],
    //                     "police_no" => $post['police_no'],
    //                     "driver_name" => $post['driver_name'],
    //                     "origin" => $post['origin'],
    //                     "sailing" => $post['sailing'],
    //                     "ship_by" => $post['ship_by'],
    //                     "incoterm" => $post['incoterm'],
    //                     "note" => $post['note'],
    //                     "status" => $post['status'],
    //                     "delivery_order_no" => $post['delivery_order_no'],
    //                     "item_fg_id" => $post['item_fg_id'],
    //                     "sales_order_no_rm" => $post['sales_order_no'],
    //                     "customer_order_no" => $post['customer_order_no'],
    //                     "trans_type" => $post['trans_type'],
    //                     "uom" => $post['uom'],
    //                     "status_delivery" => $post['status_delivery'],
    //                     "qty" => $post['qty'],
    //                     // "remarks" => $post['remarks'],
    //                 );
    //                 // $dataFinal = array_merge($post, [
    //                 //     "sales_order_no_rm" => $post['sales_order_no']
    //                 // ]);
    //                 $send = $this->crud->create('delivery_notes', $dataFinal);
    //             }
    //             $this->crud->update("delivery_orders", ["delivery_order_no" => $post['delivery_order_no']], ["status" => 1]);
    //         }

    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // Dokumen yang sudah dijurnal (Journal Inventory) tidak boleh diubah / dihapus
    private function _journaled_block($delivery_note_no)
    {
        $this->load->model('autopostingjournal');
        return $this->autopostingjournal->journaled_block(['DELIVERY NOTE'], $delivery_note_no);
    }

    // Dipanggil view setelah semua baris Save tersimpan. Scan ada di DO (Shipping Orders), DN hanya dari DO yang sudah full scan
    public function autoPostJournal()
    {
        $delivery_note_no = $this->input->post('delivery_note_no');
        if (empty($delivery_note_no)) {
            echo json_encode(['status' => false, 'message' => 'Delivery Note No is empty.']);
            return;
        }

        $this->load->model('autopostingjournal');
        $result = $this->autopostingjournal->auto_post_on_save('DELIVERY NOTE', $delivery_note_no);
        echo json_encode($result ?? ['status' => null, 'message' => 'No journal posting for this document.']);
    }

    public function create()//berubah
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            if ($blocked = $this->_journaled_block($post['delivery_note_no'] ?? '')) {
                echo json_encode($blocked + ['journaled' => true]);
                return;
            }

            $delivery_notes = $this->crud->read("delivery_notes", [], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']]);
            $sales_order = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no']]);
            
            $query = "SELECT SUM(qty) AS accum_qty_dn
                    FROM delivery_notes
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no = '{$post['sales_order_no']}'
                ";
            $result = $this->crud->query($query);
            $accum_qty_dn = $result[0]->accum_qty_dn ?? 0;

            $query2 = "SELECT SUM(qty) AS accum_qty_dn
                    FROM delivery_notes
                    WHERE item_fg_id = '{$post['item_fg_id']}' AND sales_order_no_rm = '{$post['sales_order_no']}'
                ";
            $result = $this->crud->query($query);
            $accum_qty_dn2 = $result[0]->accum_qty_dn2 ?? 0;

            // var_dump($qty_so);
            // var_dump($post['qty']);
            // var_dump($accum_qty_dn);
            // die;

            if (@$delivery_notes->delivery_order_no != "") {
                $send = $this->crud->update('delivery_notes', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], $post);
            } else {
                if($sales_order !== NULL){
                    $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
                    $qty_so = $sales_orders->qty;
                    
                    if (round($accum_qty_dn + $post['qty']) <= round($qty_so)) {
                        $send = $this->crud->createNotLog('delivery_notes', $post);
                        if (round($accum_qty_dn + $post['qty']) == round($qty_so)) {
                                $update = $this->crud->update('sales_orders', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], ["status" => 1]);
                            }
                    }else{
                        show_error("Qty DN > Qty SO");
                    }
                }else{
                    $dataFinal = array(
                        "customer_id" => $post['customer_id'],
                        "delivery_note_date" => $post['delivery_note_date'],
                        "delivery_note_no" => $post['delivery_note_no'],
                        "division" => $post['division'],
                        "address_id" => $post['address_id'],
                        "police_no" => $post['police_no'],
                        "driver_name" => $post['driver_name'],
                        "origin" => $post['origin'],
                        "sailing" => $post['sailing'],
                        "ship_by" => $post['ship_by'],
                        "incoterm" => $post['incoterm'],
                        "note" => $post['note'],
                        "status" => $post['status'],
                        "delivery_order_no" => $post['delivery_order_no'],
                        "item_fg_id" => $post['item_fg_id'],
                        "sales_order_no_rm" => $post['sales_order_no'],
                        "customer_order_no" => $post['customer_order_no'],
                        "trans_type" => $post['trans_type'],
                        "uom" => $post['uom'],
                        "status_delivery" => $post['status_delivery'],
                        "qty" => $post['qty'],
                        // "remarks" => $post['remarks'],
                    );
                    // $dataFinal = array_merge($post, [
                    //     "sales_order_no_rm" => $post['sales_order_no']
                    // ]);

                    $sales_order_rm = $this->crud->read("sales_order_rm", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
                    $qty_so_rm = $sales_order_rm->qty;
        
                    if (round($accum_qty_dn2 + $post['qty']) <= round($qty_so_rm)) {
                        $send = $this->crud->createNotLog('delivery_notes', $dataFinal);
                        if (round($accum_qty_dn2 + $post['qty']) == round($qty_so_rm)) {
                                $update = $this->crud->update('sales_order_rm', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], ["status" => 1]);
                            }
                    }else{
                        show_error("Qty DN > Qty SO");
                    }
                }
                $this->crud->update("delivery_orders", ["delivery_order_no" => $post['delivery_order_no']], ["status" => 1]);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        if ($blocked = $this->_journaled_block($data['delivery_note_no'] ?? '')) {
            echo json_encode($blocked);
            return;
        }
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["delivery_note_no" => $data['delivery_note_no']]);

        $send = $this->crud->delete('delivery_notes', ["delivery_note_no" => $data['delivery_note_no']]);
        foreach ($delivery_notes as $delivery_note) {
            $update = $this->crud->update('delivery_orders', ["delivery_order_no" => $delivery_note->delivery_order_no], ["status" => "0"]);
            if(!empty($sales_order_nos)){
                $update = $this->crud->update('sales_orders', ["sales_order_no" => $data['sales_order_no']], ["status" => "0"]);
            }else{
                $update = $this->crud->update('sales_order_rm', ["sales_order_no" => $data['sales_order_no_rm']], ["status" => "0"]);
            }
        }
        echo $send;
    }

    public function print_dn($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);

        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        $delivery_note = $this->crud->read('delivery_notes', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_notes = $delivery_note->delivery_note_no;

        // var_dump($delivery_notes);
        // var_dump($delivery_order_no);

        $approval = $this->crud->read('approvals', [], ["table_name" => "delivery_notes"]);
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);
       
        if (!empty($approval->user_approval_2)) {
            $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        } else {
            $user_2 = (object) ["name" => ""];
        }
        
        if($delivery_note->approved == 0){
            $users_1 = '';
            $users_2 = '';
        } elseif ($delivery_note->approved == 1) {
            $users_1 = '';
            $users_2 = '';
        } elseif ($delivery_note->approved == 2) {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="100"/>';
            $users_2 = '';
        } else {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="100"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="100"/>';
        }

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 20;//25
        $page = ceil(count($delivery_orders) / $rows);
        //Generate QRcode
        // $this->createQrcode($delivery_notes, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_notes . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 10 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            // $this->db->select('a.*, b.id as item_id, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, 
            //     (CASE WHEN d.customer_order_no is null THEN g.customer_order_no ELSE d.customer_order_no END) as customer_order_no, 
            //     e.delivery_note_no, f.address, e.origin, e.sailing, e.ship_by, e.incoterm, e.police_no, f.address_billing, f.telp, 
            //     a.created_date as created_date, e.note');
            // $this->db->from('delivery_orders a');
            // $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            // $this->db->join('customers c', 'a.customer_id = c.id');
            // $this->db->join('sales_orders d', 'a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id and a.customer_id = d.customer_id', 'left');
            // $this->db->join('sales_order_rm g', '(a.sales_order_no_rm = g.sales_order_no OR a.sales_order_no IS NULL) AND a.item_fg_id = g.item_fg_id AND a.customer_id = g.customer_id', 'left');
            // $this->db->join('delivery_notes e', 'a.delivery_order_no = e.delivery_order_no and a.customer_order_no = e.customer_order_no', 'left');
            // $this->db->join('customer_address f', 'e.address_id = f.id','left');
            // $this->db->where('a.delivery_order_no', $delivery_order_no);
            // $this->db->where('a.qty_del !=', 0);
            // $this->db->group_by('a.delivery_order_no, b.number, b.name, c.name, customer_order_no, 
            //     e.delivery_note_no, f.address, e.origin, e.sailing, e.ship_by, e.incoterm, e.police_no, f.address_billing, f.telp, a.created_date, e.note');
            // $this->db->order_by('b.number', 'asc');
            // $this->db->limit(25, ($i * 25));
            // $records = $this->db->get()->result_array();


            $this->db->select('b.id as item_id, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, 
            a.customer_order_no, a.delivery_note_date, a.trans_type, 
            a.delivery_note_no, f.address, a.origin, a.sailing, a.ship_by, a.incoterm, a.police_no, f.address_billing, f.telp, 
            a.created_date as created_date, a.note, a.sales_order_no, a.uom, a.remarks, a.qty, a.division, f.contact_person');
            $this->db->select('b.hs_code');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('sales_orders d', 'a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id and a.customer_id = d.customer_id', 'left');
            $this->db->join('sales_order_rm g', '(a.sales_order_no_rm = g.sales_order_no OR a.sales_order_no IS NULL) AND a.item_fg_id = g.item_fg_id AND a.customer_id = g.customer_id', 'left');
            $this->db->join('customer_address f', 'a.address_id = f.id','left');
            // $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->where('a.delivery_note_no', $delivery_notes);
            $this->db->where('a.qty !=', 0);
            //$this->db->group_by('a.delivery_order_no', $delivery_order_no);
            $this->db->group_by('a.delivery_order_no, b.number, b.name, c.name, 
            a.delivery_note_no, f.address, a.origin, a.sailing, a.ship_by, a.incoterm, a.police_no, f.address_billing, f.telp, a.created_date, a.note');
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(20, ($i * 20));//25
            $records = $this->db->get()->result_array();
            // <img src="' . base_url('assets/image/qrcode/' . $delivery_notes . '.png') . '" width="60" style="margin-right:5px;" />

            $html .= '<div style="border: 0px solid black; width:100%; height:70%;">
            <div style="padding:10px; position:relative; top:10px;">
                <div style="text-align:center;">
                    <h3 style="margin:0;">DELIVERY NOTE</h3>
                </div>
                <br><br><br><br>
                <div style="position:absolute; top:10px; right:5px; display:flex; align-items:center;">
                    <div>
                       
                    </div>
                    <div>
                        <table style="width:50%; font-size:10px; text-align:left;">
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
                    </div>
                </div>
                <br><br><br><br> <br><br><br>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Customer Name</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['customer_name'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Ship To</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['address'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Bill To</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['address_billing'] . '</td>
                        </tr>
                            <tr>
                            <td width="100">Country Of Origin</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['origin'] . '</td>
                        </tr>
                        <tr>
                            <td width="150">Sailing On Or About to</td>
                            <td width="5">:</td>
                            <td><b>' . @$records[0]['sailing'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Attention</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['contact_person'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Phone No.</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['telp'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Police No.</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['police_no'] . '</td>
                        </tr>
                    </table>
                </div>
                <div style="float:right; width:40%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Delivery Note No</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['delivery_note_no'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Transaction Type</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['trans_type'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Ship By</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_by'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Incoterms</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['incoterm'] . '</b></td>
                        </tr>
                        <tr>
                            <td width="100">Delivery Date</td>
                            <td width="5">:</td>
                            <td>' . date("d F Y", strtotime(@$records[0]['delivery_note_date'])) . '</td>
                        </tr>
                        <tr>
                            <td width="100">Created Date</td>
                            <td width="5">:</td>
                            <td>' . date("d F Y", strtotime(@$records[0]['created_date'])) . '</td>
                        </tr>
                        <tr>
                            <td width="100">Note</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['note'] . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers">
                    <tr>
                        <th width="20">No</th>
                        <th>Product No</th>
                        <th>HS Code (INSW)</th>
                        <th>Product Name</th>
                        <th>UoM</th>
                        <th width="60">Qty</th>
                        <th width="150">Customer Order No</th>
                        <th width="100">Remark</th>
                    </tr>';

            $total_qty = 0;
            $no = 1;
            foreach ($records as $record) {

                if($record['sales_order_no'] == NULL){
                    $product = $record['item_id'];
                    $name = "Indraswari Syahri";
                }elseif($record['division'] == 'MTS'){
                    $product = $record['item_fg_number'];
                    $name = "Silvia Indah";
                }else{
                    $product = $record['item_fg_number'];
                    $name = "Arik Rosdiana";
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $product . '</td>
                                <td>' . $record['hs_code'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format((float) $record['qty'], 2, ",", ".") . '</td>
                                <td>' . $record['customer_order_no'] . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $total_qty += $record['qty'];
                $no++;
            }
            $html .= '<tr>
            <td colspan="5" style="text-align:right; font-weight:bold;">Total</td>
            <td style="text-align:right; font-weight:bold;">' . number_format((float) $total_qty, 2, ",", ".") . '</td>
            <td colspan="2"></td>
          </tr>';

        $html .= '</table>';

        if ($i + 1 != $page) {
            $html .= '<div style="page-break-after:always;"></div>';
        }

            $html .= '</div></div>';
            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="200" style="text-align:center;">CUSTOMER STAMP & SIGNATURE By</th>
                                        <th width="200" style="text-align:center;">TRANSPORTER</th>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURES</th>
                                        <th width="200" style="text-align:center;">DELIVERY CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:120px;"></th>
                                        <th style="height:120px;"></th>
                                        <th style="height:120px;">'. $users_2. '</th>
                                        <th style="height:120px;">'. $users_1. '</th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;">' . $user_2->name . '</th>
                                        <th style="height:20px; text-align:center;">' . $name . '</th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', 0);

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_notes_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status_delivery = @base64_decode($get['filter_status_delivery']);
        $filter_status = @base64_decode($get['filter_status']);
        $filter_status_invoice = @base64_decode($get['filter_status_invoice']);
        $filter_trans_type = @base64_decode($get['filter_trans_type']);
        $filter_division = @base64_decode($get['filter_division']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, d.address as shipping_address,
        h.number as item_fg_number, h.name as item_fg_name ,
        (CASE WHEN a.sales_order_no is null THEN a.sales_order_no_rm ELSE a.sales_order_no END) as sales_order_no");
        $this->db->select('h.hs_code');
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address d', 'a.address_id = d.id','left');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id','left');
        $this->db->join('sales_order_rm f', '(a.sales_order_no = f.sales_order_no OR a.sales_order_no IS NULL) AND a.item_fg_id = f.item_fg_id AND a.customer_id = f.customer_id', 'left');
        $this->db->join('item_fg h', 'a.item_fg_id = h.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_note_date >=', $filter_from);
            $this->db->where('a.delivery_note_date <=', $filter_to);
        }

        $this->db->where("
            (CASE 
                WHEN c.customer_order_no IS NULL THEN f.customer_order_no 
                ELSE c.customer_order_no 
            END) LIKE '%" . $filter_customer_order_no . "%'"
        );

        $this->db->where("
            (CASE 
                WHEN a.sales_order_no IS NULL THEN a.sales_order_no_rm 
                ELSE a.sales_order_no 
            END) LIKE '%" . $filter_sales_order_no . "%'"
        );

        if ($filter_status !== "") { // Pastikan filter_status tidak kosong
            $this->db->where('a.status', $filter_status);
        }

        if ($filter_status_invoice !== "") { // Pastikan filter_status tidak kosong
            $this->db->where('a.status', $filter_status_invoice);
        }

        if ($filter_trans_type !== "") { // Pastikan filter_status tidak kosong
            $this->db->where('a.trans_type', $filter_trans_type);
        }

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }


        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
        // $this->db->like('a.sales_order_no', $filter_sales_order_no);
        // $this->db->like('c.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        // $this->db->like('a.status', $filter_status);
        $this->db->group_by('a.delivery_note_no');
        $this->db->group_by('a.delivery_order_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('a.status', 'ASC');

        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>DELIVERY NOTES</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Delivery Note No</th>
                <th>Delivery Note Date</th>
                <th>Delivery Order No</th>
                <th>Product ID</th>
                <th>HS Code</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Sales Order No</th>
                <th>Customer Order No</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Shipping Address</th>
                <th>Trans Type</th>
                <th>Note</th>
                <th>Delivery Status</th>
                <th>Status</th>
              
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['delivery_note_no'] . '</td>
                        <td>' . $data['delivery_note_date'] . '</td>
                        <td>' . $data['delivery_order_no'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['hs_code'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['shipping_address'] . '</td>
                        <td>' . $data['trans_type'] . '</td>
                        <td>' . $data['note'] . '</td>
                        <td>' . $data['status_delivery'] . '</td>
                        <td>' . $data['status'] . '</td>
                       
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
