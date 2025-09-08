<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_master_data_eng extends CI_Controller
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
            $this->load->view('master/report_master_data_eng');
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
            header("Content-Disposition: attachment; filename=report_master_data_eng_$format.xls");
        }
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get("filter_division");
        $filter_status = $this->input->get("filter_status");
        $filter_customer_id = $this->input->get("filter_customer_id");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "SELECT 
            a.*,
            b.name as division_name, 
            (SELECT COUNT(*) FROM mold_items c WHERE c.item_fg_id = a.id) as total_mold, 
            f.min, 
            f.max, 
            g.name as item_family_name,
            h.runner,
            i.number as machine_number,
            COALESCE(i.toonage,0) as machine_toonage, 
            COALESCE(j.cavity_actual,0) as mold_cavity_actual, 
            COALESCE(j.cavity_standard,0) as mold_cavity_standard,
            COALESCE(h.cycle_time,0) as cycle_time,
            COALESCE(h.cycle_time_process,0) as cycle_time_process,
            COALESCE(h.manpower,0) as manpower,
            a.status,
            e.number as cust_number,
            COALESCE(l.name, m.name) AS part_name,
            COALESCE(l.number, m.number) AS part_number,
            COALESCE(l.uom, m.uom) AS part_uom,
            k.composition
        FROM item_fg a
        LEFT JOIN divisions b ON a.division_id = b.id
        LEFT JOIN (
            SELECT DISTINCT item_fg_id, customer_id
            FROM customer_items
        ) d ON d.item_fg_id = a.id
        LEFT JOIN customers e ON d.customer_id = e.id
        LEFT JOIN setting_stocks f ON e.type = f.kind AND f.item_category_id = 'C03'
        LEFT JOIN item_familys g ON a.item_family_id = g.id
        LEFT JOIN menu_loadings h ON a.id = h.item_fg_id
        LEFT JOIN machines i ON h.machine_id = i.id
        LEFT JOIN molds j ON h.mold_id = j.id
        LEFT JOIN bom k ON a.id = k.item_fg_id
        LEFT JOIN item_rm l ON k.item_rm_id = l.id
        LEFT JOIN item_fg m ON k.item_fg_sa_id = m.id

        WHERE a.id LIKE '%$filter_items%' 
        AND a.division_id LIKE '%$filter_division%'
        AND a.status LIKE '%$filter_status%'
        AND e.id LIKE '%$filter_customer_id%'
        ORDER BY a.number
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
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Uom</th>
                    <th>Composition</th>
                    <th>Product No Customer</th>
                    <th>Total Mold</th>
                    <th>Process Type</th>
                    <th>Division</th>
                    <th>Type</th>
                    <th>Product Family</th>
                    <th>Box</th>
                    <th>Polybag Label</th>
                    <th>Box Label</th>
                    <th>IS No</th>
                    <th>Color</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Qty/Box</th>
                    <th>Qty/Sub Box</th>
                    <th>Default Packing</th>
                    <th>Leadtime</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Product Weight (gram)</th>
                    <th>Runner per Shoot Weight (gram)</th>
                    <th>Machine No</th>
                    <th>Tonage Of Machine</th>
                    <th>Cavity Standard</th>
                    <th>Cavity Actual</th>
                    <th>Cycle Time</th>
                    <th>Cycle Time Second Process</th>
                    <th>Man Power</th>
                    <th>Status Subcont</th>
                    <th>Subcont Type</th>
                    <th>Customer</th>
                    <th>Status</th>
                </tr>';

        $groups = [];
        foreach ($records as $r) {
            $key = trim($r->number) . '||' . trim($r->name);
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
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->number) . '</td>';
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->name) . '</td>';

                    // Kolom yang muncul di setiap baris (part_number - composition)
                    $html .= '<td>' . htmlspecialchars($it->part_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->part_name ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->part_uom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->composition ?? '') . '</td>';

                    // Merge kolom dari Number Customer sampai Weight
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->number_customer ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->total_mold ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->process ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->division_name ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->type ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_family_name ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->boxs ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->polybag ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->box_label ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->is_no ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->color ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->mpq ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->moq ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->qty_box ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->box_sub ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->default_packing ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->leadtime ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->min ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->max ?? '') . '</td>';
                    $html .= '<td rowspan="' . $rowspan . '">' . htmlspecialchars($first->weight ?? '') . '</td>';

                    // Kolom detail yang tetap per baris
                    $html .= '<td>' . htmlspecialchars($it->runner ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_toonage ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_standard ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_actual ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time_process ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->manpower ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->status_subcont ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->subcont_type ?? '') . '</td>';

                    // Customer Number (selalu tiap baris)
                    $html .= '<td>' . htmlspecialchars($it->cust_number ?? '') . '</td>';

                    // Status (merge)
                    $statusCur = (isset($first->status) && $first->status == '0') ? 'Active' : 'Inactive';
                    $html .= '<td rowspan="' . $rowspan . '">' . $statusCur . '</td>';

                    $firstRow = false;

                } else {
                    // Baris berikutnya: semua kolom kecuali Product No & Product Name
                    $html .= '<td>' . htmlspecialchars($it->part_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->part_name ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->part_uom ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->composition ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->runner ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_toonage ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_standard ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_actual ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time_process ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->manpower ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->status_subcont ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->subcont_type ?? '') . '</td>';

                    // Customer Number (selalu tiap baris)
                    $html .= '<td>' . htmlspecialchars($it->cust_number ?? '') . '</td>';
                }
                $html .= '</tr>';
            }

            $no++;
        }

        $html .= '</table>';

        echo $html;
    }
}
