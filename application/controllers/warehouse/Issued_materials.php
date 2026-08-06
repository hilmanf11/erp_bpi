<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_materials extends CI_Controller
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
        // $this->form_validation->set_rules('item_fg_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_materials');
        } else {
            redirect('error_access');
        }
    }

    public function readItemRm()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT id, number, name, uom 
        FROM item_rm 
        WHERE item_category_id = 'C11' AND item_family_id IN ('P05', 'P33') AND (`number` LIKE '%$post%' OR `name` LIKE '%$post%')
        ORDER BY `number` ASC");
        echo json_encode($records);
    }

    public function readItemRmByIds($encodedIds) {
        $decodedIds = json_decode(base64_decode($encodedIds), true);
        // var_dump($decodedIds);
        
        $this->db->select('id, number, name ');
        $this->db->from('item_rm');
        $this->db->where_in('id', $decodedIds);
        $query = $this->db->get();
        
        echo json_encode($query->result());
    }

    public function getCRItem()
    {
        $item_rm_id = $this->input->post('item_rm_id');

        // var_dump($item_rm_id);
        // return;

        // Cari item dengan prefix 'CR-'
        $query_cr = $this->db->query("
            SELECT id as item_rm_id, number as item_rm_no, name as item_rm_name, uom
            FROM item_rm
            WHERE number LIKE CONCAT('CR-', (
                SELECT number
                FROM item_rm
                WHERE id = ?
            ))
        ", array($item_rm_id));

        $cr_result = $query_cr->row_array();

        // Cari item dengan prefix 'PL-'
        $query_pl = $this->db->query("
            SELECT id as item_rm_id, number as item_rm_no, name as item_rm_name, uom
            FROM item_rm
            WHERE number LIKE CONCAT('PL-', (
                SELECT number
                FROM item_rm
                WHERE id = ?
            ))
        ", array($item_rm_id));

        $pl_result = $query_pl->row_array();

        $query_vg = $this->db->query("
            SELECT b.item_rm_id_equivalent as item_rm_id, b.item_number as item_rm_no, b.item_name as item_rm_name, b.uom
            FROM item_rm a
            LEFT JOIN item_equivalents b ON a.id = b.item_rm_id
            WHERE b.item_rm_id = ?
        ", array($item_rm_id));

        $vg_result = $query_vg->row_array();

        // Gabungkan kedua hasil jika ada
        $result = array(
            'cr_item' => $cr_result,
            'pl_item' => $pl_result,
            'vg_item' => $vg_result
        );

        echo json_encode($result);
    }

    public function datatables2($request_no, $item_rm_ids_encoded = "")
    {
        $request_no = base64_decode($request_no);
        $item_rm_ids = json_decode(base64_decode($item_rm_ids_encoded));

        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;

            $result = array();

            $this->db->select('a.*, b.name as part_name, b.number as part_no');
            $this->db->from('issued_material_details a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('request_no', $request_no);
            $this->db->where_in('item_rm_id', $item_rm_ids); // gunakan where_in untuk array ID

            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('request_no', 'asc');

            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);

            $records = $this->db->get()->result_array();
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);

            echo json_encode($result);
        }
    }

    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE((a.qty_req *(f.recyle/100)),0) as qty_crusher, COALESCE(d.total_purging,0) as qty_purging');//, d.period, d.wp
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules d', 'a.workorder = d.wo_no and a.item_fg_id = d.item_fg_id');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
            $this->db->where('a.request_no', $request_no);
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            
            if ($totalRows <= 0) {
                $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");                
                $this->db->from('supply_materials a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.request_no', $request_no);
                $totalRows = $this->db->count_all_results('', false);
                //Get Data Array
                $records = $this->db->get()->result_array();
                if ($totalRows <= 0) {
                    $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");
                    $this->db->from('supply_requestions a');
                    $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                    $this->db->where('a.request_no', $request_no);
                    $this->db->where("(a.approved_to = '' OR a.approved_to IS NULL)", NULL, FALSE);
                    $totalRows = $this->db->count_all_results('', false);
                    //Get Data Array
                    $records = $this->db->get()->result_array();
                }
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function getSupplySheet()
    // {
    //     if ($this->input->post()) {
    //         $request_no = $this->input->post('request_no');
    //         $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE((a.qty_req *(f.recyle/100)),0) as qty_crusher, COALESCE((a.qty_req *(d.total_purging/100)),0) as qty_purging');
    //         $this->db->from('supply_sheets a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->join('item_rm c', 'a.item_rm_id = c.id');
    //         $this->db->join('production_schedules d', 'a.workorder = d.wo_no and a.item_fg_id = d.item_fg_id');
    //         $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
    //         $this->db->where('a.request_no', $request_no);
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();

    //         // Check if recyle > 0 for the primary item_rm_id
    //         if (!empty($records)) {
    //             $primary_rm_id = $records[0]['item_rm_id'];
            
    //             // Query untuk memeriksa nilai recyle dan memilih yang lebih besar dari 0
    //             $this->db->select_max('recyle');
    //             $this->db->from('bom');
    //             $this->db->where('item_rm_id', $primary_rm_id);
    //             $this->db->where('recyle >', 0);
    //             $recyle_check = $this->db->get()->row_array();
            
    //             if ($recyle_check && $recyle_check['recyle'] > 0) {
    //                 // Jika ditemukan nilai recyle yang lebih besar dari 0
    //                 $query = $this->db->query("SELECT id as item_rm_id, number as item_rm_no, name as item_rm_name, uom
    //                     FROM item_rm
    //                     WHERE number LIKE CONCAT('CR-', (
    //                         SELECT number 
    //                         FROM item_rm 
    //                         WHERE id = '$primary_rm_id'
    //                     ))
    //                 ");
    //                 $cr_item = $query->row_array();
            
    //                 if ($cr_item) {
    //                     // Tambahkan record baru dengan data dari CR item
    //                     $new_record = $records[0];
    //                     $new_record['item_rm_id'] = $cr_item['item_rm_id'];
    //                     $new_record['item_rm_no'] = $cr_item['item_rm_no'];
    //                     $new_record['item_rm_name'] = $cr_item['item_rm_name'];
    //                     $new_record['uom'] = $cr_item['uom'];
            
    //                     $records[] = $new_record;
    //                     $totalRows++;
    //                 }
    //             } 
    //         }

    //         if ($totalRows <= 0) {
    //             $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");
    //             $this->db->from('supply_materials a');
    //             $this->db->join('item_rm b', 'a.item_rm_id = b.id');
    //             $this->db->where('a.request_no', $request_no);
    //             $totalRows = $this->db->count_all_results('', false);
    //             //Get Data Array
    //             $records = $this->db->get()->result_array();
    //             if ($totalRows <= 0) {
    //                 $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");
    //                 $this->db->from('supply_requestions a');
    //                 $this->db->join('item_rm b', 'a.item_rm_id = b.id');
    //                 $this->db->where('a.request_no', $request_no);
    //                 $totalRows = $this->db->count_all_results('', false);
    //                 //Get Data Array
    //                 $records = $this->db->get()->result_array();
    //             }
    //         }

    //         // Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $receipt_id = $this->input->post('receipt_id');
            $this->db->select('a.label_no, b.item_rm_id, a.qty, "POR" as type, b.price, b.currency');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $receipt_id);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('a.label_divided as label_no,
                COALESCE(b.item_rm_id, c.item_rm_id, d.item_rm_id) as item_rm_id,
                a.qty, 
                b.price, 
                b.currency');
                $this->db->from('barcode_divides a');
                $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id','left');
                $this->db->join('new_barcode c', 'a.reff = c.label_no','left');
                $this->db->join('bpm d', 'a.reff = d.request_id','left');
                $this->db->where('a.label_divided', $receipt_id);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();
                if (!$records) {
                    $this->db->select('a.label_no, a.item_rm_id, a.qty, "NBRM" as type, "0" as price');
                    $this->db->from('new_barcode a');
                    $this->db->join('item_rm d', 'a.item_rm_id = d.id');
                    $this->db->where('a.label_no', @$receipt_id);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                    if (!$records) {
                        $this->db->select('a.label_no, a.item_rm_id, a.qty, "BPM" as type, "0" as price');
                        $this->db->from('bpm_labels a');
                        $this->db->join('item_rm d', 'a.item_rm_id = d.id');
                        $this->db->join('scan_item_bpm b', 'a.label_no = b.label');
                        $this->db->where('a.label_no', @$receipt_id);
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

    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));
            //Select Query
            $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE(d.qty_req, 0) as qty_req, 0 as qty_req_crusher, f.warehouse, (COALESCE(d.qty_req,0) - a.qty) as balance');
            $this->db->from('issued_materials a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id','left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details GROUP BY request_no, item_rm_id) d', 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
            // $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->join('wip_balances f', 'a.item_rm_id = f.item_rm_id and a.request_no = f.request_no', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if ($request_no != "") {
                $this->db->where('a.request_no', $request_no);
            }
            $this->db->group_by('a.request_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_rm_id', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            // if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                if (empty($post['item_fg_id'])) {
                    $post['item_fg_id'] = null;
                }

                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                if (!$issued_materials) {
                    $send   = $this->crud->create('issued_materials', $post);
                    echo $send;
                } else {
                    $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
                    echo $send;
                }
            // } else {
            //     show_error(validation_errors());
            // }
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function create2()
    // {
    //     if ($this->input->post()) {
    //         $post   = $this->input->post();
    //         $request_no = $post['request_no'];// dari supply sheet
    //         $item_rm_id_sh = $post['item_rm_id_sh'];

    //         $dataFinal = array(
    //             "request_no" => $post['request_no'],
    //             "item_rm_id" => $post['item_rm_id'],
    //             "qty" => $post['qty'],
    //             "type" => $post['type'],
    //         );

    //         // var_dump($post);
    //         // die;

    //         $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id = '$item_rm_id_sh'");
    //         $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']]);

    //         $supply_materials = $this->crud->read("supply_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']]);
    //         $supply_sheets = $this->crud->read("supply_sheets", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']]);
    //         $supply_requestions = $this->crud->read("supply_requestions", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']]);

    //         //crusher
    //         $item_rm_id_crusher = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', (SELECT number FROM item_rm WHERE id = '$item_rm_id_sh'))");
    //         $item_rm_id_cr = $item_rm_id_crusher->row() ? $item_rm_id_crusher->row()->id : null;
    //         $qty_crusher = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_cr'");
    //         $qty_crusherValue = isset($qty_crusher[0]) ? ($qty_crusher[0]->qty ?? 0) : 0;

    //         //peletizing
    //         $item_rm_id_peletizing = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', (SELECT number FROM item_rm WHERE id = '$item_rm_id_sh'))");
    //         $item_rm_id_pl = $item_rm_id_peletizing->row() ? $item_rm_id_peletizing->row()->id : null;
    //         $qty_peletizing = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_pl'");
    //         $qty_peletizingValue = isset($qty_peletizing[0]) ? ($qty_peletizing[0]->qty ?? 0) : 0;

    //         //equivalent
    //         $item_rm_id_equivalent = $this->db->query("SELECT b.item_rm_id_equivalent as id FROM item_rm a LEFT JOIN item_equivalents b ON a.id = b.item_rm_id WHERE b.item_rm_id = '$item_rm_id_sh'");
    //         $item_rm_id_eq = $item_rm_id_equivalent->row() ? $item_rm_id_equivalent->row()->id : null;
    //         $qty_equivalent = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_eq'");
    //         $qty_equivalentValue = isset($qty_equivalent[0]) ? ($qty_equivalent[0]->qty ?? 0) : 0;

    //         $totalSupplyQty = (float)$totalSupply[0]->qty;
    //         $postQty = (float)$post['qty'];
    //         $crusherQty = (float)$qty_crusherValue;
    //         $peletizingQty = (float)$qty_peletizingValue;
    //         $equivalentQty = (float)$qty_equivalentValue;
    //         $issuedQty = (float)$issued_materials->qty;

    //         // var_dump("Total Supply :",$totalSupplyQty);
    //         // var_dump("Total qty Issued :",$issuedQty);
    //         // var_dump("Qty :",$postQty);
    //         // var_dump("Qty Crusher : ",$crusherQty);
    //         // var_dump("Qty Pelet : ",$peletizingQty);

    //         // var_dump("TOTAL:", round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty));
    //         // var_dump("ISSUED:", round($issuedQty));
    //         // var_dump("sheets:", $supply_sheets);
    //         // var_dump("materials:", $supply_materials);
    //         // var_dump("requestions:", $supply_requestions);
    //         // return;

    //         if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty + $equivalentQty) <= round($issuedQty)) {
    //                 $send   = $this->crud->create('issued_material_details', $dataFinal);
    //                 if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) == round($issuedQty)){
    //                     if($supply_sheets){
    //                         $update = $this->crud->update('supply_sheets', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']], ["status" => 1]);
    //                     }else if($supply_materials){
    //                         $update = $this->crud->update('supply_materials', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']], ["status" => 1]);
    //                     }else if($supply_requestions){
    //                         $update = $this->crud->update('supply_requestions', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']], ["status" => 1]);
    //                     }
    //                 }
    //                 echo $send;
    //         } else {
    //             echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function update2()
    // {
    //     if ($this->input->post()) {
    //         $id   = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();
    //         $send = $this->crud->update('issued_material_details', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function create_label()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $request_no = $post['request_no'];// dari supply sheet
    //         $item_rm_id = $post['item_rm_id'];//item_fg_id
    //         $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
    //         $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
    //         $issued_material_details2 = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status_issued" => 1]);

    //         $purchase_order_labels = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status" => 1]);
    //         $new_barcode = $this->crud->read("new_barcode", [], ["label_no" => $post['label_no'], "status_issued" => 0]);
    //         $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
    //         $bpm_labels = $this->crud->read("bpm_labels", [], ["label_no" => $post['label_no'], "status" => 1]);

    //         $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
    //         $supply_materials = $this->crud->read("supply_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
    //         $supply_requestions = $this->crud->read("supply_requestions", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
    //         $supply_sheets = $this->crud->read("supply_sheets", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);

    //         //crusher
    //         $item_rm_id_crusher = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', (SELECT number FROM item_rm WHERE id = '$item_rm_id'))");
    //         $item_rm_id_cr = $item_rm_id_crusher->row() ? $item_rm_id_crusher->row()->id : null;
    //         $qty_crusher = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_cr'");
    //         //$qty_crusherValue = isset($qty_crusher[0]) ? $qty_crusher[0]->qty : 0;
    //         $qty_crusherValue = isset($qty_crusher[0]) ? ($qty_crusher[0]->qty ?? 0) : 0;

    //         //peletizing
    //         $item_rm_id_peletizing = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', (SELECT number FROM item_rm WHERE id = '$item_rm_id'))");
    //         $item_rm_id_pl = $item_rm_id_peletizing->row() ? $item_rm_id_peletizing->row()->id : null;
    //         $qty_peletizing = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_pl'");
    //         //$qty_peletizingValue = isset($qty_peletizing[0]) ? $qty_peletizing[0]->qty : 0;
    //         $qty_peletizingValue = isset($qty_peletizing[0]) ? ($qty_peletizing[0]->qty ?? 0) : 0;

    //         $totalSupplyQty = (float)$totalSupply[0]->qty;
    //         $postQty = (float)$post['qty'];
    //         $crusherQty = (float)$qty_crusherValue;
    //         $peletizingQty = (float)$qty_peletizingValue;
    //         $issuedQty = (float)$issued_materials->qty;

    //         // var_dump($totalSupplyQty);
    //         // var_dump($postQty);
    //         // var_dump($crusherQty);
    //         // var_dump($peletizingQty);
    //         // var_dump($issuedQty);
    //         // die;
    //         //  if (!$issued_material_details || !$issued_material_details2) {//jika tidak ada
    //         if (!$issued_material_details) {//jika tidak ada
    //             if ($purchase_order_labels) {
    //                 if ($issued_materials) {

    //                     $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $purchase_order_labels->receipt_id]);
    //                     // $checkItems = $this->crud->query("SELECT a.receipt_date, b.label_no, a.receipt_id, b.receipt_id, c.label_no, d.label_no
    //                     // FROM purchase_order_receipts a 
    //                     // LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
    //                     // LEFT JOIN barcode_divides c ON b.label_no = c.label_no
    //                     // LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
    //                     // WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null
    //                     // -- WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date between '%2025-02-16%' and '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null
    //                     // ORDER BY receipt_date ASC");

    //                     $checkItems = $this->crud->query("SELECT a.receipt_date,a.lotno, b.label_no, a.receipt_id, b.receipt_id, c.label_no, d.label_no
    //                     FROM purchase_order_receipts a 
    //                     LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
    //                     LEFT JOIN barcode_divides c ON b.label_no = c.label_no
    //                     LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
    //                     WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null AND b.status_issued = 0
    //                     ORDER BY receipt_date ASC,a.lotno ASC");

    //                     if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) <= round($issuedQty)) {
    //                         if (count($checkItems) <= 0) {
    //                             $send   = $this->crud->create('issued_material_details', $post);
    //                             $update = $this->crud->update('purchase_order_labels', ["label_no" => $post['label_no']], ["status_issued" => 1]);
    //                             // var_dump($totalSupply[0]->qty + $post['qty']);
    //                             // var_dump($issued_materials->qty);

    //                             if(round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) == round($issuedQty)){
    //                                 if($supply_sheets){
    //                                     $update = $this->crud->update('supply_sheets', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }else if($supply_materials){
    //                                     $update = $this->crud->update('supply_materials', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }else if($supply_requestions){
    //                                     $update = $this->crud->update('supply_requestions', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }
    //                             }
    //                             echo $send;
    //                         } else {
    //                             //echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                                
    //                             if (count($checkItems) > 0) {//berubah
    //                                 // Ambil semua lotno yang masih pending untuk FIFO
    //                                 $pendingLotnos = array_unique(array_map(function($item) {
    //                                     return $item->lotno;
    //                                 }, $checkItems));
                                
    //                                 $pendingLotnosText = implode(", ", $pendingLotnos);
                                
    //                                 echo json_encode([
    //                                     "title" => "FIFO Violation",
    //                                     "message" => "Please scan sequentially. Pending LOT No(s): " . $pendingLotnosText,
    //                                     "theme" => "error"
    //                                 ]);
    //                                 return;
    //                             }
    //                         }
    //                     } else {
    //                         echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued < Qty Supply", "theme" => "error"));
    //                     }
    //                 } else {
    //                     echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
    //                 }

    //             } elseif ($barcode_divides) {
    //                 if ($issued_materials) {

    //                     // for FIFO
    //                     // $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
    //                     // $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no, a.receipt_id, b.receipt_id, d.label_no
    //                     // FROM purchase_order_receipts a
    //                     // LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
    //                     // LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
    //                     // LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
    //                     // WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND c.status = 0 AND d.label_no is null
    //                     // ORDER BY receipt_date ASC");

    //                     if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) <= round($issuedQty)) {
    //                         // if (count($checkItems) <= 0) {
    //                             $send = $this->crud->create('issued_material_details', $post);
    //                             $update = $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);

    //                             if(round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) == round($issuedQty)){
    //                                 if($supply_sheets){
    //                                     $update = $this->crud->update('supply_sheets', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }else if($supply_materials){
    //                                     $update = $this->crud->update('supply_materials', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }else if($supply_requestions){
    //                                     $update = $this->crud->update('supply_requestions', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                                 }
    //                             }
    //                             echo $send;
    //                         // } else {
    //                         //     echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
    //                         // }
    //                     } else {
    //                         echo json_encode(array("title" => "More Then Qty", "message" => ($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) . "<=" . $issuedQty . " Qty Issued <= Qty Supply", "theme" => "error"));
    //                     }
    //                 } else {
    //                     echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
    //                 }
    //             } else if ($new_barcode) {
    //                 if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) <= round($issuedQty)) {
    //                     $send   = $this->crud->create('issued_material_details', $post);
    //                     $update = $this->crud->update('new_barcode', ["label_no" => $post['label_no']], ["status_issued" => 1]);

    //                     if(round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) == round($issuedQty)){
    //                         if($supply_sheets){
    //                             $update = $this->crud->update('supply_sheets', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }else if($supply_materials){
    //                             $update = $this->crud->update('supply_materials', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }else if($supply_requestions){
    //                             $update = $this->crud->update('supply_requestions', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }
    //                     }
    //                     echo $send;
    //                 } else {
    //                     echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
    //                 }
    //             } else if ($bpm_labels) {
    //                 if (round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) <= round($issuedQty)) {
    //                     $send   = $this->crud->create('issued_material_details', $post);
    //                     $update = $this->crud->update('bpm_labels', ["label_no" => $post['label_no']], ["status_issued" => 1]);

    //                     if(round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) == round($issuedQty)){
    //                         if($supply_sheets){
    //                             $update = $this->crud->update('supply_sheets', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }else if($supply_materials){
    //                             $update = $this->crud->update('supply_materials', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }else if($supply_requestions){
    //                             $update = $this->crud->update('supply_requestions', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
    //                         }
    //                     }
    //                     echo $send;
    //                 } else {
    //                     echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
    //                 }
    //             } else {
    //                 echo json_encode(array("title" => "Available", "message" => "This label not found", "theme" => "error"));
    //             }
    //         } else {
    //             echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // dokumentasi : handle status not change in supply sheet    
    private function _checkAndCloseSupplyStatus($request_no, $item_rm_id_sh, $issuedQty)
    {
        $rm_main  = $item_rm_id_sh;

        $cr_query = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', (SELECT number FROM item_rm WHERE id = '$rm_main'))")->row();
        $rm_cr    = $cr_query ? $cr_query->id : 'NULL';

        $pl_query = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', (SELECT number FROM item_rm WHERE id = '$rm_main'))")->row();
        $rm_pl    = $pl_query ? $pl_query->id : 'NULL';

        $eq_query = $this->db->query("SELECT b.item_rm_id_equivalent as id FROM item_rm a LEFT JOIN item_equivalents b ON a.id = b.item_rm_id WHERE b.item_rm_id = '$rm_main'")->row();
        $rm_eq    = $eq_query ? $eq_query->id : 'NULL';

        $queryTotal = $this->db->query("
            SELECT COALESCE(SUM(qty), 0) as total_qty 
            FROM issued_material_details 
            WHERE request_no = '$request_no' 
              AND item_rm_id IN ('$rm_main', '$rm_cr', '$rm_pl', '$rm_eq')
        ")->row();

        $totalAccumulated = (float)($queryTotal ? $queryTotal->total_qty : 0);
        $targetIssued     = (float)$issuedQty;

        if (round($totalAccumulated, 4) >= round($targetIssued, 4)) {
            // Update Supply Sheets
            $supply_sheets = $this->crud->read("supply_sheets", [], ["request_no" => $request_no, "item_rm_id" => $rm_main]);
            if ($supply_sheets) {
                $this->crud->update('supply_sheets', ["request_no" => $request_no, "item_rm_id" => $rm_main], ["status" => 1]);
                return true;
            }

            // Update Supply Materials
            $supply_materials = $this->crud->read("supply_materials", [], ["request_no" => $request_no, "item_rm_id" => $rm_main]);
            if ($supply_materials) {
                $this->crud->update('supply_materials', ["request_no" => $request_no, "item_rm_id" => $rm_main], ["status" => 1]);
                return true;
            }

            // Update Supply Requisitions
            $supply_requestions = $this->crud->read("supply_requestions", [], ["request_no" => $request_no, "item_rm_id" => $rm_main]);
            if ($supply_requestions) {
                $this->crud->update('supply_requestions', ["request_no" => $request_no, "item_rm_id" => $rm_main], ["status" => 1]);
                return true;
            }
        }

        return false;
    }

    public function create2()
    {
        if ($this->input->post()) {
            $post          = $this->input->post();
            $request_no    = $post['request_no']; // dari supply sheet
            $item_rm_id_sh = $post['item_rm_id_sh'];

            $dataFinal = array(
                "request_no" => $post['request_no'],
                "item_rm_id" => $post['item_rm_id'],
                "qty"        => $post['qty'],
                "type"       => $post['type'],
            );

            $totalSupply      = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id = '$item_rm_id_sh'");
            $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id_sh']]);

            // crusher
            $item_rm_id_crusher = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', (SELECT number FROM item_rm WHERE id = '$item_rm_id_sh'))");
            $item_rm_id_cr      = $item_rm_id_crusher->row() ? $item_rm_id_crusher->row()->id : null;
            $qty_crusher        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_cr'");
            $qty_crusherValue   = isset($qty_crusher[0]) ? ($qty_crusher[0]->qty ?? 0) : 0;

            // peletizing
            $item_rm_id_peletizing = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', (SELECT number FROM item_rm WHERE id = '$item_rm_id_sh'))");
            $item_rm_id_pl         = $item_rm_id_peletizing->row() ? $item_rm_id_peletizing->row()->id : null;
            $qty_peletizing        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_pl'");
            $qty_peletizingValue   = isset($qty_peletizing[0]) ? ($qty_peletizing[0]->qty ?? 0) : 0;

            // equivalent
            $item_rm_id_equivalent = $this->db->query("SELECT b.item_rm_id_equivalent as id FROM item_rm a LEFT JOIN item_equivalents b ON a.id = b.item_rm_id WHERE b.item_rm_id = '$item_rm_id_sh'");
            $item_rm_id_eq         = $item_rm_id_equivalent->row() ? $item_rm_id_equivalent->row()->id : null;
            $qty_equivalent        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_eq'");
            $qty_equivalentValue   = isset($qty_equivalent[0]) ? ($qty_equivalent[0]->qty ?? 0) : 0;

            $totalSupplyQty = (float)$totalSupply[0]->qty;
            $postQty        = (float)$post['qty'];
            $crusherQty     = (float)$qty_crusherValue;
            $peletizingQty  = (float)$qty_peletizingValue;
            $equivalentQty  = (float)$qty_equivalentValue;
            $issuedQty      = (float)$issued_materials->qty;

            $totalAccumulated = round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty + $equivalentQty, 4);
            $targetIssued     = round($issuedQty, 4);

            if ($totalAccumulated <= $targetIssued) {
                $send = $this->crud->create('issued_material_details', $dataFinal);

                $this->_checkAndCloseSupplyStatus($request_no, $item_rm_id_sh, $issuedQty);

                echo $send;
            } else {
                echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update2()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('issued_material_details', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create_label()
    {
        if ($this->input->post()) {
            $post       = $this->input->post();
            $request_no = $post['request_no']; // dari supply sheet
            $item_rm_id = $post['item_rm_id']; // item_rm_id (atau ID Label/Material Utama)

            $totalSupply              = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
            $issued_material_details  = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            $issued_material_details2 = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status_issued" => 1]);

            $purchase_order_labels    = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status" => 1]);
            $new_barcode              = $this->crud->read("new_barcode", [], ["label_no" => $post['label_no'], "status_issued" => 0]);
            $barcode_divides          = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
            $bpm_labels               = $this->crud->read("bpm_labels", [], ["label_no" => $post['label_no'], "status" => 1]);

            $issued_materials         = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);

            // crusher
            $item_rm_id_crusher = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('CR-', (SELECT number FROM item_rm WHERE id = '$item_rm_id'))");
            $item_rm_id_cr      = $item_rm_id_crusher->row() ? $item_rm_id_crusher->row()->id : null;
            $qty_crusher        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_cr'");
            $qty_crusherValue   = isset($qty_crusher[0]) ? ($qty_crusher[0]->qty ?? 0) : 0;

            // peletizing
            $item_rm_id_peletizing = $this->db->query("SELECT id FROM item_rm WHERE number LIKE CONCAT('PL-', (SELECT number FROM item_rm WHERE id = '$item_rm_id'))");
            $item_rm_id_pl         = $item_rm_id_peletizing->row() ? $item_rm_id_peletizing->row()->id : null;
            $qty_peletizing        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_pl'");
            $qty_peletizingValue   = isset($qty_peletizing[0]) ? ($qty_peletizing[0]->qty ?? 0) : 0;

            $item_rm_id_equivalent = $this->db->query("SELECT b.item_rm_id_equivalent as id FROM item_rm a LEFT JOIN item_equivalents b ON a.id = b.item_rm_id WHERE b.item_rm_id = '$item_rm_id'");
            $item_rm_id_eq         = $item_rm_id_equivalent->row() ? $item_rm_id_equivalent->row()->id : null;
            $qty_equivalent        = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id_eq'");
            $qty_equivalentValue   = isset($qty_equivalent[0]) ? ($qty_equivalent[0]->qty ?? 0) : 0;

            $totalSupplyQty = (float)$totalSupply[0]->qty;
            $postQty        = (float)$post['qty'];
            $crusherQty     = (float)$qty_crusherValue;
            $peletizingQty  = (float)$qty_peletizingValue;
            $equivalentQty  = (float)$qty_equivalentValue;
            $issuedQty      = (float)($issued_materials ? $issued_materials->qty : 0);

            $totalAccumulated = round($totalSupplyQty + $postQty + $crusherQty + $peletizingQty + $equivalentQty, 4);
            $targetIssued     = round($issuedQty, 4);

            if (!$issued_material_details) { // jika belum ada
                if ($purchase_order_labels) {
                    if ($issued_materials) {
                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $purchase_order_labels->receipt_id]);

                        $checkItems = $this->crud->query("SELECT a.receipt_date, a.lotno, b.label_no, a.receipt_id, b.receipt_id, c.label_no, d.label_no
                        FROM purchase_order_receipts a 
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                        LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
                        WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null AND b.status_issued = 0
                        ORDER BY receipt_date ASC, a.lotno ASC");

                        if ($totalAccumulated <= $targetIssued) {
                            if (count($checkItems) <= 0) {
                                $send   = $this->crud->create('issued_material_details', $post);
                                $update = $this->crud->update('purchase_order_labels', ["label_no" => $post['label_no']], ["status_issued" => 1]);

                                $this->_checkAndCloseSupplyStatus($request_no, $item_rm_id, $issuedQty);

                                echo $send;
                            } else {
                                $pendingLotnos = array_unique(array_map(function ($item) {
                                    return $item->lotno;
                                }, $checkItems));

                                $pendingLotnosText = implode(", ", $pendingLotnos);

                                echo json_encode([
                                    "title"   => "FIFO Violation",
                                    "message" => "Please scan sequentially. Pending LOT No(s): " . $pendingLotnosText,
                                    "theme"   => "error"
                                ]);
                                return;
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued < Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }

                } elseif ($barcode_divides) {
                    if ($issued_materials) {
                        if ($totalAccumulated <= $targetIssued) {
                            $send   = $this->crud->create('issued_material_details', $post);
                            $update = $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);

                            $this->_checkAndCloseSupplyStatus($request_no, $item_rm_id, $issuedQty);

                            echo $send;
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => ($totalSupplyQty + $postQty + $crusherQty + $peletizingQty) . "<=" . $issuedQty . " Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }

                } else if ($new_barcode) {
                    if ($totalAccumulated <= $targetIssued) {
                        $send   = $this->crud->create('issued_material_details', $post);
                        $update = $this->crud->update('new_barcode', ["label_no" => $post['label_no']], ["status_issued" => 1]);

                        $this->_checkAndCloseSupplyStatus($request_no, $item_rm_id, $issuedQty);

                        echo $send;
                    } else {
                        echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                    }

                } else if ($bpm_labels) {
                    if ($totalAccumulated <= $targetIssued) {
                        $send   = $this->crud->create('issued_material_details', $post);
                        $update = $this->crud->update('bpm_labels', ["label_no" => $post['label_no']], ["status_issued" => 1]);

                        // PERBAIKAN: Gunakan Helper untuk Pengecekan Status Close
                        $this->_checkAndCloseSupplyStatus($request_no, $item_rm_id, $issuedQty);

                        echo $send;
                    } else {
                        echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                    }

                } else {
                    echo json_encode(array("title" => "Available", "message" => "This label not found", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
