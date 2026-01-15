<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Master_mpq_material_crushers extends CI_Controller
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
            $this->load->view('master/master_mpq_material_crushers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.number like '%$post%' or a.name like '$post'");
        echo json_encode($send);
    }

    public function readItems()
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $item_family_id = explode(",", $this->input->get('item_family_id'));
       
       $this->db->select('a.*,a.number as item_number, a.name as item_name');//c.specification
       $this->db->from('item_rm a');
       $this->db->where_in('a.item_family_id', $item_family_id);
       $this->db->where('a.status', 0);
       $this->db->like('a.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readFamily($categoryId)
    {
        $this->db->select("a.*,a.name, a.account_name, a.account_number, COALESCE(c.number,'-') as division_number");//c.specification
        $this->db->from('item_familys a');
        $this->db->join('item_categories b','a.item_category_id = b.id');
        $this->db->join('divisions c','a.division_id = c.id','left');
        $this->db->where('a.item_category_id', $categoryId);
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function read($id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.item_family_id like '%$id%'");
        echo json_encode($send);
    }

    public function readItemRm()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT a.id, a.number, a.name, a.uom
        FROM item_rm a
        -- LEFT JOIN supplier_items b ON a.id = b.item_rm_id
        WHERE a.status = '0' 
            AND a.division = 'INJ' 
            AND item_category_id = 'C11' 
            AND item_family_id = 'P05' 
            AND (a.number LIKE '%$post%' OR a.name LIKE '%$post%')
        ORDER BY a.number ASC");
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
            b.id as item_rm_id,
            b.name as item_name, 
            b.number as item_number, 
            b.uom, 
            b.division, 
            c.name as item_category_name, 
            d.name as item_family_name, 
            e.name as item_sub_family_name');
            $this->db->from('master_mpq_material_crushers a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->join('item_familys d', 'b.item_family_id = d.id');
            $this->db->join('item_family_subs e', 'b.item_sub_family_id = e.id','left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "item_category_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "item_sub_family_name") {
                        $this->db->like("e.name", $filter->value);
                    } elseif ($filter->field == "item_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "item_number") {
                        $this->db->like("b.number", $filter->value);
                    } elseif ($filter->field == "uom") {
                        $this->db->like("b.uom", $filter->value);
                    } elseif ($filter->field == "division") {
                        $this->db->like("b.division", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            // $this->db->order_by('a.id', 'ASC');
            $this->db->order_by('a.created_date', 'DESC');
            $this->db->order_by('a.updated_date', 'DESC');
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
            $items = $this->crud->reads("master_mpq_material_crushers", [], ["item_rm_id" => $post['item_rm_id']]);

            if (count($items) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Data has been created", "theme" => "error"));
            } else {
                $send   = $this->crud->create('master_mpq_material_crushers', $post);
                echo $send;
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
            $send = $this->crud->update('master_mpq_material_crushers', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('master_mpq_material_crushers', $data);
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
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'mpq' => $data->val($i, 4),
                'status' => $data->val($i, 5)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/master_mpq_material_crushers.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/master_mpq_material_crushers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/master_mpq_material_crushers.txt";
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
            //Cek Process Number          //table       //field        //field excel
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['number']]);
            $items = $this->crud->reads("master_mpq_material_crushers", [], ["item_rm_id" => $item_rm->id]);
            
            if (!empty($items)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "item_rm_id" => $item_rm->id,
                    "item_number" => $data['number'],
                    "item_name" => $data['name'],
                    "uom" => $item_rm->uom,
                    "mpq" => $data['mpq'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('master_mpq_material_crushers', $dataFinal);
                echo $send;
            }
        }
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment; filename=item_rm_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
        b.id as item_rm_id,
        b.name as item_name, 
        b.number as item_number, 
        b.uom, 
        b.division, 
        c.name as item_category_name, 
        d.name as item_family_name, 
        e.name as item_sub_family_name');
        $this->db->from('master_mpq_material_crushers a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_categories c', 'b.item_category_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->join('item_family_subs e', 'b.item_sub_family_id = e.id','left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#master_mpq_material_crushers {border-collapse: collapse;width: 100%;font-size: 12px;}#master_mpq_material_crushers td, #master_mpq_material_crushers th {border: 1px solid #ddd;padding: 2px;}#master_mpq_material_crushers tr:nth-child(even){background-color: #f2f2f2;}#master_mpq_material_crushers tr:hover {background-color: #ddd;}#master_mpq_material_crushers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MPQ MATERIAL CRUSHERS</h3>
            </div>
        </center>
        
        <table id="master_mpq_material_crushers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Part Name</th>
                <th>UOM</th>
                <th>Division</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Product Family Sub</th>
                <th>Mpq</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['item_sub_family_name'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
