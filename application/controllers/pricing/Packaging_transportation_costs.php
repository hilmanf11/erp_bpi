<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Packaging_transportation_costs extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
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
            $this->load->view('pricing/packaging_transportation_costs');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function readFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, 
            d.name as box_kind_name,
            c.id as box_id,
            c.name as box_name,
            c.code as box_code,
            c.length as box_length,
            c.width as box_width,
            c.height as box_height,
            c.color as box_color 
        FROM item_fg a 
        JOIN item_boxs c ON a.boxs = c.name
        JOIN item_kinds d ON c.item_kind_id = d.id
        WHERE a.status = '0' 
        AND (a.number like '%$post%' or a.number_customer like '%$post%' or a.name like '%$post%' or a.id like '%$post%') 
        ORDER BY a.number ASC");
        echo json_encode($send);
    }

    public function reads($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readPlant($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id' and (plant LIKE '%$post%' or `address` LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            a.name, 
            b.price 
        FROM item_rm a 
        JOIN supplier_items b ON b.item_rm_id = a.id 
        WHERE b.share_order = '100' AND (a.number LIKE '%$post%' or a.name LIKE '%$post%')");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_fg_number = @base64_decode($get['filter_item_fg_number']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*');
            $this->db->from('packaging_transportation_costs a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.item_fg_number', $filter_item_fg_number);
            
            $this->db->order_by('b.id', 'ASC');
            // $this->db->order_by('d.plant', 'ASC');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('packaging_transportation_costs', $post);
            echo $send;  
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('packaging_transportation_costs', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('packaging_transportation_costs', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'customer_id' => $data->val($i, 2),
                'division_id' => $data->val($i, 3),
                'customer_address_id' => $data->val($i, 4),
                'item_fg_id' => $data->val($i, 5),
                'price' => $data->val($i, 6),
                'currency' => $data->val($i, 7),
                'valid_date' => $data->val($i, 8),
                'remark' => $data->val($i, 9)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/customer_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customer_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/customer_items.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         //Cek Process Number          //table       //field        //field excel
    //         $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id']]);

    //         if (!empty($customer_items->customer_id)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => " Customer " . $data['customer_id'] . " is Duplicate Data", "theme" => "error"));
    //         } elseif (!empty($customer_items->item_fg_id)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
    //         } else {
    //             $dataFinal = array(
    //                 //field
    //                 "customer_id" => $data['customer_id'],
    //                 "item_fg_id" => $data['item_fg_id'],
    //                 "price" => $data['price'],
    //                 "valid_date" => $data['valid_date'],
    //                 "remark" => $data['remark'],
    //             );
    //             $send   = $this->crud->create('customer_items', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=customer_items_$format.xls");
        }

        $get = $this->input->get();
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_fg_number = @base64_decode($get['filter_item_fg_number']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('packaging_transportation_costs a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.item_fg_number', $filter_item_fg_number);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#packaging_transportation_costs {border-collapse: collapse;width: 100%;font-size: 12px;}#packaging_transportation_costs td, #packaging_transportation_costs th {border: 1px solid #ddd;padding: 2px;}#packaging_transportation_costs tr:nth-child(even){background-color: #f2f2f2;}#packaging_transportation_costs tr:hover {background-color: #ddd;}#packaging_transportation_costs th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
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
                <h3>Packaging & Transportation Costs</h3>
            </div>
        </center>
        
        <table id="packaging_transportation_costs" border="1">
            <tr>
                <th width="40">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Customer</th>
                <th>Month</th>
                <th>Year</th>
                <th>Revision</th>

                <th>Dimension</th>
                <th>Part No Layer</th>
                <th>Qty Usage</th>
                <th>Qty Packing Std</th>
                <th>Price Layer</th>
                <th>Adj %</th>
                <th>Adj Price</th>
                <th>Price/Part</th>

                <th>Polybag 1</th>
                <th>Qty PB 1</th>
                <th>Price PCS 1</th>
                <th>Price/Part 1</th>

                <th>Polybag 2</th>
                <th>Qty PB 2</th>
                <th>Price PCS 2</th>
                <th>Price/Part 2</th>

                <th>Part No Foam</th>
                <th>Qty Foam</th>
                <th>Price Foam</th>
                <th>Adj % Foam</th>
                <th>Adj Price Foam</th>
                <th>Price/Part Foam</th>

                <th>Part No Tape</th>
                <th>Length</th>
                <th>Qty Tape</th>
                <th>Price Tape</th>
                <th>Adj % Tape</th>
                <th>Adj Price Tape</th>
                <th>Price/MM</th>
                <th>Price/Part Tape</th>

                <th>Vol/M</th>
                <th>Need Part/Day</th>
                <th>Need Box/Day</th>
                <th>Storage Pos</th>
                <th>Need Pos</th>
                <th>Storage Dur</th>
                <th>BPI/Day</th>
                <th>Total Box</th>
                <th>Price Box</th>
                <th>Total Box Price</th>
                <th>Month</th>
                <th>Planning</th>
                <th>Price/Part Box</th>

                <th>Total Packing Cost</th>

                <th>Box Name</th>
                <th>Box Color</th>

                <th>Vehicle</th>
                <th>Cap Box</th>
                <th>Cap PCS</th>

                <th>Distance Astimation</th>
                <th>BBM Cost</th>
                <th>Tol Price</th>
                <th>Operational Cost</th>
                <th>Transportasion Cost/PCS</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>'.$no.'</td>
                    <td style="mso-number-format:\@;">'.$data['item_fg_number'].'</td>
                    <td style="mso-number-format:\@;">'.$data['item_fg_name'].'</td>
                    <td style="mso-number-format:\@;">'.$data['customer_name'].'</td>
                    <td>'.$data['p_month'].'</td>
                    <td>'.$data['p_year'].'</td>
                    <td>'.$data['revision'].'</td>

                    <td>'.$data['dimension_part'].'</td>
                    <td style="mso-number-format:\@;">'.$data['item_rm_number_layer'].'</td>
                    <td>'.$data['qty_usage'].'</td>
                    <td>'.$data['qty_packing_standart'].'</td>
                    <td>'.$data['price_layer'].'</td>
                    <td>'.$data['adjustment'].'</td>
                    <td>'.$data['price_adjustment'].'</td>
                    <td>'.$data['price_part'].'</td>

                    <td>'.$data['polybag_size_1'].'</td>
                    <td>'.$data['qty_polybag_1'].'</td>
                    <td>'.$data['price_pcs_1'].'</td>
                    <td>'.$data['price_part_1'].'</td>

                    <td>'.$data['polybag_size_2'].'</td>
                    <td>'.$data['qty_polybag_2'].'</td>
                    <td>'.$data['price_pcs_2'].'</td>
                    <td>'.$data['price_part_2'].'</td>

                    <td style="mso-number-format:\@;">'.$data['item_rm_number_foam'].'</td>
                    <td>'.$data['qty_foam'].'</td>
                    <td>'.$data['price_foam'].'</td>
                    <td>'.$data['adjustment_foam'].'</td>
                    <td>'.$data['price_adjustment_foam'].'</td>
                    <td>'.$data['price_part_foam'].'</td>

                    <td style="mso-number-format:\@;">'.$data['item_rm_number_tape'].'</td>
                    <td>'.$data['length'].'</td>
                    <td>'.$data['qty_tape'].'</td>
                    <td>'.$data['price_tape'].'</td>
                    <td>'.$data['adjustment_tape'].'</td>
                    <td>'.$data['price_adjustment_tape'].'</td>
                    <td>'.$data['price_mm_tape'].'</td>
                    <td>'.$data['price_part_tape'].'</td>

                    <td>'.$data['volume'].'</td>
                    <td>'.$data['need_part_day'].'</td>
                    <td>'.$data['need_box_day'].'</td>
                    <td>'.$data['storage_pos'].'</td>
                    <td>'.$data['need_pos_day'].'</td>
                    <td>'.$data['storage_duration'].'</td>
                    <td>'.$data['storage_bpi_day'].'</td>
                    <td>'.$data['total_need_box'].'</td>
                    <td>'.$data['box_price'].'</td>
                    <td>'.$data['total_box_price'].'</td>
                    <td>'.$data['month'].'</td>
                    <td>'.$data['planning'].'</td>
                    <td>'.$data['price_part_box'].'</td>

                    <td>'.$data['total_packing_cost'].'</td>

                    <td>'.$data['box_name'].'</td>
                    <td>'.$data['color'].'</td>

                    <td>'.$data['vehicle_name'].'</td>
                    <td>'.$data['armada_cap_box'].'</td>
                    <td>'.$data['armada_cap_pcs'].'</td>

                    <td>'.$data['distance_astimation'].'</td>
                    <td>'.$data['bbm_cost'].'</td>
                    <td>'.$data['tol_price'].'</td>
                    <td>'.$data['operation'].'</td>
                    <td>'.$data['transportasion_cost_pcs'].'</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
