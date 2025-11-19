<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Upload_stock_whs_tf extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');

        //VALIDASI FORM
        $this->form_validation->set_rules('item_rm_id', 'Item RM', 'required|min_length[1]|max_length[30]|is_unique[upload_stock_whs_tf.item_rm_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/upload_stock_whs_tf');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('upload_stock_whs_tf', ["item_rm_id" => $post]);
        echo json_encode($send);
    }

    public function readArea()
    {
        $data = $this->crud->query("SELECT DISTINCT area FROM warehouse_locations WHERE `status` = '0' ORDER BY area ASC");
        echo json_encode($data);
    }

    public function datatableHistories()
    {
        if ($this->input->get()) {
            $item_rm_id = base64_decode($this->input->get('item_rm_id'));

            $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name');
            $this->db->from('upload_stock_whs_tf_histories a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.item_rm_id', $item_rm_id);
            $this->db->order_by('a.created_date', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_category = @base64_decode($get['filter_category']);
            $filter_product_family = @base64_decode($get['filter_product_family']);
            $filter_product_family_sub = @base64_decode($get['filter_product_family_sub']);
            $filter_item_rm = @base64_decode($get['filter_item_rm']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as category_name, d.name as product_family_name, e.name as product_family_sub_name');//
            $this->db->from('upload_stock_whs_tf a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->join('item_familys d', 'b.item_family_id = d.id');
            $this->db->join('item_family_subs e', 'b.item_sub_family_id = e.id','left');
            if($filter_from != "" && $filter_to != ""){
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if (!empty($filter_category)) {
            $this->db->like('c.id', $filter_category);
            }
            if (!empty($filter_product_family)) {
                $this->db->like('d.id', $filter_product_family);
            }
            if (!empty($filter_product_family_sub)) {
                $this->db->like('e.id', $filter_product_family_sub);
            }
            if (!empty($filter_item_rm)) {
                $this->db->like('a.item_rm_id', $filter_item_rm);
            }
            $this->db->order_by('a.trans_date', 'DESC');

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
            $upload_stock_whs_tf = $this->crud->read("upload_stock_whs_tf", [], ["item_rm_id" => $post['item_rm_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $post['item_rm_id']]);


            if (@$post['cutoff'] == "") {
                $date = date("Y-m-d");
            } else {
                $date = $post['cutoff'];
            }
            $item_rm_id = $post['item_rm_id'];
            $query = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
            (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            if ($query && count($query) > 0) {
                $ending_stock = $query[0]->ending_stock;
            } else {
                $ending_stock = 0;
            }

            if($post['qty_from'] + $post['qty_to'] <= $ending_stock){
                if (!empty($upload_stock_whs_tf)) {
                    $send = $this->crud->update('upload_stock_whs_tf', ["item_rm_id" => $post['item_rm_id']], $post);
                    $send2 = $this->crud->create('upload_stock_whs_tf_histories', $post);
                } else {
                    $send = $this->crud->createNotLog('upload_stock_whs_tf', $post);
                    $send2 = $this->crud->create('upload_stock_whs_tf_histories', $post);
                }
                echo $send;
            }else{
                echo json_encode(array("title" => "Qty Over", "message" => "Qty From + Qty To > Ending Balance RM is " . $ending_stock , "theme" => "error"));
                return;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();
    //         $send = $this->crud->update('upload_stock_whs_tf', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('upload_stock_whs_tf', $data);
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
                'item_number' => $data->val($i, 2),
                'trans_date' => $data->val($i, 3),
                'cutoff' => $data->val($i, 4),
                'transfer_from' => $data->val($i, 5),
                'transfer_to' => $data->val($i, 6),
                'qty_from' => $data->val($i, 7),
                'qty_to' => $data->val($i, 8)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/upload_stock_whs_tf.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/upload_stock_whs_tf.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/upload_stock_whs_tf.txt";
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
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['item_number']]);
            $upload_stock_whs_tf = $this->crud->read('upload_stock_whs_tf', [], ["item_rm_id" => $item_rm->id]);
            
            if (@$data['cutoff'] == "") {
                $date = date("Y-m-d");
            } else {
                $date = $data['cutoff'];
            }
            $item_rm_id = $item_rm->id;
            $query = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
            (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS ending_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date <= '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date <= '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date <= '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            $ending_stock = ($query && count($query) > 0) ? $query[0]->ending_stock : 0;

            // default response
            $response = null;

            if ($data['qty_from'] + $data['qty_to'] > $ending_stock) {
                $response = array("title" => "Qty Over", "message" => "Part No " . $data['item_number'] . " Qty From + Qty To > Ending Balance RM is " . $ending_stock , "theme" => "error");
            } elseif (!empty($upload_stock_whs_tf)) {
                $dataFinal = array(
                    //field
                    "item_rm_id" => $item_rm_id,
                    "trans_date" => $data['trans_date'],
                    "cutoff" => $data['cutoff'],
                    "transfer_from" => $data['transfer_from'],
                    "transfer_to" => $data['transfer_to'],
                    "qty_from" => $data['qty_from'],
                    "qty_to" => $data['qty_to']
                );

                $this->crud->update('upload_stock_whs_tf', ["item_rm_id" => $item_rm_id], $dataFinal);
                $this->crud->create('upload_stock_whs_tf_histories', $dataFinal);
                $response = array("title" => "Updated", "message" => "Data updated successfully", "theme" => "success");
            } elseif (empty($item_rm)) {
                $response = array("title" => "Not Found", "message" => "Part No " . $data['item_number'] . " is Not Found", "theme" => "error");
            } else {
                $dataFinal = array(
                    //field
                    "item_rm_id" => $item_rm_id,
                    "trans_date" => $data['trans_date'],
                    "cutoff" => $data['cutoff'],
                    "transfer_from" => $data['transfer_from'],
                    "transfer_to" => $data['transfer_to'],
                    "qty_from" => $data['qty_from'],
                    "qty_to" => $data['qty_to']
                );

                $this->crud->create('upload_stock_whs_tf', $dataFinal);
                $this->crud->create('upload_stock_whs_tf_histories', $dataFinal);
                $response = array("title" => "Created", "message" => "Data created successfully", "theme" => "success");
            }

            echo json_encode($response);
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=upload_stock_whs_tf_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_category = @base64_decode($get['filter_category']);
        $filter_product_family = @base64_decode($get['filter_product_family']);
        $filter_product_family_sub = @base64_decode($get['filter_product_family_sub']);
        $filter_item_rm = @base64_decode($get['filter_item_rm']);


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as category_name, d.name as product_family_name, e.name as product_family_sub_name');
        $this->db->from('upload_stock_whs_tf a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id','left');
        $this->db->join('item_categories c', 'b.item_category_id = c.id','left');
        $this->db->join('item_familys d', 'b.item_family_id = d.id','left');
        $this->db->join('item_family_subs e', 'b.item_sub_family_id = e.id','left');
        if($filter_from != "" && $filter_to != ""){
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        if (!empty($filter_category)) {
            $this->db->like('c.id', $filter_category);
        }
        if (!empty($filter_product_family)) {
            $this->db->like('d.id', $filter_product_family);
        }
        if (!empty($filter_product_family_sub)) {
            $this->db->like('e.id', $filter_product_family_sub);
        }
        if (!empty($filter_item_rm)) {
            $this->db->like('a.item_rm_id', $filter_item_rm);
        }
        $this->db->order_by('a.trans_date', 'DESC');
        $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#upload_stock_whs_tf {border-collapse: collapse;width: 100%;font-size: 12px;}#upload_stock_whs_tf td, #upload_stock_whs_tf th {border: 1px solid #ddd;padding: 2px;}#upload_stock_whs_tf tr:nth-child(even){background-color: #f2f2f2;}#upload_stock_whs_tf tr:hover {background-color: #ddd;}#upload_stock_whs_tf th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>UPLOAD STOCK WAREHOUSE TRANSFER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:30%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>Cut Off</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_from . ' to ' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            
            <table id="upload_stock_whs_tf" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Part ID</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Product Family Sub</th>
                    <th>Trans Date</th>
                    <th>Cut Off</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Qty From</th>
                    <th>Qty To</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['item_rm_id'] . '</td>
                            <td>' . $data['item_rm_number'] . '</td>
                            <td>' . $data['item_rm_name'] . '</td>
                            <td>' . $data['category_name'] . '</td>
                            <td>' . $data['product_family_name'] . '</td>
                            <td>' . $data['product_family_sub_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['cutoff'] . '</td>
                            <td>' . $data['transfer_from'] . '</td>
                            <td>' . $data['transfer_to'] . '</td>
                            <td>' . number_format($data['qty_from'],2) . '</td>
                            <td>' . number_format($data['qty_to'],2) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
    }   
}
