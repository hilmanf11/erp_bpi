<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Item_ng extends CI_Controller
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
        //Validasi Form
        $this->form_validation->set_rules('workorder', 'Workorder No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('planning/item_ng');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_ng', ["name" => $post]);
        echo json_encode($send);
    }

    public function readDocument()
    {
        $send = $this->crud->query("SELECT DISTINCT document FROM item_ng order by document desc");
        echo json_encode($send);
    }

    public function item_ng_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $year       = date("Y", strtotime($trans_date));
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT MAX(SUBSTR(document, -4, 4)) as kode FROM item_ng WHERE trans_date like '%$year%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo "NG-" . $datenow . "-" . $autoID;
    }

    // public function readWorkorders()
    // {
    //     $send = $this->crud->query("SELECT DISTINCT a.wo_no, a.period, a.item_fg_id, a.item_fg_name, a.qty, b.number as item_fg_number 
    //     FROM production_schedules a
    //     JOIN item_fg b ON a.item_fg_id = b.id
    //     WHERE a.status = '0'
    //     order by a.wo_no desc");
    //     echo json_encode($send);
    // }

    public function readWorkorders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $kind = $this->input->get('kind');
        if($kind == 'Ng for Req Material'){
            $send = $this->crud->query("
                SELECT DISTINCT 
                    a.wo_no AS wo_no, 
                    a.period AS period, 
                    a.qty AS qty, 
                    a.lot_no as lot_no, 
                    a.item_fg_id AS item_fg_id, 
                    a.item_fg_name AS product_name, 
                    b.number AS product_no,
                    a.division as division,
                    b.status_subcont,
                    b.subcont_type
                FROM production_schedules a
                JOIN item_fg b ON a.item_fg_id = b.id
                WHERE a.status = 0 
                AND a.wo_no != '' 
                AND (b.number LIKE '%$post%' 
                    OR a.lot_no LIKE '%$post%' 
                    OR a.wo_no LIKE '%$post%' 
                    OR a.period LIKE '%$post%')
                ORDER BY b.number DESC
            ");
        } else {
            $send = $this->crud->query("
                SELECT DISTINCT 
                    a.wo_no AS wo_no, 
                    a.period AS period, 
                    a.qty AS qty, 
                    a.lot_no as lot_no, 
                    a.item_fg_id AS item_fg_id, 
                    a.item_fg_name AS product_name, 
                    b.number AS product_no,
                    a.division as division,
                    b.status_subcont,
                    b.subcont_type
                FROM production_schedules a
                JOIN item_fg b ON a.item_fg_id = b.id
                WHERE a.wo_no != '' 
                AND (b.number LIKE '%$post%' 
                    OR a.lot_no LIKE '%$post%' 
                    OR a.wo_no LIKE '%$post%' 
                    OR a.period LIKE '%$post%')
                ORDER BY b.number DESC
            ");
        }

        echo json_encode($send);
    }

    public function checkWo_no($wo_no, $item_fg_id)
    {
        $wono = base64_decode($wo_no);
        $item_fg_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT COALESCE(SUM(qty_product),0) as qty
            FROM item_ng
            WHERE workorder = '$wono' and item_fg_id = '$item_fg_id' and no_urut = 1
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function readItems($workorder)
    {
        $workorders = base64_decode($workorder);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty_req as qty, b.uom, COALESCE(d.scrap, 0) as scrap
        FROM supply_sheets a
        JOIN item_rm b ON a.item_rm_id = b.id
        LEFT JOIN (SELECT item_rm_id, wo_no, SUM(qty) as scrap FROM scraps GROUP BY item_rm_id, wo_no) d ON a.item_rm_id = d.item_rm_id and a.workorder = d.wo_no
        WHERE a.workorder = '$workorders' and b.status = '0'
        order by b.number asc");
        echo json_encode($send);
    }

    public function datatablesTemp()
    {
        $workorder = base64_decode($this->input->get('workorder'));
        $qty_product = $this->input->get('qty_product');
        $qty_sh = $this->input->get('qty_sh');

        //var_dump($workorder);

        $this->db->select('b.id, b.number, b.name, b.uom, COALESCE(d.scrap, 0) as scrap, 
        ROUND('.$qty_sh.' * COALESCE(c.composition, 1), 4) as qty, ROUND('.$qty_product.' * COALESCE(c.composition, 1), 4) as ng ');
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('bom c', 'a.item_rm_id = c.item_rm_id and a.item_fg_id = c.item_fg_id','left');
        $this->db->join('(SELECT item_rm_id, wo_no, SUM(qty) as scrap FROM scraps GROUP BY item_rm_id, wo_no) d', 'a.item_rm_id = d.item_rm_id AND a.workorder = d.wo_no', 'left');
        $this->db->where('a.workorder',$workorder);
        $this->db->where('b.status', 0);
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();
        //echo $this->db->last_query();

        $id = 1;
        $obj = []; 
        foreach ($records as $record) {
            $obj[] = array(
                "no_id" => $id,
                "item_rm_id" => $record['id'],
                "number" => $record['number'],
                "name" => $record['name'],
                "stock" => $record['qty'],
                "qty" => $record['ng'],
                "uom" => $record['uom'],
                "scrap" => $record['scrap']
            );
            $id++;
        }

        $arr['rows'] = $obj;
        die(json_encode($arr));
    }


    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_document = $this->input->get('filter_document');
            $filter_family_id = $this->input->get('filter_family_id');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.number as product_no, c.name as product_name');
            $this->db->from('item_ng a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.document', $filter_document);
            $this->db->like('b.item_family_id', $filter_family_id);
            $this->db->like('c.id', $filter_item_fg_id);
            $this->db->group_by('a.document');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('a.document', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1-10
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
            $document = base64_decode($this->input->get('document'));

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('item_ng a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
            $this->db->where('a.document', $document);
            // $this->db->like('a.item_rm_id', $filter_item_rm_id); // bentrok dengan datagrid sub assy
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
         }
     }
 

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post = $this->input->post();
    //             $itemNg = $this->crud->reads("item_ng", [], ["item_rm_id" => $post['item_rm_id'], "workorder" => $post['workorder']]);

                
    //             if (count($itemNg) > 0) {
    //                 echo json_encode(array("title" => "Duplicate", "message" => "Data has been created", "theme" => "error"));
    //             } else {
    //                 if ($post['scrap'] > 0) {
    //                     $this->crud->create('scraps', [
    //                         "item_rm_id" => $post['item_rm_id'],
    //                         "trans_date" => $post['trans_date'],
    //                         "document" => $post['document_scrap'],
    //                         "wo_no" => $post['workorder'],
    //                         "type" => $post['type'],
    //                         "period" => $post['period'],
    //                         "qty" => $post['scrap'],
    //                         "uom" => $post['uom'],
    //                         "remarks" => $post['remarks'],
    //                     ]);

    //                     $document_scrap = array("document_scrap" => $post['document_scrap']);
    //                 } else {
    //                     $document_scrap = array("document_scrap" => "-");
    //                 }

    //                 $send = $this->crud->create('item_ng', array_replace($post, $document_scrap));
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
                $post = $this->input->post();
                $document = $this->db->escape($post['document']); // Escape variable to prevent SQL injection

                // Hitung jumlah data yang sudah ada dengan dokumen yang sama
                $existingCountQuery = $this->crud->query("SELECT COUNT(*) AS count FROM item_ng WHERE document = $document");

                // Akses hasil dari query sebagai objek
                $existingCount = isset($existingCountQuery[0]->count) ? $existingCountQuery[0]->count : 0;

                // Tambahkan 1 ke existingCount untuk membuat nomor urut baru
                $newSequence = $existingCount + 1;

                if ($post['qty'] > 0) {
                    $this->crud->create('scraps', [
                        "item_rm_id" => $post['item_rm_id'],
                        "trans_date" => $post['trans_date'],
                        "document" => $post['document_scrap'],
                        "wo_no" => $post['workorder'],
                        "type" => $post['type'],
                        "period" => $post['period'],
                        "qty" => $post['qty'],
                        "uom" => $post['uom'],
                        // "remarks" => $post['remarks'],
                    ]);

                    $document_scrap = ["document_scrap" => $post['document_scrap']];
                } else {
                    $document_scrap = ["document_scrap" => "-"];
                }

                // Gabungkan nomor urut baru dengan data lainnya dan buat catatan di tabel item_ng
                $dataToInsert = array_replace($post, $document_scrap, ["no_urut" => $newSequence]);
                $send = $this->crud->create('item_ng', $dataToInsert);

                echo $send;
            } else {
                show_error(validation_errors());
            }
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

            $itemNg = $this->crud->read("item_ng", [], ["id" => $id]);
            $scraps = $this->crud->reads("scraps", [], ["document" => @$itemNg->document_scrap, "item_rm_id" => @$itemNg->item_rm_id, "trans_date" => @$itemNg->trans_date]);
            if (count($scraps) > 0) {
                $send = $this->crud->update('scraps', [
                    "document" => @$itemNg->document_scrap,
                    "item_rm_id" => @$itemNg->item_rm_id,
                    "trans_date" => @$itemNg->trans_date
                ], ["qty" => $post['scrap']]);
            }

            $send = $this->crud->update('item_ng', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        @$itemNg = $this->crud->reads("item_ng", [], ["id" => $data['id']]);

        $document = @$itemNg[0]->document_scrap;
        $item_rm_id = @$itemNg[0]->item_rm_id;
        $trans_date = @$itemNg[0]->trans_date;

        $scraps = $this->crud->reads("scraps", [], ["document" => $document, "item_rm_id" => $item_rm_id, "trans_date" => $trans_date]);

        if (count($scraps) > 0) {
            $this->crud->delete('scraps', [
                "document" => @$document,
                "item_rm_id" => @$item_rm_id,
                "trans_date" => @$trans_date
            ]);
        }

        @$send = $this->crud->delete('item_ng', ["document" => $data['document']]);
        echo $send;
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_ng', $data);
        echo $send;
    }

    //UPLOAD DATA
    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);
    //     for ($i = 3; $i <= $total_row; $i++) {
    //         $datas[] = array(
    //             //excel
    //             'trans_date' => $data->val($i, 2),
    //             'process' => $data->val($i, 3),
    //             'kind' => $data->val($i, 4),
    //             'type' => $data->val($i, 5),
    //             'workorder' => $data->val($i, 6),
    //             'item_number' => $data->val($i, 7),
    //             'qty_product' => $data->val($i, 8),
    //             'shift' => $data->val($i, 9)
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }

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

        $datas = [];

        for ($i = 3; $i <= $total_row; $i++) {
            // buat key unik untuk grouping (gabungan beberapa field penting)
            $key = $data->val($i, 2) . '|' . 
                $data->val($i, 3) . '|' . 
                $data->val($i, 4) . '|' . 
                $data->val($i, 5) . '|' . 
                $data->val($i, 6) . '|' . 
                $data->val($i, 7) . '|' . 
                $data->val($i, 9);

            if (isset($datas[$key])) {
                $datas[$key]['qty_product'] += (float)$data->val($i, 8);
            } else {
                $datas[$key] = [
                    'trans_date'  => $data->val($i, 2),
                    'process'     => $data->val($i, 3),
                    'kind'        => $data->val($i, 4),
                    'type_code'   => $data->val($i, 5),
                    'workorder'   => $data->val($i, 6),
                    'item_number' => $data->val($i, 7),
                    'qty_product' => (float)$data->val($i, 8),
                    'shift'       => $data->val($i, 9),
                ];
            }
        }

        // ubah associative array menjadi numerik array
        $result = array_values($datas);
        $result['total'] = count($result);

        echo json_encode($result);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/item_ng.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_ng.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_ng.txt";
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

            $production_schedule = $this->crud->read('production_schedules',[],array("wo_no" => $data['workorder']));
            $item_fg = $this->crud->read('item_fg',[],array("number" => $data['item_number']));
            $types = [];
            if (!empty($data['type_code'])) {
                $codes = array_map('trim', explode(',', $data['type_code']));
                $master_ngs = $this->db->where_in('code', $codes)->get('master_ng')->result();

                foreach ($master_ngs as $ng) {
                    $types[] = $ng->name;
                }
            }

            $type_name = implode(', ', $types);

            $accumulate_sh = $this->db->query("SELECT COALESCE(SUM(qty_product),0) as qty FROM item_ng WHERE workorder = '{$data['workorder']}' AND item_fg_id = '{$item_fg->id}' AND no_urut = 1")->row();
            $accumulate_qty = !empty($accumulate_sh) ? $accumulate_sh->qty : 0;

            // ambil records supply_sheets
            $this->db->select("
                a.item_fg_id, 
                a.item_rm_id,
                b.id, 
                b.number, 
                b.name, 
                b.uom, 
                COALESCE(d.scrap, 0) as scrap, 
                ROUND({$production_schedule->qty} * COALESCE(c.composition, 1), 4) as qty, 
                ROUND({$data['qty_product']} * COALESCE(c.composition, 1), 4) as ng,
                a.period
            ");
            $this->db->from('supply_sheets a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('bom c', 'a.item_rm_id = c.item_rm_id and a.item_fg_id = c.item_fg_id','left');
            $this->db->join('(SELECT item_rm_id, wo_no, SUM(qty) as scrap 
                            FROM scraps 
                            GROUP BY item_rm_id, wo_no) d', 
                            'a.item_rm_id = d.item_rm_id AND a.workorder = d.wo_no', 'left');
            $this->db->where('a.workorder', $data['workorder']);
            $this->db->where('b.status', 0);
            $this->db->order_by('b.number', 'asc');
            $records = $this->db->get()->result_array();

            
            if (!$production_schedule || empty($production_schedule->id)) {
                echo json_encode(array("title" => "Not Found","message" => "Workorder " . $data['workorder'] . " NOT FOUND","theme" => "error"));
                // return;
            } elseif ($data['kind'] === 'Ng for Req Material' && $production_schedule->status == 1) {
                echo json_encode(["title"   => "Invalid","message" => "Workorder " . $data['workorder'] . " Already CLOSE, Cannot input Ng for Req Material","theme"   => "error"]);
                //return;
            }else{

                // generate autonumber (satu nomor untuk semua row dalam upload ini)
                $trans_date = $data['trans_date'];
                $year       = date("Y", strtotime($trans_date));
                $datenow    = date("ymd", strtotime($trans_date));
                $sqlGetID   = $this->db->query("
                    SELECT MAX(RIGHT(document,4)) as kode 
                    FROM item_ng 
                    WHERE YEAR(trans_date) = '$year'
                ");
                $rowID = $sqlGetID->row();
                $kode  = $rowID->kode;

                $urutan = ($kode == NULL) ? 1 : ((int) $kode + 1);
                $autoID = sprintf("%04s", $urutan);
                $autonumber = "NG-" . $datenow . "-" . $autoID;

                // insert ke item_ng
                foreach ($records as $row) {
                    $dataFinal = array(
                        "item_fg_id"    => $row['item_fg_id'],
                        "item_rm_id"    => $row['item_rm_id'],
                        "trans_date"    => $data['trans_date'],
                        "document"      => $autonumber,
                        "departement"   => 'PRODUCTION',
                        "process"       => $data['process'],
                        "type"          => $type_name,
                        "workorder"     => $data['workorder'],
                        "period"        => $row['period'],
                        "stock"         => $row['qty'],
                        "qty"           => $row['ng'],
                        "qty_sh"        => $production_schedule->qty,
                        "qty_product"   => $data['qty_product'],
                        "accumulate_sh" => $accumulate_qty,
                        "uom"           => $row['uom'],
                        "kind"          => $data['kind'],
                        "shift"         => $data['shift'],
                    );
                    $send = $this->crud->create('item_ng', $dataFinal);
                }

                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=item_ng_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_document = $this->input->get('filter_document');
        $filter_family_id = $this->input->get('filter_family_id');
        $filter_item_id = $this->input->get('filter_item_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, c.number as item_fg_number, c.name as item_fg_name');
        $this->db->from('item_ng a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.document', $filter_document);
        $this->db->like('b.item_family_id', $filter_family_id);
        $this->db->like('b.id', $filter_item_id);
        $this->db->order_by('a.trans_date', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>ITEMS NG TRANSACTION</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>

            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Trans Date</th>
                    <th>Document No</th>
                    <th>Departement</th>
                    <th>Process</th>
                    <th>NG Type</th>
                    <th>Work Order</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty Product NG</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Qty</th>
                    <th>Uom</th>
                    <th>Remarks</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document'] . '</td>
                            <td>' . $data['departement'] . '</td>
                            <td>' . $data['process'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['qty_product'] . '</td>
                            <td>' . $data['item_rm_number'] . '</td>
                            <td>' . $data['item_rm_name'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td style="text-align:center;">' . $data['uom'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td style="text-align:center;">' . $data['created_by'] . '</td>
                            <td style="text-align:center;">' . $data['created_date'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function getNgTypes()
    {
        $this->db->select('name');
        $this->db->from('master_ng');
        $this->db->where('deleted', 0);
        $query = $this->db->get();
        $result = $query->result_array();
        echo json_encode($result);
    }
}
