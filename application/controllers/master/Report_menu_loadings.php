<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_menu_loadings extends CI_Controller
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
            $this->load->view('master/report_menu_loadings');
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
            header("Content-Disposition: attachment; filename=report_menu_loadings_$format.xls");
        }
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get("filter_division");
        $filter_status = $this->input->get("filter_status");
        $filter_machine_id = $this->input->get("filter_machine_id");
        $filter_toonage = $this->input->get("filter_toonage");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "SELECT a.*, 
            b.id as item_fg_id,
            b.number as item_fg_number, 
            b.name as item_fg_name, 
            c.number as machine_number, 
            c.toonage as machine_toonage, 
            d.model as mold_model, 
            d.cavity_actual as mold_cavity_actual, 
            d.cavity_standard as mold_cavity_standard,
            b.status
        FROM menu_loadings a
        LEFT JOIN item_fg b ON a.item_fg_id = b.id
        LEFT JOIN machines c ON a.machine_id = c.id
        LEFT JOIN molds d ON a.mold_id = d.id
    
        WHERE b.id LIKE '%$filter_items%' 
        AND b.division_id LIKE '%$filter_division%'
        AND b.status LIKE '%$filter_status%'
        AND a.machine_id LIKE '%$filter_machine_id%'
        AND c.toonage LIKE '%$filter_toonage%'
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
                    <th>Product Id</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Machine No</th>
                    <th>Tonage Of Machine</th>
                    <th>Mold ID</th>
                    <th>Cavity Standard</th>
                    <th>Cavity Actual</th>
                    <th>Shift</th>
                    <th>Hour per Shift</th>
                    <th>Productivity Factor (%)</th>
                    <th>Cycle Time</th>
                    <th>Cycle Time Second Process</th>
                    <th>Man Power</th>
                    <th>Runner per Shoot (gram)</th>
                    <th>Priority</th>
                    <th>Product Status</th>
                </tr>';

        $groups = [];
        foreach ($records as $r) {
            $key = trim($r->item_fg_number) . '||' . trim($r->item_fg_name) . '||' . trim($r->item_fg_id);
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

                    // Merge Product No , Product Name dan id
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_id) . '</td>';
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_number) . '</td>';
                    $html .= '<td style="mso-number-format:\@;" rowspan="' . $rowspan . '">' . htmlspecialchars($first->item_fg_name) . '</td>';

                    // Kolom detail yang tetap per baris
                    $html .= '<td>' . htmlspecialchars($it->machine_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_toonage ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_id ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_standard ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_actual ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->shift ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->shift_hour ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->productcivity ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time_process ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->manpower ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->runner ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->priority ?? '') . '</td>';

                    // Status (merge)
                    $statusCur = (isset($first->status) && $first->status == '0') ? 'Active' : 'Inactive';
                    $html .= '<td rowspan="' . $rowspan . '">' . $statusCur . '</td>';

                    $firstRow = false;

                } else {
                    // Baris berikutnya: semua kolom kecuali Product No & Product Name
                    $html .= '<td>' . htmlspecialchars($it->machine_number ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->machine_toonage ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_id ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_standard ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->mold_cavity_actual ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->shift ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->shift_hour ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->productcivity ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->cycle_time_process ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->manpower ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->runner ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($it->priority ?? '') . '</td>';
                }
                $html .= '</tr>';
            }

            $no++;
        }

        $html .= '</table>';

        echo $html;
    }
}
