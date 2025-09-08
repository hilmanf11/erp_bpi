<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_bom extends CI_Controller
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
            $this->load->view('master/report_bom');
        } else {
            redirect('error_access');
        }
    }

    public function lsb_lock()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $period      = date('Ym', strtotime($filter_from));

        $this->db->where("('$filter_from' BETWEEN lock_from AND lock_to OR 
                        '$filter_to' BETWEEN lock_from AND lock_to OR 
                        lock_from BETWEEN '$filter_from' AND '$filter_to')");
        $cek = $this->db->get('lsb_lock')->num_rows();

        if ($cek > 0) {
            echo json_encode([
                "status"  => false,
                "message" => "Period has been saved."
            ]);
            return;
        }

        // Simpan jika belum ada
        $data = array(
            "period"     => $period,
            "lock_from"  => $filter_from,
            "lock_to"    => $filter_to
        );

        $send = $this->crud->create('lsb_lock', $data);

        if ($send) {
            echo json_encode([
                "status"  => true,
                "message" => "Period saved successfully!: $period"
            ]);
        } else {
            echo json_encode([
                "status"  => false,
                "message" => "Gagal menyimpan data. Silakan coba lagi."
            ]);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_bom_$format.xls");
        }
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get("filter_division");
        $filter_status = $this->input->get("filter_status");
        $filter_item_rm_id = $this->input->get("filter_item_rm_id");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "SELECT 
            a.*,
            a.created_by as created_by_bom,
            a.created_date as created_date_bom,
            b.id as item_fg_id,
            b.number as item_fg_number,
            b.name as item_fg_name,
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
                ELSE 'SUB ASSY'
            END) AS selected_item_prodfam
        FROM bom a
        LEFT JOIN item_fg b ON a.item_fg_id = b.id
        LEFT JOIN item_rm c ON a.item_rm_id = c.id
        LEFT JOIN item_familys d ON c.item_family_id = d.id
        LEFT JOIN item_fg e ON a.item_fg_sa_id = e.id

        WHERE b.id LIKE '%$filter_items%' 
        AND c.id LIKE '%$filter_item_rm_id%'
        AND b.division_id LIKE '%$filter_division%'
        AND b.status LIKE '%$filter_status%'
        ORDER BY b.number
        ";

        $records = $this->crud->query($query_main);

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
                            <small>'.$config->description.'</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">MASTER DATA ENGINEERING (FG)</h3>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
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
                    <th>UOM</th>
                    <th>Composition</th>
                    <th>Remark</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                </tr>';

        $groups = [];
        foreach ($records as $r) {
            $key = trim($r->item_fg_number) . '||' . trim($r->item_fg_name). '||' . trim($r->item_fg_id);
            if (!isset($groups[$key])) $groups[$key] = [];
            $groups[$key][] = $r;
        }

        $no = 1;
        foreach ($groups as $key => $items) {
            $rowspan = count($items);

            // Use the first item for product-level columns
            $first = $items[0];

            // derive status text once
            $statusText = (isset($first->status) && $first->status == '0') ? 'Active' : 'Inactive';

            $firstRow = true;
            foreach ($items as $it) {
                $html .= '<tr>';

                if ($firstRow) {
                    // No (rowspan)
                    $html .= '<td style="text-align:center" rowspan="' . $rowspan . '">' . $no . '</td>';

                    // Merge Product No & Product Name
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_id) . '</td>';
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_number) . '</td>';
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_name) . '</td>';

                    // Kolom yang muncul di setiap baris
                    $html .= '<td>' . htmlspecialchars($it->selected_item_id ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_name ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->type ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->recycle ?? '0') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_prodfam ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_uom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->composition ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->remark ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->created_by_bom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->created_date_bom ?? '') . '</td>';

                    $firstRow = false;

                } else {
                    // Baris berikutnya: semua kolom kecuali Product No & Product Name
                    $html .= '<td>' . htmlspecialchars($it->selected_item_id ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_name ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->type ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->recycle ?? '0') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_prodfam ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->selected_item_uom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->composition ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->remark ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->created_by_bom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->created_date_bom ?? '') . '</td>';
                }
                $html .= '</tr>';
            }

            $no++;
        }

        $html .= '</table>';

        echo $html;
    }
}
