<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_item_bpm extends CI_Controller
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
        $this->form_validation->set_rules('label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/scan_item_bpm');
        } else {
            redirect('error_access');
        }
    }

    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $request_no = $this->input->get('request_no');

            $this->db->select('a.request_no, 
            a.request_id, 
            a.request_date, 
            b.uom, 
            b.number as item_number, 
            b.name as item_name, 
            a.qty, 
            a.created_by, 
            a.created_date,
            c.qty_bpm');
            $this->db->from('bpm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join("(SELECT 
                                a.request_no, 
                                a.request_id,
                                a.item_rm_id, 
                                SUM(a.qty) AS qty_bpm
                            FROM scan_item_bpm a
                            WHERE a.request_no = '$request_no'
                            GROUP BY a.request_no, a.item_rm_id
                            ) c", 'a.request_no = c.request_no AND a.item_rm_id = c.item_rm_id', 'left');
            $this->db->where('a.request_no', $request_no);
            $this->db->group_by('b.number');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $label = $this->input->post('label');

            $this->db->select("a.qty, a.item_rm_id, a.request_id, b.request_date");
            $this->db->from('bpm_labels a');
            $this->db->join('bpm b', 'a.request_id = b.request_id');
            $this->db->where('a.label_no', $label);
            $this->db->where('a.status', 0);
            $this->db->group_by('a.label_no');

            $totalRows = $this->db->count_all_results('', false);
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
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $request_no = $post['request_no'];
                $request_id = $post['request_id'];
                $item_rm_id = $post['item_rm_id'];
                
                $scan_item_bpm = $this->crud->read("scan_item_bpm", [], ["request_id" => $request_id, "label" => $post['label']]);

                $bpm = $this->crud->read("bpm",[], ["request_id" => $request_id, "item_rm_id" => $post['item_rm_id']]);                
                $totalScan = $this->crud->query("SELECT SUM(qty) as qty FROM scan_item_bpm WHERE request_id = '$request_id' and item_rm_id = '$item_rm_id'");
                
                // $this->db->select("a.*");
                // $this->db->from('scan_item_receipts_fg a');
                // $this->db->join('wip_receipts d', 'a.checksheet_number = d.checksheet_number','left');
                // $this->db->join('sales_orders b', 'd.item_fg_id = b.item_fg_id','left');
                // $this->db->join('delivery_orders c', 'b.item_fg_id = c.item_fg_id and c.customer_id = b.customer_id','left');//ok
                // $this->db->where('a.checksheet_label', $post['checksheet_label']);
                // $this->db->group_by('a.checksheet_label');
                // $label_items = $this->db->get()->result_array();

                // var_dump($post['qty']);
                // var_dump($totalShipping[0]->qty);
                // var_dump($do_no->qty_del);

                // if ($label_items) {
                    if (!$scan_item_bpm) {
                        if(round($post['qty'] + $totalScan[0]->qty) <= round($bpm->qty)){
                            $send = $this->crud->create('scan_item_bpm', $post);
                            $this->crud->update('bpm_labels', ["label_no" => $post['label']], ["status" => "1"]);
                            if(round($post['qty'] + $totalScan[0]->qty) == round($bpm->qty)){
                                $this->crud->update('bpm', ["request_id" => $request_id, "item_rm_id" => $item_rm_id], ["status" => "1"]);
                            }
                            echo $send;
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Label > Qty BPM ", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Available", "message" => "Data BPM has been Scanning", "theme" => "error"));
                    }
                //} else {
                //     $this->db->select("e.*");
                //     $this->db->from('barcode_divides_fg e');
                //     $this->db->where('e.label_divided', $post['checksheet_label']);
                //     $barcode_items = $this->db->get()->result_array(); // Query kedua
                
                //     if ($barcode_items) {
                //         if (!$shipping_orders) {
                //             if(round($post['qty'] + $totalShipping[0]->qty) <= round($do_no->qty_del)){
                //                 $send = $this->crud->create('shipping_orders', $post);
                //                 $this->crud->update('barcode_divides_fg', ["label_divided" => $post['checksheet_label']], ["status" => "1"]);
                //                 if(round($post['qty'] + $totalShipping[0]->qty) == round($do_no->qty_del)){
                //                     $this->crud->update('delivery_orders', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id']], ["status_scan" => "1"]);
                //                 }                                
                //                 echo $send;
                //             } else {
                //                 echo json_encode(array("title" => "More Then Qty", "message" => "Qty Label > Qty DO ", "theme" => "error"));
                //             }
                //         } else {
                //             echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
                //         }
                //     } else {
                //         echo json_encode(array("title" => "Not Match", "message" => "Label does not match the list item", "theme" => "error"));
                //     }
                // }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
