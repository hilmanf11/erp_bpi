<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_sto_finish_goods extends CI_Controller
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
            $this->load->view('warehouse/report_sto_finish_goods');
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

    public function readsDivision()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('divisions', ["name" => $post]);
        echo json_encode($send);
    }

    // public function readItemFamilys()
    // {
    //     $this->db->select('*');
    //     $this->db->from('item_familys');
    //     $this->db->where('id !=', "P08"); 
    //     $this->db->where('deleted', 0);
    //     // $this->db->where("item_category_id", $item_category_id);
    //     $this->db->order_by('name', 'ASC');
    //     $records = $this->db->get()->result_array();
    //     echo json_encode($records);
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sto_finish_goods_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_cut_of_stock = $this->input->get('filter_cut_of_stock');
        //$filter_cut_of_sto   = $this->input->get('filter_cut_of_sto');
        $filter_from_sto   = $this->input->get('filter_from_sto');
        $filter_to_sto   = $this->input->get('filter_to_sto');
        $filter_division   = $this->input->get('filter_division');
        $filter_deviation   = $this->input->get('filter_deviation');
        $filter_items   = $this->input->get('filter_items');

        $division = $this->crud->read('divisions',[],["number"=> $filter_division]);

        $division_num = isset($division->number) && !empty($division->number) ? $division->number : '-';

        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        // $records = $this->crud->query("SELECT
        //         a.id,
        //         a.number, 
        //         a.name, 
        //         a.uom, 
        //         b.number AS division, 
        //         f.created_by, 
        //         f.created_date, 
        //         COALESCE(f.qty_sto, 0) AS qty_sto,
        //         COALESCE(q.qty_stock, 0) AS qty_stock,
        //         COALESCE(f.qty_sto, 0) - COALESCE(q.qty_stock, 0) AS deviation
        //     FROM item_fg a 
        //     LEFT JOIN production_schedules d ON a.id = d.item_fg_id
        //     LEFT JOIN checksheets e ON d.wo_no = e.wo_no
        //     LEFT JOIN divisions b ON a.division_id = b.id
        //     LEFT JOIN (
        //         SELECT 
        //             item_fg_id, 
        //             SUM(qty) AS qty_sto, 
        //             MAX(created_date) AS created_date, 
        //             MAX(created_by) AS created_by
        //         FROM sto_finish_goods 
        //         WHERE created_date < '$filter_cut_of_sto'
        //         GROUP BY item_fg_id
        //     ) f ON a.id = f.item_fg_id
        //     LEFT JOIN (
        //         SELECT 
        //             a.id AS item_fg_id,
        //             (
        //                 SELECT COALESCE(SUM(f.qty), 0)
        //                 FROM scan_item_receipts_fg f
        //                 JOIN checksheets e ON e.number = f.checksheet_number
        //                 WHERE a.id = e.item_fg_id
        //                 AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_cut_of_stock'
        //             ) + (
        //                 SELECT COALESCE(SUM(i.qty), 0)
        //                 FROM scan_item_receipts_fg i
        //                 WHERE i.item_fg_id = a.id
        //                 AND i.type = 'NBFG'
        //                 AND i.packing_date < '$filter_cut_of_stock'
        //                 AND NOT EXISTS (
        //                     SELECT 1
        //                     FROM checksheets e
        //                     WHERE e.number = i.checksheet_number
        //                 )
        //             ) AS qty_stock
        //         FROM item_fg a
        //     ) q ON a.id = q.item_fg_id
        //     WHERE a.division_id LIKE '%$filter_division%' AND a.id LIKE '%$filter_items%'
        //     GROUP BY a.id
        //     HAVING 
        //         ('$filter_deviation' = 'deviationplus' AND deviation > 0) OR 
        //         ('$filter_deviation' = 'deviationminus' AND deviation < 0) OR
        //         ('$filter_deviation' = '' OR '$filter_deviation' IS NULL)
        //     ORDER BY a.number
        // ");


        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') <= '$filter_cut_of_stock'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date <= '$filter_cut_of_stock'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date <= '$filter_cut_of_stock'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date <= '$filter_cut_of_stock'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date <= '$filter_cut_of_stock'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') <= '$filter_cut_of_stock'
        GROUP BY f.item_fg_id";

        // Step 7: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            b.number AS division, 
            f.created_by, 
            f.created_date,
            COALESCE(f.qty_sto, 0) AS qty_sto,
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS qty_stock,
            COALESCE(f.qty_sto, 0) - 
            ((COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0)) - (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS deviation
        FROM item_fg a
        LEFT JOIN divisions b ON a.division_id = b.id
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_sto, MAX(created_date) AS created_date, MAX(created_by) AS created_by FROM sto_finish_goods WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from_sto' and '$filter_to_sto' GROUP BY item_fg_id) f ON a.id = f.item_fg_id
        WHERE a.division_id LIKE '%$filter_division%' AND a.id LIKE '%$filter_items%'
        HAVING 
                ('$filter_deviation' = 'deviationplus' AND deviation > 0) OR 
                ('$filter_deviation' = 'deviationminus' AND deviation < 0) OR
                ('$filter_deviation' = '' OR '$filter_deviation' IS NULL)
        ORDER BY a.number
        ";

        $records = $this->crud->query($query_main);

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
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">Division</th>
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
