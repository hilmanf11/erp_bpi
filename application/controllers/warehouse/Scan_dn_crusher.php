<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_dn_crusher extends CI_Controller
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
            $this->load->view('warehouse/scan_dn_crusher');
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
            $this->db->select("'-' as po_no, b.request_no as receipt_no, b.request_id as receipt_id, a.label_no, a.qty, b.item_rm_id, c.number as item_number, c.name as item_name, c.uom");
            $this->db->from('receipt_crusher_labels a');
            $this->db->join('receipt_crusher_labels b', 'a.request_id = b.request_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->where('a.label_no', $label_no);
            $this->db->where('a.status_issued', 0);
            $this->db->group_by('a.label_no');
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function checkLabel()
    {
        $label_no = $this->input->post('label_no');

        $exists = $this->db->where('label_no', $label_no)
                        ->count_all_results('scan_dn_crusher') > 0;

        echo json_encode(['valid' => !$exists]);
    }

    public function saveTransfer()
    {
        $header  = $this->input->post('header'); 
        $details = json_decode($this->input->post('details'), true);

        if (empty($header) || empty($details)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Data header atau detail tidak ada!"
            ]);
            return;
        }

        $trans_date = $header['transaction_date'];
        $div = $header['division'];
        $year       = date("Y", strtotime($trans_date));
        $monthYear  = date("my", strtotime($trans_date));
                    
        $sqlGetID = $this->db->query("SELECT MAX(LEFT(document_no,3)) as kode FROM scan_dn_crusher WHERE YEAR(transaction_date) = '$year'");
        $rowID = $sqlGetID->row();
        $kode  = $rowID->kode;

        $urutan = ($kode == NULL) ? 1 : ((int) $kode + 1);
        $autoID = sprintf("%03s", $urutan);

        $autonumber = $autoID . "-".$div."-CR-" . $monthYear;

        $inserted = 0;
        foreach ($details as $row) {
            $dataInsert = [
                "document_no"      => $autonumber,
                "transaction_date" => $header['transaction_date'],
                "supplier_id"      => $header['supplier_id'],
                "division"         => $header['division'],
                "remarks"          => $header['remarks'],
                "label_no"         => $row['label_no'],
                "receipt_no"       => $row['receipt_no'],
                "receipt_id"       => $row['receipt_id'],
                "po_no"            => $row['po_no'],
                "item_rm_id"       => $row['item_rm_id'],
                "qty"              => $row['qty']
            ];

            if ($this->crud->create('scan_dn_crusher', $dataInsert)) {
                $inserted++;

                $this->db->where('label_no', $row['label_no'])
                        ->update('receipt_crusher_labels', ['status_issued' => 1]);
            }
        }

        if ($inserted > 0) {
            echo json_encode([
                "status"  => "success",
                "message" => "Transfer Succes with Document No: $autonumber ($inserted row)"
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Gagal menyimpan data"
            ]);
        }
    }
}
