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
        $this->form_validation->set_rules('item_fg_id', 'Item ID', 'required|min_length[1]|max_length[50]');
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

    // public function getSupplySheet()
    // {
    //     if ($this->input->post()) {
    //         $request_no = $this->input->post('request_no');
    //         $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE((a.qty_req *(f.recyle/100)),0) as qty_crusher, COALESCE(d.total_purging,0) as qty_purging');//, d.period, d.wp
    //         $this->db->from('supply_sheets a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->join('item_rm c', 'a.item_rm_id = c.id');
    //         $this->db->join('production_schedules d', 'a.workorder = d.wo_no and a.item_fg_id = d.item_fg_id');
    //         $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
    //         $this->db->where('a.request_no', $request_no);
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();
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
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE((a.qty_req *(f.recyle/100)),0) as qty_crusher, COALESCE((a.qty_req *(d.total_purging/100)),0) as qty_purging');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules d', 'a.workorder = d.wo_no and a.item_fg_id = d.item_fg_id');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
            $this->db->where('a.request_no', $request_no);
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            // Check if recyle > 0 for the primary item_rm_id
            if (!empty($records)) {
                $primary_rm_id = $records[0]['item_rm_id'];
            
                // Query untuk memeriksa nilai recyle dan memilih yang lebih besar dari 0
                $this->db->select_max('recyle');
                $this->db->from('bom');
                $this->db->where('item_rm_id', $primary_rm_id);
                $this->db->where('recyle >', 0);
                $recyle_check = $this->db->get()->row_array();
            
                if ($recyle_check && $recyle_check['recyle'] > 0) {
                    // Jika ditemukan nilai recyle yang lebih besar dari 0
                    $query = $this->db->query("SELECT id as item_rm_id, number as item_rm_no, name as item_rm_name, uom
                        FROM item_rm
                        WHERE number LIKE CONCAT('CR-', (
                            SELECT number 
                            FROM item_rm 
                            WHERE id = '$primary_rm_id'
                        ))
                    ");
                    $cr_item = $query->row_array();
            
                    if ($cr_item) {
                        // Tambahkan record baru dengan data dari CR item
                        $new_record = $records[0];
                        $new_record['item_rm_id'] = $cr_item['item_rm_id'];
                        $new_record['item_rm_no'] = $cr_item['item_rm_no'];
                        $new_record['item_rm_name'] = $cr_item['item_rm_name'];
                        $new_record['uom'] = $cr_item['uom'];
            
                        $records[] = $new_record;
                        $totalRows++;
                    }
                } 
            }

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
                    $totalRows = $this->db->count_all_results('', false);
                    //Get Data Array
                    $records = $this->db->get()->result_array();
                }
            }

            // Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $receipt_id = $this->input->post('receipt_id');
            $this->db->select('a.label_no, b.item_rm_id, a.qty, "POR" as type');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $receipt_id);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty');
                $this->db->from('barcode_divides a');
                $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                $this->db->where('a.label_divided', $receipt_id);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();
                if (!$records) {
                    $this->db->select('a.label_no, b.item_rm_id, a.qty, "CR" as type');
                    $this->db->from('purchase_order_label_crushers a');
                    $this->db->join('purchase_order_receipt_crushers b', 'a.receipt_id = b.receipt_id');
                    $this->db->where('a.label_no', $receipt_id);
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

    // public function checkItemNumber()
    // {
    //     $item_rm_id = $this->input->post('item_rm_id');

    //     // Query untuk mengambil item number berdasarkan id
    //     $this->db->select('number');
    //     $this->db->from('item_rm');
    //     $this->db->where('id', $item_rm_id);
    //     $query = $this->db->get();
    //     $item = $query->row();

    //     if ($item) {
    //         // Hapus awalan "CR-" dari item number
    //         $stripped_number = str_replace('CR-', '', $item->number);

    //         // Query untuk mencari item berdasarkan item number yang telah dihapus awalan "CR-"
    //         $this->db->select('id');
    //         $this->db->from('item_rm');
    //         $this->db->where('number', $stripped_number);
    //         $query = $this->db->get();
    //         $result = $query->row();

    //         if ($result) {
    //             echo json_encode(['id' => $result->id]);
    //         } else {
    //             echo json_encode(['id' => null]);
    //         }
    //     } else {
    //         echo json_encode(['id' => null]);
    //     }
    // }

    
    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));
            //Select Query
            $this->db->select('a.*, 
            b.number as item_number, 
            c.number as item_rm_no, 
            c.name as item_rm_name, 
            c.uom, 
            f.warehouse, 
            (CASE WHEN d.type = "POR" THEN d.qty_req ELSE 0 END) as qty_req, 
            (CASE WHEN d.type = "POR" THEN (d.qty_req + f.balance) ELSE 0 END) as balance, 

            (CASE WHEN e.type = "CR" THEN e.qty_req_crusher ELSE 0 END) as qty_req_crusher, 
            (CASE WHEN e.type = "CR" THEN (e.qty_req_crusher + f.balance) ELSE 0 END) as balance_crusher');

            $this->db->from('issued_materials a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('(SELECT item_rm_id, request_no, type, SUM(qty) as qty_req FROM issued_material_details GROUP BY request_no, item_rm_id) d', 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
            $this->db->join('(SELECT item_rm_id, request_no, type, SUM(qty) as qty_req_crusher FROM issued_material_details GROUP BY request_no, item_rm_id) e', 'e.request_no = a.request_no and e.item_rm_id = a.item_rm_id', 'left');
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

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
    //             if (!$issued_materials) {
    //                 $send   = $this->crud->create('issued_materials', $post);
    //                 echo $send;
    //             } else {
    //                 $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
    //                 echo $send;
    //             }
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                if (!$issued_materials) {
                    if (strpos($post['item_rm_no'], 'CR-') !== 0) { //jika tidak ada
                        $post_final = [
                            "item_fg_id" => $post['item_fg_id'],
                            "item_rm_id" => $post['item_rm_id'],
                            "request_no" => $post['request_no'],
                            "period" => $post['period'],
                            "workorder" => $post['workorder'],
                            "qty" => $post['qty'],
                            "qty_crusher" => "0",
                            "qty_purging" => $post['qty_purging']
                        ];
                        $send   = $this->crud->create('issued_materials', $post_final);
                    } else{
                        $post_final = [
                            "item_fg_id" => $post['item_fg_id'],
                            "item_rm_id" => $post['item_rm_id'],
                            "request_no" => $post['request_no'],
                            "period" => $post['period'],
                            "workorder" => $post['workorder'],
                            "qty" => "0",
                            "qty_crusher" => $post['qty_crusher'],
                            "qty_purging" => $post['qty_purging']
                        ];
                        $send   = $this->crud->create('issued_materials', $post_final);
                    }
                    echo $send;
                } else {
                    if (strpos($post['item_rm_no'], 'CR-') !== 0) { //jika tidak ada
                        $post_final = [
                            "request_no" => $post['request_no'],
                            "period" => $post['period'],
                            "qty" => $post['qty'],
                            "qty_crusher" => "0",
                            "qty_purging" => $post['qty_purging']
                        ];
                        $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post_final);
                    } else{
                        $post_final = [
                            "request_no" => $post['request_no'],
                            "period" => $post['period'],
                            "qty" => "0",
                            "qty_crusher" => $post['qty_crusher'],
                            "qty_purging" => $post['qty_purging']
                        ];
                        $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post_final);
                    }
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create_label()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $request_no = $post['request_no'];// dari supply sheet
            $item_rm_id = $post['item_rm_id'];//item_fg_id
            $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
            $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            $purchase_order_labels = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status" => 1]);
            $purchase_order_label_crushers = $this->crud->read("purchase_order_label_crushers", [], ["label_no" => $post['label_no'], "status" => 0]);
            $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
            $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);

            if (!$issued_material_details) {//jika tidak ada
                if ($purchase_order_labels) {
                    if ($issued_materials) {

                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $purchase_order_labels->receipt_id]);
                        $checkItems = $this->crud->query("SELECT a.receipt_date, b.label_no, a.receipt_id, b.receipt_id, c.label_no, d.label_no
                        FROM purchase_order_receipts a 
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                        LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
                        WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null
                        ORDER BY receipt_date ASC");

                        if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            if (count($checkItems) <= 0) {
                                $send   = $this->crud->create('issued_material_details', $post);
                                echo $send;
                            } else {
                                echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }

                } elseif ($barcode_divides) {
                    if ($issued_materials) {

                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
                        $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no, a.receipt_id, b.receipt_id, d.label_no
                        FROM purchase_order_receipts a
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
                        LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
                        WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND c.status = 0 AND d.label_no is null
                        ORDER BY receipt_date ASC");

                        if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            if (count($checkItems) <= 0) {
                                $send = $this->crud->create('issued_material_details', $post);
                                $update = $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
                                echo $send;
                            } else {
                                echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } else if ($purchase_order_label_crushers) {
                    if ($issued_materials) {

                        $purchase_order_receipt_crushers = $this->crud->read("purchase_order_receipt_crushers", [], ["receipt_id" => $purchase_order_label_crushers->receipt_id]);
                        $checkItems = $this->crud->query("SELECT a.trans_date, b.label_no, a.receipt_id, b.receipt_id, d.label_no
                        FROM purchase_order_receipt_crushers a 
                        LEFT JOIN purchase_order_label_crushers b ON a.receipt_id = b.receipt_id
                        -- LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                        LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
                        WHERE a.item_rm_id = '$purchase_order_receipt_crushers->item_rm_id' and a.trans_date < '$purchase_order_receipt_crushers->trans_date' AND d.label_no is null
                        ORDER BY trans_date ASC");

                        if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            if (count($checkItems) <= 0) {
                                $send   = $this->crud->create('issued_material_details', $post);
                                echo $send;
                            } else {
                                echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Scanned In", "message" => "This label has not been scanned in", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
