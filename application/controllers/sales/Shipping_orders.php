<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_orders extends CI_Controller
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
            $this->load->view('sales/shipping_orders');
        } else {
            redirect('error_access');
        }
    }

    // public function getDeliveryOrders()
    // {
    //     if ($this->input->get()) {
    //         $delivery_order_no = $this->input->get('delivery_order_no');
    //         $this->db->select('a.customer_order_no, a.delivery_order_no, a.delivery_order_date, a.uom, 
    //                COALESCE(a.sales_order_no, a.sales_order_no_rm) as sales_order_no,  
    //                a.trans_type, a.remarks, b.number as item_fg_number, b.name as item_fg_name, 
    //                c.name as customer_name, a.qty_del as delivery, a.created_by, a.created_date, 
    //                COALESCE(e.shipping, f.additional_shipping) as shipping');
    //         $this->db->from('delivery_orders a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->join('customers c', 'a.customer_id = c.id');
    //         $this->db->join("(SELECT 
    //                             a.delivery_order_no, 
    //                             b.item_fg_id, 
    //                             COALESCE(SUM(a.qty), 0) AS shipping
    //                         FROM shipping_orders a

    //                         JOIN (SELECT DISTINCT checksheet_label, item_fg_id 
    //                             FROM scan_item_receipts_fg) b 
    //                         ON a.checksheet_label = b.checksheet_label

    //                         JOIN (SELECT DISTINCT sales_order_no 
    //                             FROM (SELECT sales_order_no FROM sales_orders 
    //                                     UNION ALL 
    //                                     SELECT sales_order_no FROM sales_order_rm) combined_sales_orders) c 
    //                         ON a.sales_order_no = c.sales_order_no
    //                         WHERE a.delivery_order_no = '$delivery_order_no'
    //                         GROUP BY a.delivery_order_no, b.item_fg_id
    //                         ) e", 'a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id', 'left'
    //                     );

    //         $this->db->join("(SELECT 
    //                     a.delivery_order_no, 
    //                     a.item_fg_id, 
    //                     COALESCE(SUM(a.qty), 0) AS additional_shipping
    //                     FROM shipping_orders a
    //                     JOIN barcode_divides_fg b 
    //                     ON a.checksheet_label = b.label_divided
    //                     WHERE a.delivery_order_no = '$delivery_order_no'
    //                     GROUP BY a.delivery_order_no, a.item_fg_id
    //                     ) f", 'a.delivery_order_no = f.delivery_order_no AND a.item_fg_id = f.item_fg_id', 'left'
    //                     );
    
    //         $this->db->where('a.delivery_order_no', $delivery_order_no);
    //         $this->db->group_by('b.number');

    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $delivery_order_no = $this->input->get('delivery_order_no');
            $this->db->select('a.customer_order_no, a.delivery_order_no, a.delivery_order_date, a.uom, 
                COALESCE(a.sales_order_no, a.sales_order_no_rm) as sales_order_no,  
                a.trans_type, a.remarks, b.number as item_fg_number, b.name as item_fg_name, 
                c.name as customer_name, a.qty_del as delivery, a.created_by, a.created_date, 
                                COALESCE(f.additional_shipping, e.shipping) AS shipping
            ');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join("(SELECT 
                                a.delivery_order_no, 
                                b.item_fg_id, 
                                SUM(a.qty) AS shipping
                            FROM shipping_orders a
                            LEFT JOIN (SELECT DISTINCT checksheet_label, item_fg_id 
                                        FROM scan_item_receipts_fg) b 
                            ON a.checksheet_label = b.checksheet_label
                            WHERE a.delivery_order_no = '$delivery_order_no'
                            GROUP BY a.delivery_order_no, b.item_fg_id
                            ) e", 'a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id', 'left');

            $this->db->join("(SELECT 
                                a.delivery_order_no, 
                                a.item_fg_id, 
                                SUM(a.qty) AS additional_shipping
                            FROM shipping_orders a
                            LEFT JOIN barcode_divides_fg b 
                            ON a.checksheet_label = b.label_divided
                            WHERE a.delivery_order_no = '$delivery_order_no'
                            GROUP BY a.delivery_order_no, a.item_fg_id
                            ) f", 'a.delivery_order_no = f.delivery_order_no AND a.item_fg_id = f.item_fg_id', 'left');

            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->group_by(['a.delivery_order_no', 'a.item_fg_id', 'b.number']);

            // Untuk debugging, tambahkan kolom hasil subquery
            $this->db->select('e.shipping AS shipping_e, f.additional_shipping AS shipping_f');

            // Eksekusi query
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            // Hasil JSON
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }


    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $delivery_order_no = $this->input->post('delivery_order_no');

            $this->db->select("qty, wo_no, '0' as delivery , item_fg_id");
            $this->db->from('scan_item_receipts_fg');
            $this->db->where('checksheet_label', $checksheet_label);
            $this->db->group_by('checksheet_label');

            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if(!$records){
                $this->db->select("COALESCE(a.qty, 0) as qty, a.item_fg_id, '0' as delivery");
                $this->db->from('new_barcode_fg a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->where('a.label_no', $checksheet_label);
                $this->db->where('a.label_type !=', 'manual');
                $this->db->group_by('a.label_no');

                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if(!$records){
                    $this->db->select("a.qty, c.item_fg_id, '0' as delivery");
                    $this->db->from('barcode_divides_fg a');
                    $this->db->join('wip_receipt_labels b', 'a.reff = b.checksheet_label');
                    $this->db->join('checksheets c', 'b.checksheet_number = c.number');
                    $this->db->join('item_fg d', 'c.item_fg_id = d.id');
                    $this->db->where('a.label_divided', $checksheet_label);

                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if(!$records){
                        $this->db->select("a.qty, c.item_fg_id, '0' as delivery");
                        $this->db->from('barcode_divides_fg a');
                        $this->db->join('wip_receipt_boxs b', 'a.reff = b.checksheet_label');
                        $this->db->join('checksheets c', 'b.checksheet_number = c.number');
                        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
                        $this->db->where('a.label_divided', $checksheet_label);
                        // $this->db->where('a.type', 'SUPPLY');

                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();

                        if(!$records){
                            $this->db->select("a.qty, b.item_fg_id, '0' as delivery");
                            $this->db->from('barcode_divides_fg a');
                            $this->db->join('new_barcode_fg b', 'a.reff = b.label_no');
                            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
                            $this->db->where('a.label_divided', $checksheet_label);
                            // $this->db->where('a.type', 'SUPPLY');

                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
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

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();

    //             $delivery_order_no = $post['delivery_order_no'];
    //             $item_fg_id = $post['item_fg_id'];
    //             //$shipping_orders = $this->crud->read("shipping_orders", [], ["delivery_order_no" => $post['delivery_order_no'], "checksheet_label" => $post['checksheet_label']]);
    //             $shipping_orders = $this->crud->read("shipping_orders", [], ["checksheet_label" => $post['checksheet_label']]);
    //             $do_no = $this->crud->read("delivery_orders",[], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id']]);                
    //             $totalShipping = $this->crud->query("SELECT SUM(qty) as qty FROM shipping_orders WHERE delivery_order_no = '$delivery_order_no' and item_fg_id = '$item_fg_id'");
                
    //             $this->db->select("a.*");
    //             $this->db->from('scan_item_receipts_fg a');
    //             $this->db->join('wip_receipts d', 'a.checksheet_number = d.checksheet_number','left');
    //             $this->db->join('sales_orders b', 'd.item_fg_id = b.item_fg_id','left');
    //             $this->db->join('delivery_orders c', 'b.item_fg_id = c.item_fg_id and c.customer_id = b.customer_id','left');//ok
    //             $this->db->where('a.checksheet_label', $post['checksheet_label']);
    //             $this->db->group_by('a.checksheet_label');
    //             $label_items = $this->db->get()->result_array();

    //             // var_dump($post['qty']);
    //             // var_dump($totalShipping[0]->qty);
    //             // var_dump($do_no->qty_del);

    //             if ($label_items) {
    //                 if (!$shipping_orders) {

    //                     if(round($post['qty'] + $totalShipping[0]->qty) <= round($do_no->qty_del)){
    //                         $send = $this->crud->createNotLog('shipping_orders', $post);
    //                         $this->crud->updateNotlog('scan_item_receipts_fg', ["checksheet_label" => $post['checksheet_label']], ["status" => "1"]);
    //                         if(round($post['qty'] + $totalShipping[0]->qty) == round($do_no->qty_del)){
    //                             $this->crud->updateNotlog('delivery_orders', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id']], ["status_scan" => "1"]);
    //                         }
    //                         echo $send;
    //                     } else {
    //                         echo json_encode(array("title" => "More Then Qty", "message" => "Qty Label > Qty DO ", "theme" => "error"));
    //                     }
    //                 } else {
    //                     echo json_encode(array("title" => "Available", "message" => "Label has been scan for Other Shipping, Please Scan with other Label", "theme" => "error"));
    //                 }
    //             } else {
    //                 $this->db->select("e.*");
    //                 $this->db->from('barcode_divides_fg e');
    //                 $this->db->where('e.label_divided', $post['checksheet_label']);
    //                 $barcode_items = $this->db->get()->result_array(); // Query kedua
                
    //                 if ($barcode_items) {
    //                     if (!$shipping_orders) {
    //                         if(round($post['qty'] + $totalShipping[0]->qty) <= round($do_no->qty_del)){
    //                             $send = $this->crud->createNotLog('shipping_orders', $post);
    //                             $this->crud->updateNotlog('barcode_divides_fg', ["label_divided" => $post['checksheet_label']], ["status" => "1"]);
    //                             if(round($post['qty'] + $totalShipping[0]->qty) == round($do_no->qty_del)){
    //                                 $this->crud->updateNotlog('delivery_orders', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id']], ["status_scan" => "1"]);
    //                             }                                
    //                             echo $send;
    //                         } else {
    //                             echo json_encode(array("title" => "More Then Qty", "message" => "Qty Label > Qty DO ", "theme" => "error"));
    //                         }
    //                     } else {
    //                         echo json_encode(array("title" => "Available", "message" => "Label has been scan for Other Shipping, Please Scan with other Label", "theme" => "error"));
    //                     }
    //                 } else {
    //                     echo json_encode(array("title" => "Not Match", "message" => "Label does not match the list item", "theme" => "error"));
    //                 }
    //             }
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function create()//dokumentasi : Optimasi Create dengan Race Condition
    // {
    //     if (!$this->input->post()) {
    //         echo json_encode([
    //             "title" => "Error",
    //             "message" => "Cannot Process your request",
    //             "theme" => "error"
    //         ]);
    //         return;
    //     }

    //     if ($this->form_validation->run() !== TRUE) {
    //         echo json_encode(["title" => "Error","message" => validation_errors(),"theme" => "error"]);
    //         return;
    //     }

    //     $post = $this->input->post();
    //     $delivery_order_no = $post['delivery_order_no'];
    //     $item_fg_id        = $post['item_fg_id'];
    //     $label             = $post['checksheet_label'];

    //     $this->db->trans_begin();

    //     try {

    //         /** LOCK DO */
    //         $do = $this->db->query(
    //             "SELECT * FROM delivery_orders 
    //             WHERE delivery_order_no = ? 
    //             AND item_fg_id = ? 
    //             FOR UPDATE",
    //             [$delivery_order_no, $item_fg_id]
    //         )->row();

    //         if (!$do) {
    //             throw new Exception("Item not found in Delivery Order", 404);
    //         }

    //         /** DUPLICATE LABEL */
    //         $exists = $this->crud->read("shipping_orders", [], ["checksheet_label" => $label]);
    //         if ($exists) {
    //             throw new Exception("Data Shipping Orders has been Scanning", 409);
    //         }

    //         /** VALIDASI LABEL */
    //         $label_type = null;

    //         $receipt = $this->db->where('checksheet_label', $label)
    //                             ->get('scan_item_receipts_fg')
    //                             ->row();

    //         if ($receipt) {
    //             $label_type = 'receipt';
    //         } else {
    //             $barcode = $this->db->where('label_divided', $label)
    //                                 ->get('barcode_divides_fg')
    //                                 ->row();

    //             if ($barcode) {
    //                 $label_type = 'barcode';
    //             } else {
    //                 throw new Exception("Label does not match the list item", 400);
    //             }
    //         }

    //         /** INSERT SHIPPING */
    //         $this->crud->createNotLog('shipping_orders', $post);

    //         /** UPDATE LABEL */
    //         if ($label_type === 'receipt') {
    //             $this->crud->updateNotlog('scan_item_receipts_fg',['checksheet_label' => $label],['status' => '1']);
    //         } else {
    //             $this->crud->updateNotlog('barcode_divides_fg',['label_divided' => $label],['status' => '1']);
    //         }

    //         /** TOTAL QTY */
    //         $total = $this->db->query(
    //             "SELECT COALESCE(SUM(qty),0) qty 
    //             FROM shipping_orders 
    //             WHERE delivery_order_no = ? 
    //             AND item_fg_id = ?",
    //             [$delivery_order_no, $item_fg_id]
    //         )->row()->qty;

    //         if (!$this->decimal_lte($total, $do->qty_del, 2)) {
    //             throw new Exception("Qty Label > Qty DO", 422);
    //         }

    //         if ($this->decimal_equal($total, $do->qty_del, 2)) {
    //             $this->crud->updateNotlog('delivery_orders',['delivery_order_no' => $delivery_order_no, 'item_fg_id' => $item_fg_id],['status_scan' => '1']);
    //         }

    //         $this->db->trans_commit();

    //         echo json_encode([
    //             "title" => "Success",
    //             "message" => "Shipping order created",
    //             "theme" => "success"
    //         ]);

    //     } catch (Exception $e) {

    //         $this->db->trans_rollback();

    //         /** Mapping code → title frontend */
    //         $titleMap = [
    //             404 => "Not Registered",
    //             400 => "Not Scanned In",
    //             409 => "Available",
    //             422 => "More Then Qty"
    //         ];

    //         $title = $titleMap[$e->getCode()] ?? "Error";

    //         echo json_encode([
    //             "title" => $title,
    //             "message" => $e->getMessage(),
    //             "theme" => "error"
    //         ]);
    //     }
    // }

    function decimal_equal($a, $b, $scale = 2)
    {
        return bccomp((string)$a, (string)$b, $scale) === 0;
    }

    function decimal_lte($a, $b, $scale = 2)
    {
        return bccomp((string)$a, (string)$b, $scale) <= 0;
    }

    public function create()//dokumentasi : penambahan fitur FIFO
    {
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error","message" => "Cannot Process your request","theme" => "error"]);
            return;
        }

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["title" => "Error","message" => validation_errors(),"theme" => "error"]);
            return;
        }

        $post = $this->input->post();
        $delivery_order_no = $post['delivery_order_no'];
        $item_fg_id        = $post['item_fg_id'];
        $label             = $post['checksheet_label'];

        $this->db->trans_begin();

        try {

            /** LOCK DO */
            $do = $this->db->query(
                "SELECT * FROM delivery_orders 
                WHERE delivery_order_no = ? AND item_fg_id = ? FOR UPDATE",
                [$delivery_order_no, $item_fg_id]
            )->row();

            if (!$do) { throw new Exception("Item not found in Delivery Order", 404); }

            /** DUPLICATE LABEL */
            $exists = $this->crud->read("shipping_orders", [], ["checksheet_label" => $label]);
            if ($exists) { throw new Exception("Data Shipping Orders has been Scanning", 409); }

            /** =========================================================
             * 1. PENCARI JEJAK: Temukan Packing Date dari label yang di-scan
             * ========================================================= */
            $current_packing_date = null;
            $table_to_update = null;
            $column_to_update = null;

            // A. Cek di New Barcode FG (Tambah filter != manual)
            $nbfg = $this->db->select('packing_date')
                             ->where('label_no', $label)
                             ->where('label_type !=', 'manual') 
                             ->get('new_barcode_fg')->row();
                             
            if ($nbfg) {
                $current_packing_date = $nbfg->packing_date;
                $table_to_update = 'new_barcode_fg';
                $column_to_update = 'label_no';
            } 
            else {
                // B. Cek di Barcode Divides FG
                $divide = $this->db->where('label_divided', $label)->get('barcode_divides_fg')->row();
                if ($divide) {
                    $table_to_update = 'barcode_divides_fg';
                    $column_to_update = 'label_divided';
                    
                    // Cari Induknya (Checksheet atau New Barcode?)
                    $parent_cs = $this->db->query("
                        SELECT c.packing_date FROM checksheets c
                        JOIN (
                            SELECT checksheet_number, checksheet_label FROM wip_receipt_boxs
                            UNION ALL
                            SELECT checksheet_number, checksheet_label FROM wip_receipt_labels
                        ) w ON w.checksheet_number = c.number
                        WHERE w.checksheet_label = ?
                    ", [$divide->reff])->row();
                    
                    if ($parent_cs) { $current_packing_date = $parent_cs->packing_date; } 
                    else {
                        // Tambah filter != manual di sini juga
                        $parent_nb = $this->db->select('packing_date')
                                              ->where('label_no', $divide->reff)
                                              ->where('label_type !=', 'manual')
                                              ->get('new_barcode_fg')->row();
                                              
                        if ($parent_nb) { $current_packing_date = $parent_nb->packing_date; }
                    }
                } 
                else {
                    // C. Cek di Scan Item Receipts (Undivided Checksheet Label)
                    $cs_label = $this->db->query("
                        SELECT c.packing_date FROM checksheets c
                        JOIN (
                            SELECT checksheet_number, checksheet_label FROM wip_receipt_boxs
                            UNION ALL
                            SELECT checksheet_number, checksheet_label FROM wip_receipt_labels
                        ) w ON w.checksheet_number = c.number
                        WHERE w.checksheet_label = ?
                    ", [$label])->row();
                    
                    if ($cs_label) {
                        $current_packing_date = $cs_label->packing_date;
                        $table_to_update = 'scan_item_receipts_fg';
                        $column_to_update = 'checksheet_label';
                    }
                }
            }

            if (!$current_packing_date) {
                throw new Exception("Label origin or Packing Date not found (Or Label is Manual)!", 400);
            }

            /** =========================================================
             * 2. VALIDASI FIFO (UNIVERSAL QUERY UNION)
             * ========================================================= */
            $fifo_violation = $this->db->query("
                SELECT source_label, packing_date, doc_no 
                FROM (
                    -- Sumber 1: Checksheet Utuh (Box & Labels)
                    SELECT 
                        w.checksheet_label AS source_label, 
                        c.packing_date, 
                        c.number AS doc_no
                    FROM (
                        SELECT checksheet_number, checksheet_label FROM wip_receipt_boxs
                        UNION ALL
                        SELECT checksheet_number, checksheet_label FROM wip_receipt_labels
                    ) w
                    JOIN checksheets c ON w.checksheet_number = c.number
                    WHERE c.item_fg_id = ?
                    
                    UNION ALL
                    
                    -- Sumber 2: New Barcode Utuh (Filter != manual)
                    SELECT 
                        n.label_no AS source_label, 
                        n.packing_date, 
                        'NEW BARCODE' AS doc_no
                    FROM new_barcode_fg n
                    WHERE n.item_fg_id = ?
                    AND n.label_type != 'manual'
                    
                    UNION ALL
                    
                    -- Sumber 3: Barcode Divides (Pecahan dari Checksheet)
                    SELECT 
                        d.label_divided AS source_label, 
                        c.packing_date, 
                        c.number AS doc_no
                    FROM barcode_divides_fg d
                    JOIN (
                        SELECT checksheet_number, checksheet_label FROM wip_receipt_boxs
                        UNION ALL
                        SELECT checksheet_number, checksheet_label FROM wip_receipt_labels
                    ) w ON d.reff = w.checksheet_label
                    JOIN checksheets c ON w.checksheet_number = c.number
                    WHERE c.item_fg_id = ?
                    
                    UNION ALL
                    
                    -- Sumber 4: Barcode Divides (Pecahan dari New Barcode, Filter != manual)
                    SELECT 
                        d.label_divided AS source_label, 
                        n.packing_date, 
                        'NEW BARCODE' AS doc_no
                    FROM barcode_divides_fg d
                    JOIN new_barcode_fg n ON d.reff = n.label_no
                    WHERE n.item_fg_id = ?
                    AND n.label_type != 'manual'
                ) AS all_stok
                WHERE packing_date < ?
                AND source_label NOT IN (SELECT checksheet_label FROM shipping_orders)
                AND source_label NOT IN (SELECT reff FROM barcode_divides_fg) 
                ORDER BY packing_date ASC
                LIMIT 1
            ", [$item_fg_id, $item_fg_id, $item_fg_id, $item_fg_id, $current_packing_date])->row();

            if ($fifo_violation) {
                $old_date = date('d-M-Y', strtotime($fifo_violation->packing_date));
                $old_doc = $fifo_violation->doc_no;
                $old_label = $fifo_violation->source_label;
                throw new Exception("FIFO Alert! Please scan in sequence Packing Date : {$old_date} | Doc: {$old_doc} | Label: {$old_label}", 406);
            }

            /** =========================================================
             * 3. INSERT & UPDATE
             * ========================================================= */
            $this->crud->createNotLog('shipping_orders', $post);

            // Update status label secara dinamis berdasarkan tabel asalnya
            if ($table_to_update) {
                $this->crud->updateNotlog($table_to_update, [$column_to_update => $label], ['status' => '1']);
            }

            /** TOTAL QTY */
            $total = $this->db->query(
                "SELECT COALESCE(SUM(qty),0) qty 
                FROM shipping_orders 
                WHERE delivery_order_no = ? 
                AND item_fg_id = ?",
                [$delivery_order_no, $item_fg_id]
            )->row()->qty;

            if (!$this->decimal_lte($total, $do->qty_del, 2)) {
                throw new Exception("Qty Label > Qty DO", 422);
            }

            if ($this->decimal_equal($total, $do->qty_del, 2)) {
                $this->crud->updateNotlog('delivery_orders',['delivery_order_no' => $delivery_order_no, 'item_fg_id' => $item_fg_id],['status_scan' => '1']);
            }

            $this->db->trans_commit();

            echo json_encode(["title" => "Success", "message" => "Shipping order created", "theme" => "success"]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $titleMap = [
                404 => "Not Registered",
                400 => "Not Scanned In",
                406 => "FIFO Violation", // Alarm FIFO!
                409 => "Available",
                422 => "More Then Qty"
            ];

            $title = $titleMap[$e->getCode()] ?? "Error";

            echo json_encode(["title" => $title, "message" => $e->getMessage(), "theme" => "error"]);
        }
    }
}
