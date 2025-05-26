<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_sto_raw_materials extends CI_Controller
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
            $this->load->view('warehouse/report_sto_raw_materials');
        } else {
            redirect('error_access');
        }
    }

    public function readsNotfg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_categories WHERE name LIKE '%$post%'AND number != 'FG'");
        // $send = $this->crud->reads('item_categories', ["name" => $post]);
        echo json_encode($send);
    }

    public function readItemFamily($item_category_id)
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('id !=', "P08"); 
        $this->db->where('deleted', 0);
        $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readItemFamilys()
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('id !=', "P08"); 
        $this->db->where('deleted', 0);
        // $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sto_raw_materials_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_cut_of_stock = $this->input->get('filter_cut_of_stock');
        //$filter_cut_of_sto   = $this->input->get('filter_cut_of_sto');
        $filter_from_sto   = $this->input->get('filter_from_sto');
        $filter_to_sto   = $this->input->get('filter_to_sto');
        $filter_items   = $this->input->get('filter_items');
        $filter_division   = $this->input->get('filter_division');
        $filter_deviation   = $this->input->get('filter_deviation');

        $category = $this->crud->read('item_categories',[],["id"=> $filter_item_category]);
        $prod_fam = $this->crud->read('item_familys',[],["id"=> $filter_item_family]);
        $division = $this->crud->read('divisions',[],["number"=> $filter_division]);

        $category_name = isset($category->name) && !empty($category->name) ? $category->name : '-';
        $prod_fam_name = isset($prod_fam->name) && !empty($prod_fam->name) ? $prod_fam->name : '-';
        $division_num = isset($division->number) && !empty($division->number) ? $division->number : '-';


        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        $records = $this->crud->query("SELECT
        a.id,
        a.number, 
        a.name, 
        a.division, 
        b.name AS prodfam, 
        a.uom,
        c.name AS category_name, 
        j.created_by, 
        j.created_date, 
        (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0)) - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_stock,

        COALESCE(j.qty_sto, 0) AS qty_sto, 

        COALESCE(j.qty_sto, 0) - ((COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0)) - (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0))) AS deviation

        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_cut_of_stock' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_cut_of_stock' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_cut_of_stock' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_cut_of_stock' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_cut_of_stock' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_cut_of_stock' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id

        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_sto, MAX(created_date) AS created_date,  MAX(created_by) AS created_by FROM sto_raw_materials WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from_sto' and '$filter_to_sto' GROUP BY item_rm_id ) j ON a.id = j.item_rm_id

        WHERE c.id LIKE '%$filter_item_category%' AND b.id LIKE '%$filter_item_family%' AND a.division LIKE '%$filter_division%' AND a.id LIKE '%$filter_items%'
        GROUP BY a.id, a.number, a.name, b.name, a.uom, c.name, j.created_by, j.created_date, j.qty_sto
        HAVING 
            ('$filter_deviation' = 'deviationplus' AND deviation > 0) OR 
            ('$filter_deviation' = 'deviationminus' AND deviation < 0) OR
            ('$filter_deviation' = '' OR '$filter_deviation' IS NULL)

        ORDER BY c.name DESC, b.name DESC, a.number");

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; }
            #customers { border-collapse: collapse; width: 100%; font-size: 12px; }
            #customers td, #customers th { border: 1px solid #ddd; padding: 2px; }
            #customers tr:nth-child(even) { background-color: #f2f2f2; }
            #customers tr:hover { background-color: #ddd; }
            #customers th { padding-top: 2px; padding-bottom: 2px; text-align: center; color: black; }
            @media print {
                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                body { margin: 0; }
            }
        </style>
        <body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; text-align: center; vertical-align: middle;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin: 2px;">
                                <b>' . $config->name . '</b><br>
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">STO REPORT</h3>
            </center>
            <br>
            <div style="float:left; width:50%;">
                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                    <tr>
                        <td width="100">Division</td>
                        <td width="5">:</td>
                        <td>' . $division_num . '</td>
                    </tr>
                    <tr>
                        <td width="100">Category</td>
                        <td width="5">:</td>
                        <td>' . $category_name . '</td>
                    </tr>
                    <tr>
                        <td width="100">Product Family</td>
                        <td width="5">:</td>
                        <td>' . $prod_fam_name . '</td>
                    </tr>
                    <tr>
                        <td width="100">Cut of Stock</td>
                        <td width="5">:</td>
                        <td>' . $filter_cut_of_stock . '</td>
                    </tr>
                    <tr>
                        <td width="100">Period of Sto</td>
                        <td width="5">:</td>
                        <td>' . $filter_from_sto . ' to ' . $filter_to_sto . '</td>
                    </tr>
                </table>
            </div>
            <table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr>
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">Part No</th>
                        <th rowspan="2">Part Name</th>
                        <th rowspan="2">Division</th>
                        <th rowspan="2">Category</th>
                        <th rowspan="2">Product Family</th>
                        <th rowspan="2">Uom</th>
                        <th rowspan="2">Qty Stock</th>
                        <th rowspan="2">Total Qty STO</th>
                        <th rowspan="2">Deviation</th>
                        <th rowspan="2">Description</th>
                        <th colspan="2">STO</th>
                    </tr>
                    <tr>
                        <th width="100">By</th>
                        <th width="100">Date</th>
                    </tr>
                </thead>
                <tbody>';

        // Loop untuk data
        $no = 1;
        foreach ($records as $record) {
            $deviation = $record->deviation;
            $formatted_deviation = $deviation < 0 ? '(' . abs($deviation) . ')' : $deviation;
            
            if ($deviation < 0) {
                $description = 'Deviation -';
                $deviation_color = 'style="color: red; mso-number-format:\@;"';  // Menambahkan gaya merah pada deviasi negatif
            } elseif ($deviation > 0) {
                $description = 'Deviation +';
                $deviation_color = 'style="color: red; mso-number-format:\@;"';  // Menambahkan gaya merah pada deviasi positif
            } else {
                $description = '';
                $deviation_color = 'style="mso-number-format:\@;"';  // Tidak ada gaya jika deviasi 0
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td ' . $deviation_color . '>' . $record->number . '</td>
                            <td ' . $deviation_color . '>' . $record->name . '</td>
                            <td ' . $deviation_color . '>' . $record->division . '</td>
                            <td ' . $deviation_color . '>' . $record->category_name . '</td>
                            <td ' . $deviation_color . '>' . $record->prodfam . '</td>
                            <td ' . $deviation_color . '>' . $record->uom . '</td>
                            <td ' . $deviation_color . '>' . $record->qty_stock . '</td>
                            <td ' . $deviation_color . '>' . $record->qty_sto . '</td>
                            <td ' . $deviation_color . '>' . $formatted_deviation . '</td>
                            <td ' . $deviation_color . '>' . $description . '</td>
                            <td ' . $deviation_color . '>' . $record->created_by . '</td>
                            <td ' . $deviation_color . '>' . $record->created_date . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</tbody></table></body></html>';

        $html .= '
            <br><br>
            <table style="width: 100%; font-size: 12px; border: 1px solid #000; text-align: center; border-collapse: collapse;">
                <tr>
                    <td colspan="6" style="padding: 10px; text-align: center;">
                        <b>CONFIRM OK</b>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>PIC STO</b>
                    </td>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>Data Entry</b>
                    </td>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>Koordinator Lapangan</b>
                    </td>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>Koordinator Data</b>
                    </td>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>Penanggung Jawab</b>
                    </td>
                    <td style="border: 1px solid #000; padding: 10px; height: 50px;">
                        <b>Ketua STO</b>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                    <td style="border: 1px solid #000; height: 100px;"></td>
                </tr>
                <tr>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                    <td style="text-align: center; border: 1px solid #000; height: 25px;"></td>
                </tr>
            </table>
        </body>
        </html>';

        echo $html;
    }

}
