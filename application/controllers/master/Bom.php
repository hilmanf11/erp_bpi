<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Bom extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/bom');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('bom', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    public function readItem()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_rm = $this->crud->query("SELECT a.*, b.name as item_family_name 
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id 
        JOIN item_categories c ON a.item_category_id = c.id 
        WHERE a.status = 0 AND (a.item_category_id = 'C01' or (a.item_category_id = 'C09' AND a.item_family_id = 'P23')) AND (a.number like '%$post%' or a.name like '$post') 
        ORDER BY a.number ASC");
        $item_fg = $this->crud->query("SELECT * FROM item_fg WHERE `type` = 'SA' AND status = 0 AND (number like '%$post%' or name like '$post')");

        $datas = array();
        foreach ($item_rm as $rm) {
            $datas[] = array(
                "id" => $rm->id,
                "number" => $rm->number,
                "name" => $rm->name,
                "uom" => $rm->uom,
                "item_family_name" => $rm->item_family_name,
                "type" => "RM",
            );
        }

        foreach ($item_fg as $fg) {
            $datas[] = array(
                "id" => $fg->id,
                "number" => $fg->number,
                "name" => $fg->name,
                "uom" => $fg->uom,
                "item_family_name" => "SUB ASSY",
                "type" => $fg->type,
            );
        }

        echo json_encode($datas);
    }

     //GET DATA
     public function readWeight()
     {
        $post = $this->input->post();
        $item_fg = $this->crud->read("item_fg", [] ,["id" => $post['item_fg_id']]);
        echo json_encode($item_fg);
    }

    //GET DATA
//     public function readRunner()
//     {
//        $post = $this->input->post();
//        $item_fg_id = $post['item_fg_id'];
//        $menu_loading = $this->crud->query("SELECT SUM(a.runner) as runner, b.cavity_standard
//        FROM menu_loadings a JOIN molds b on a.mold_id = b.id
//        WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");
//        echo json_encode($menu_loading);
//    }

   public function readRunner()
    {
        $post = $this->input->post();
        $item_fg_id = $post['item_fg_id'];
        $menu_loading = $this->crud->query("SELECT ( a.runner / b.cavity_standard ) as total_runner
        FROM menu_loadings a 
        JOIN molds b ON a.mold_id = b.id
        WHERE a.item_fg_id = '$item_fg_id' 
        ORDER BY total_runner DESC
        LIMIT 1");
        echo json_encode($menu_loading);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('b.number');
            $this->db->order_by('a.created_date', 'DESC');
            // $this->db->order_by('b.number', 'ASC');
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
            $number = base64_decode($this->input->get('number'));
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $this->db->select('a.*, 
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN a.item_rm_id
                ELSE a.item_fg_sa_id 
            END) AS selected_item_id,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.number
                ELSE e.number
            END) AS selected_item_number,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.name
                ELSE e.name
            END) AS selected_item_name,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.uom
                ELSE e.uom
            END) AS selected_item_uom,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN d.name
                ELSE "SUB ASSY"
            END) AS selected_item_prodfam');
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id','left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
            $this->db->join('item_familys d', 'c.item_family_id = d.id', 'left');
            $this->db->join('item_fg e', 'a.item_fg_sa_id = e.id','left');
            $this->db->where('b.number', $number);
            // $this->db->like('a.item_rm_id', $filter_item_rm_id); // bentrok dengan datagrid sub assy
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            // $this->db->select('a.*,c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as item_family_name');
            $this->db->select('a.*, 
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.id
                ELSE e.id
            END) AS item_rm_id,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.number
                ELSE e.number
            END) AS item_rm_number,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.name
                ELSE e.name
            END) AS item_rm_name,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN "RM"
                ELSE "SA"
            END) AS type_item,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.uom
                ELSE e.uom
            END) AS uom,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN d.name
                ELSE "SUB ASSY"
            END) AS item_family_name');
            
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
            $this->db->join('item_familys d', 'c.item_family_id = d.id', 'left');
            $this->db->join('item_fg e', 'a.item_fg_sa_id = e.id','left');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    // public function create_SA()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();

    //         $bom = $this->crud->read("bom", [], ["item_fg_id" => $post['item_fg_id'], "item_fg_sa_id" => $post['item_fg_sa_id']]);
    //         if (@$bom->item_fg_id != "") {
    //             $send = $this->crud->update('bom', ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
    //         } else {
    //             $send = $this->crud->create('bom', $post);
    //         }
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         var_dump($post);
    //         $bom = $this->crud->read("bom", [], ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
    //         if (@$bom->item_fg_id != "") {
    //             $send = $this->crud->update('bom', ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
    //         } else {
    //             $send = $this->crud->create('bom', $post);
    //         }
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create_SA()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            // $bom = $this->crud->read("bom", [], ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
           
            $dataFinal = array(
                //field
                "item_fg_sa_id" => $post['item_fg_sa_id'],
                "type" => $post['type'],
                "recyle" => $post['recyle'],
                "composition" => $post['composition'],
                "remark" => $post['remark'],
            );
            
            if (@$post['id'] != "") {
                $send = $this->crud->update('bom', ["id" => $post['id']], $dataFinal);
            } else {
                $send = $this->crud->create('bom', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            // $bom = $this->crud->read("bom", [], ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
           
            $dataFinal = array(
                //field
                "item_rm_id" => $post['item_rm_id'],
                "type" => $post['type'],
                "recyle" => $post['recyle'],
                "composition" => $post['composition'],
                "remark" => $post['remark'],
            );
            
            if (@$post['id'] != "") {
                $send = $this->crud->update('bom', ["id" => $post['id']], $dataFinal);
            } else {
                $send = $this->crud->create('bom', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    // public function delete()
    // {
    //     $data = $this->input->post();
    //     var_dump($data);
    //     $send = $this->crud->delete('bom', $data);
    //     echo $send;
    // }

    public function delete()
    {
        $data = $this->input->post();
        $bom_item_rm_id = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
        
        if (!empty($bom_item_rm_id)) {
            $dataFinal = [
                "item_fg_id" => $data['item_fg_id'],
                "item_rm_id" => $data['item_rm_id']
            ];
        } else {
            $dataFinal = [
                "item_fg_id" => $data['item_fg_id'],
                "item_fg_sa_id" => $data['item_rm_id']
            ];
        }

        $send = $this->crud->delete('bom', $dataFinal);
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
                'item_fg_id' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'type' => $data->val($i, 4),
                'recyle' => $data->val($i, 5),
                'composition' => $data->val($i, 6),
                'remark' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/bom.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/bom.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/bom.txt";
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
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);

            $item_fg_id = $data['item_fg_id'];
            $menu_loading = $this->crud->query("SELECT a.item_fg_id, SUM(a.runner) as runner, b.cavity_standard
            FROM menu_loadings a JOIN molds b on a.mold_id = b.id
            WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");

            
            $bom = $this->crud->read('bom', [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] ." Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_rm_id'] ." Not Found", "theme" => "error"));
            } elseif (empty($menu_loading[0]->item_fg_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] . " in Menu Loading Not Found", "theme" => "error"));
            } elseif ($item_rm->item_family_id == 'P06' && $data['composition'] != "") {
                echo json_encode(array("title" => "Alert", "message" => "Part ID" . $data['item_rm_id'] ." Product Family is VIRGIN ", "theme" => "error"));
            } elseif (!empty($bom->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part ID" . $data['item_rm_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                 // Hitung nilai untuk field composition
                $weight = $item_fg->weight;
                $runner = $menu_loading[0]->runner;
                $cavity_standard = $menu_loading[0]->cavity_standard;

                // if ($item_rm->item_family_id == 'P06') {
                //     $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
                // } elseif ($item_rm->item_family_id != 'P06') {
                //     $dataFinal['composition'] = $data['composition'];
                // }

                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "item_rm_id" => $data['item_rm_id'],
                    "type" => $data['type'],
                    "recyle" => $data['recyle'],
                    "remark" => $data['remark'],
                );

                if ($item_rm->item_family_id == 'P06') {
                    $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
                } elseif ($item_rm->item_family_id != 'P06') {
                    $dataFinal['composition'] = $data['composition'];
                }

                $send   = $this->crud->create('bom', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bom_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as product_family, c.uom as uom, , d.name as product_family_name');
        $this->db->select('a.*, b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name, a.created_by, a.created_date, a.updated_by, a.updated_date ,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN a.item_rm_id
                ELSE a.item_fg_sa_id 
            END) AS selected_item_id,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.number
                ELSE e.number
            END) AS selected_item_number,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.name
                ELSE e.name
            END) AS selected_item_name,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN c.uom
                ELSE e.uom
            END) AS selected_item_uom,
            (CASE 
                WHEN a.item_fg_sa_id IS NULL THEN d.name
                ELSE "SUB ASSY"
            END) AS selected_item_prodfam');
        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id','left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        $this->db->join('item_familys d', 'c.item_family_id = d.id', 'left');
        $this->db->join('item_fg e', 'a.item_fg_sa_id = e.id','left');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        // $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#bom {border-collapse: collapse;width: 100%;font-size: 12px;}#bom td, #bom th {border: 1px solid #ddd;padding: 2px;}#bom tr:nth-child(even){background-color: #f2f2f2;}#bom tr:hover {background-color: #ddd;}#bom th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="bom" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Part ID</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Type of Product</th>
                <th>% Recycle Part</th>
                <th>Product Family</th>
                <th>Unit Of Measure</th>
                <th>Composition</th>
                <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['selected_item_id'] . '</td>
                    <td>' . $data['selected_item_number'] . '</td>
                    <td>' . $data['selected_item_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['recyle'] . '</td>
                    <td>' . $data['selected_item_prodfam'] . '</td>
                    <td>' . $data['selected_item_uom'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['composition'] . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
