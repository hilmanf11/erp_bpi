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
        $send = $this->crud->query("SELECT DISTINCT 
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
        AND b.number LIKE '%$post%' or a.lot_no LIKE '%$post%' or a.wo_no LIKE '%$post%' or a.period LIKE '%$post%' 
        ORDER BY b.number DESC");
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

        @$send = $this->crud->delete('item_ng', ["id" => $data['id']]);
        echo $send;
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_ng', $data);
        echo $send;
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
