<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Historical_price_material extends CI_Controller
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
            $this->load->view('master/historical_price_material');
        } else {
            redirect('error_access');
        }
    }

    // public function readEndingStock()
    // {
    //     if ($this->input->post()) {
    //         $item_rm_id = $this->input->post('item_rm_id');
    //         $trans_date = @$this->input->post('trans_date');

    //         if (@$trans_date == "") {
    //             $date = date("Y-m-d");
    //         } else {
    //             $date = $trans_date;
    //         }

    //         $records = $this->crud->query("SELECT
    //             a.id,
    //             a.number, 
    //             a.name, 
    //             b.name as prodfam, 
    //             a.uom, 
    //             COALESCE(0,0) as begin_stock,
    //             (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,
    //             f.qty as qty_out,
    //             (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock
    //         FROM item_rm a 
    //         JOIN item_familys b ON a.item_family_id = b.id
    //         LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$date'
    //         LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
    //         LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
    //         LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
    //             FROM return_materials a 
    //             JOIN return_material_labels b ON a.return_id = b.return_id
    //             JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
    //             WHERE a.return_date <=  '$date'
    //             GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
    //         WHERE a.id like '$item_rm_id'
    //         GROUP BY a.id
    //         ORDER BY a.number");

    //         echo json_encode($records);
    //     }
    // }

    public function readEndingStock()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');

            if (@$trans_date == "") {
                $date = date("Y-m-d");
            } else {
                $date = $trans_date;
            }

            $records = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
            (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
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

            echo json_encode($records);
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
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
        $excluded_ids = ['P08', 'P05', 'P49'];
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where_not_in('id', $excluded_ids);
        $this->db->where('deleted', 0);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get('filter_division');

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Config ISO
        $this->db->select('*');
        $this->db->from('config_iso');
        $config_iso = $this->db->get()->row();
        $formHistoricalRM = !empty($config_iso->form_historical_rm) ? $config_iso->form_historical_rm : 'DOC';
        
        $months = [];
        $start_date = date('Y-m-01', strtotime($filter_from)); 
        $end_date   = date('Y-m-01', strtotime($filter_to));
        
        $current = strtotime($start_date);
        $end     = strtotime($end_date);

        while ($current <= $end) {
            $months[] = [
                'key'   => date('Y-m', $current),
                'label' => date('M y', $current)
            ];
            $current = strtotime('+1 month', $current);
        }
        $num_months = count($months);

        $query_main = "SELECT 
            a.item_rm_id,
            a.supplier_id,
            b.number, 
            b.name, 
            b.division, 
            e.name as prodfam, 
            b.uom,
            f.name as category_name,
            a.currency,
            c.name as supplier_name
        FROM supplier_items a
        JOIN item_rm b ON a.item_rm_id = b.id
        JOIN suppliers c ON a.supplier_id = c.id
        JOIN item_familys e ON b.item_family_id = e.id
        JOIN item_categories f ON b.item_category_id = f.id
        WHERE f.id LIKE '%$filter_item_category%'
        AND e.number LIKE '%$filter_item_family%'
        AND b.id LIKE '%$filter_items%'
        AND b.division LIKE '%$filter_division%'
        AND b.division NOT LIKE 'ADM'
        GROUP BY a.item_rm_id, a.supplier_id
        ORDER BY b.division ASC, f.name ASC, c.name ASC, b.name ASC, b.number";

        $records = $this->db->query($query_main)->result();
        $date_limit = date('Y-m-d 23:59:59', strtotime($filter_to));
        $query_history = "SELECT 
                            item_rm_id, 
                            supplier_id, 
                            price, 
                            DATE_FORMAT(created_date, '%Y-%m') as period
                          FROM supplier_item_histories
                          WHERE created_date <= '$date_limit'
                          ORDER BY created_date ASC";
        $histories = $this->db->query($query_history)->result();

        $history_map = [];
        foreach ($histories as $h) {
            $key = $h->item_rm_id . '_' . $h->supplier_id;
            $history_map[$key][] = $h;
        }

        $html = '<html><head><title>Print Data</title></head>
            <style>
                body {font-family: Arial, Helvetica, sans-serif;}
                #customers {border-collapse: collapse;width: 100%;font-size: 11px;}
                #customers td, #customers th {border: 1px solid #ddd;padding: 4px;}
                #customers tr:nth-child(even){background-color: #f2f2f2;}
                #customers tr:hover {background-color: #ddd;}
                #customers th {padding-top: 5px;padding-bottom: 5px;text-align: center;color: black; background-color: #f2f2f2;}
                @media print {
                    #customers thead { display: table-header-group; }
                    #customers tbody tr { page-break-inside: avoid; }
                }
            </style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="vertical-align: top; text-align: center; margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>';
                
        if ($option == "excel") {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>';
        } else {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                <br><br>';
        }
        
        $html .= '<br><br>
                <h3 style="margin:0;">HISTORICAL PRICE MATERIAL</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <div style="width: 100%; overflow-x: auto;"> 
            <table id="customers" border="1" style="white-space: nowrap;"> 
             <thead>
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Part No</th>
                    <th rowspan="2">Part Name</th>
                    <th rowspan="2">UOM</th>
                    <th rowspan="2">Supplier</th>
                    <th rowspan="2">Currency</th>
                    <th colspan="'.$num_months.'">MONTH</th>
                </tr>
                <tr>';
        
        // Render Header Bulan Dinamis
        foreach ($months as $m) {
            $html .= '<th>' . strtoupper($m['label']) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        $no = 1;
        foreach ($records as $record) {
            $key = $record->item_rm_id . '_' . $record->supplier_id;
            $item_histories = isset($history_map[$key]) ? $history_map[$key] : [];

            $start_period = date('Y-m', strtotime($filter_from));
            $last_price = 0;

            foreach ($item_histories as $h) {
                if ($h->period < $start_period) {
                    $last_price = $h->price;
                }
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $record->division . '</td>
                        <td>' . $record->category_name . '</td>
                        <td>' . $record->prodfam . '</td>
                        <td>' . $record->number . '</td>
                        <td>' . $record->name . '</td>
                        <td style="text-align:center">' . $record->uom . '</td>
                        <td>' . $record->supplier_name . '</td>
                        <td style="text-align:center">' . $record->currency . '</td>';

            foreach ($months as $m) {
                $period = $m['key'];
                $price_changed_this_month = false;
                $price_this_month = $last_price;

                foreach ($item_histories as $h) {
                    if ($h->period == $period) {
                        if ($h->price != $last_price) {
                            $price_this_month = $h->price;
                            $price_changed_this_month = true;
                        }
                    }
                }

                $bg_color = $price_changed_this_month ? 'background-color: #FFCFCF;' : '';
                $display_price = ($price_this_month > 0) ? number_format($price_this_month, 2) : '-';
                $html .= '<td style="text-align:right; font-weight:bold; ' . $bg_color . '">' . $display_price . '</td>';
                $last_price = $price_this_month; 
            }

            $html .= '</tr>';
            $no++;
        }

        $html .= '</tbody></table>
            </div>
            </body></html>';
        echo $html;
    }

}
