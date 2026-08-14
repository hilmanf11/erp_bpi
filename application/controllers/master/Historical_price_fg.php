<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Historical_price_fg extends CI_Controller
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
            $this->load->view('master/historical_price_fg');
        } else {
            redirect('error_access');
        }
    }

    // public function readItemFamily($item_category_id)
    // {
    //     $this->db->select('*');
    //     $this->db->from('item_familys');
    //     $this->db->where('id !=', "P08"); 
    //     $this->db->where('deleted', 0);
    //     $this->db->where("item_category_id", $item_category_id);
    //     $this->db->order_by('name', 'ASC');
    //     $records = $this->db->get()->result_array();
    //     echo json_encode($records);
    // }

    public function readItemFamilys()
    {
        // $excluded_ids = ['P08', 'P05', 'P49'];
        $this->db->select('*');
        $this->db->from('item_familys');
        // $this->db->where_not_in('id', $excluded_ids);
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
            header("Content-Disposition: attachment; filename=historical_price_fg_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get('filter_division');
        $filter_customer_id = $this->input->get('filter_customer_id');

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
            a.item_fg_id,
            a.customer_id,
            b.number, 
            b.name, 
            g.name as division, 
            e.name as prodfam, 
            b.uom,
            a.currency,
            c.name as supplier_name
        FROM customer_items a
        JOIN item_fg b ON a.item_fg_id = b.id
        JOIN customers c ON a.customer_id = c.id
        JOIN item_familys e ON b.item_family_id = e.id
        JOIN divisions g ON b.division_id = g.id
        WHERE e.number LIKE '%$filter_item_family%'
        AND b.id LIKE '%$filter_items%'
        AND g.id LIKE '%$filter_division%'
        AND c.id LIKE '%$filter_customer_id%'
        AND b.division_id NOT LIKE 'ADM'
        GROUP BY a.item_fg_id, a.customer_id
        ORDER BY g.name ASC, e.name ASC, c.name ASC, b.name ASC, b.number";

        $records = $this->db->query($query_main)->result();
        $date_limit = date('Y-m-d 23:59:59', strtotime($filter_to));
        $query_history = "SELECT 
                            item_fg_id, 
                            customer_id, 
                            price, 
                            DATE_FORMAT(created_date, '%Y-%m') as period
                          FROM customer_item_histories
                          WHERE created_date <= '$date_limit'
                          ORDER BY created_date ASC";
        $histories = $this->db->query($query_history)->result();

        $history_map = [];
        foreach ($histories as $h) {
            $key = $h->item_fg_id . '_' . $h->customer_id;
            $history_map[$key][] = $h;
        }

        $html = '<html><head><title>Print Data</title>
            <style>
                body {font-family: Arial, Helvetica, sans-serif;}
                #customers {border-collapse: collapse;width: 100%;font-size: 11px;}
                #customers td, #customers th {border: 1px solid #ddd;padding: 4px;}
                #customers tr:nth-child(even){background-color: #f2f2f2;}
                #customers tr:hover {background-color: #ddd;}
                #customers th {padding-top: 5px;padding-bottom: 5px;text-align: center;color: black; background-color: #f2f2f2;}
                
                @media screen {
                    html, body {
                        height: 100%;
                        margin: 0;
                        padding: 0;
                        overflow: hidden;
                    }
                    .print-container {
                        width: 100%;
                        height: 100%;
                        overflow: auto;
                        box-sizing: border-box;
                        padding: 10px;
                    }
                }

                @media print {
                    html, body, .print-container {
                        height: auto !important;
                        overflow: visible !important;
                    }
                    #customers thead { display: table-header-group; }
                    #customers tbody tr { page-break-inside: avoid; }
                }
            </style></head>
            <body>
            
            <div class="print-container">
            
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
                <h3 style="margin:0;">HISTORICAL PRICE FG</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1" style="white-space: nowrap;"> 
             <thead>
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
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
            $key = $record->item_fg_id . '_' . $record->customer_id;
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
