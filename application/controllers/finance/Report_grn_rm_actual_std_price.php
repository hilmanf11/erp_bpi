<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_grn_rm_actual_std_price extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/report_grn_rm_actual_std_price');
        } else {
            redirect('error_access');
        }
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

    public function readCust()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('customers', ["name" => $post]);
        echo json_encode($send);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_grn_rm_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get("filter_item_family");
        $filter_division = $this->input->get("filter_division");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $query_main = "SELECT a.*, 
        //     e.number as part_no, 
        //     e.name as part_name, 
        //     f.name as category_name, 
        //     g.name as family_name,
        //     c.division,
        //     h.name as supplier_name,
        //     (CASE 
        //         WHEN COALESCE(b.discount_nominal,0) > 0 
        //             THEN COALESCE(b.total,0) / COALESCE(b.qty,0) 
        //         ELSE 
        //             (COALESCE(b.total,0) 
        //             - ((COALESCE(b.total,0) / NULLIF(COALESCE(b.total_sub,0),0)) 
        //                 * COALESCE(b.discount_total,0)
        //             )
        //             ) / NULLIF(COALESCE(b.qty,0),0)
        //     END) AS actual_price,
        //     COALESCE(spr.price, 1) as standar_price,
        //     i.uom_default as uom,
        //     COALESCE(i.currency, h.currency) as currency, 
        //     COALESCE(ser.middle, 1) as std_middle_rate,
        //     COALESCE(aer.middle, 1) as actual_middle_rate
        // FROM purchase_order_receipts a 
        // JOIN (SELECT DISTINCT po_no, request_no, item_rm_id, total, discount_nominal, qty, total_sub, discount_total FROM purchase_orders) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id
        // JOIN (SELECT DISTINCT a.request_no, b.name as division FROM purchase_requests a JOIN divisions b ON a.division = b.number) c ON b.request_no = c.request_no 
        // JOIN item_rm e ON a.item_rm_id = e.id 
        // JOIN item_categories f ON e.item_category_id = f.id 
        // JOIN item_familys g ON e.item_family_id = g.id
        // JOIN suppliers h ON a.supplier_id = h.id
        // JOIN supplier_items i ON a.supplier_id = i.supplier_id and a.item_rm_id = i.item_rm_id
        
        // -- JOIN Standard Rate
        // LEFT JOIN standard_exchange_rates ser 
        //     ON ser.currency_from = COALESCE(i.currency, h.currency) 
        //     AND a.receipt_date BETWEEN ser.start_date AND ser.end_date
            
        // -- JOIN Actual Rate
        // LEFT JOIN exchange_rates aer 
        //     ON aer.currency_from = COALESCE(i.currency, h.currency) 
        //     AND a.receipt_date BETWEEN aer.start_date AND aer.end_date

        // -- JOIN Standard Price
        // LEFT JOIN standard_price_rm spr 
        //     ON a.receipt_date BETWEEN spr.start_date AND spr.end_date AND a.item_rm_id = spr.item_rm_id AND spr.division LIKE '%$filter_division%'
            
        // WHERE f.id = 'C01' 
        // AND a.deleted = '0'
        // AND a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
        // AND g.id LIKE '%$filter_item_family%'
        // AND c.division LIKE '%$filter_division%'
        // ";

        $query_main = "SELECT a.*, 
            e.number as part_no, 
            e.name as part_name, 
            f.name as category_name, 
            g.name as family_name,
            c.division,
            h.name as supplier_name,
            
            (CASE 
                WHEN COALESCE(b.discount_nominal,0) > 0 
                    THEN COALESCE(b.total,0) / NULLIF(COALESCE(b.qty,0),0) 
                ELSE 
                    (COALESCE(b.total,0) 
                    - ((COALESCE(b.total,0) / NULLIF(COALESCE(b.total_sub,0),0)) 
                        * COALESCE(b.discount_total,0)
                    )
                    ) / NULLIF(COALESCE(b.qty,0),0)
            END) AS actual_price,
            
            COALESCE(spr.price, 1) as standar_price,
            i.uom_default as uom,
            COALESCE(i.currency, h.currency) as currency, 
            COALESCE(ser.middle, 1) as std_middle_rate,
            COALESCE(aer.middle, 1) as actual_middle_rate
            
        FROM purchase_order_receipts a 
        
        JOIN (
            SELECT 
                po_no, 
                request_no, 
                item_rm_id, 
                SUM(qty) as qty, 
                SUM(total) as total, 
                SUM(total_sub) as total_sub, 
                SUM(discount_nominal) as discount_nominal,
                SUM(discount_total) as discount_total
            FROM purchase_orders
            GROUP BY po_no, request_no, item_rm_id
        ) b ON a.po_no = b.po_no AND a.item_rm_id = b.item_rm_id
        
        -- PERUBAHAN DI SINI: Tambah b.number as division_code
        JOIN (SELECT DISTINCT a.request_no, b.name as division, b.number as division_code FROM purchase_requests a JOIN divisions b ON a.division = b.number) c 
            ON b.request_no = c.request_no 
            
        JOIN item_rm e ON a.item_rm_id = e.id 
        JOIN item_categories f ON e.item_category_id = f.id 
        JOIN item_familys g ON e.item_family_id = g.id
        JOIN suppliers h ON a.supplier_id = h.id
        JOIN supplier_items i ON a.supplier_id = i.supplier_id and a.item_rm_id = i.item_rm_id
        
        LEFT JOIN standard_exchange_rates ser 
            ON ser.currency_from = COALESCE(i.currency, h.currency) 
            AND a.receipt_date BETWEEN ser.start_date AND ser.end_date
            
        LEFT JOIN exchange_rates aer 
            ON aer.currency_from = COALESCE(i.currency, h.currency) 
            AND a.receipt_date BETWEEN aer.start_date AND aer.end_date

        -- PERUBAHAN DI SINI: Ganti c.division menjadi c.division_code
        LEFT JOIN standard_price_rm spr 
            ON spr.item_rm_id = a.item_rm_id
            AND spr.division = c.division_code 
            AND a.receipt_date BETWEEN spr.start_date AND spr.end_date
            
        WHERE f.id = 'C01' 
        AND a.deleted = '0'
        AND a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
        AND g.id LIKE '%$filter_item_family%'
        AND c.division_code LIKE '%$filter_division%'
        ";

        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title>
        <style>
            @page { size: landscape; margin: 10mm; }
            body { font-family: Arial, Helvetica, sans-serif; }
            #customers { border-collapse: collapse; width: 100%; font-size: 11px; }
            #customers td, #customers th { border: 1px solid #ddd; padding: 5px 8px; }
            #customers tr:nth-child(even){ background-color: #f2f2f2; }
            #customers tr:hover { background-color: #ddd; }
            #customers th {
                padding-top: 10px;
                padding-bottom: 10px;
                text-align: center;
                color: black;
                white-space: nowrap; 
                background-color: #e9ecef; 
            }
        </style>
        </head><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
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
                <div style="clear: both;"></div> <br><br><br>
                <h3 style="margin:0;">REPORT GRN RM ACTUAL & STANDAR PRICE</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">GRN No</th>
                    <th rowspan="2">PO No</th>
                    <th rowspan="2">Supplier Name</th>
                    <th rowspan="2">Document No</th>
                    <th rowspan="2">Receipt Date</th>
                    <th rowspan="2">Part No</th>
                    <th rowspan="2">Part Name</th>
                    <th rowspan="2">Qty</th>
                    <th rowspan="2">UOM</th>
                    <th colspan="4">Standard Price</th>
                    <th colspan="4">Actual Price</th>
                    <th rowspan="2" width="100">Variance Unit</th>
                    <th rowspan="2" width="100">Variance Amount</th>
                </tr>
                <tr>
                    <th width="80">Currency</th>
                    <th width="80">Price</th>
                    <th width="80">Amount</th>
                    <th width="80">Amount IDR</th>

                    <th width="80">Currency</th>
                    <th width="80">Price</th>
                    <th width="80">Amount</th>
                    <th width="80">Amount IDR</th>
                </tr>';

        $no = 1;

        $grand_qty = 0;
        $grand_std_amount = 0;
        $grand_std_amount_idr = 0;
        $grand_act_amount = 0;
        $grand_act_amount_idr = 0;
        $grand_variance_amount = 0;

        foreach ($records as $record) {
            $qty = $record->qty_receipt2; 
            $actual_price = $record->actual_price;
            $standar_price = $record->standar_price;
            $currency = $record->currency;
            
            $std_rate = $record->std_middle_rate;
            $act_rate = $record->actual_middle_rate;

            // Kalkulasi Total Amount
            $std_amount = $qty * $standar_price;
            $std_amount_idr = $std_amount * $std_rate;

            $act_amount = $qty * $actual_price;
            $act_amount_idr = $act_amount * $act_rate;

            // Kalkulasi Unit Price (IDR)
            $price_std_idr = $standar_price * $std_rate;
            $price_act_idr = $actual_price * $act_rate;

            // Kalkulasi Variance (Standard - Actual)
            $variance_amount = $std_amount_idr - $act_amount_idr;
            $variance_unit = $price_std_idr - $price_act_idr;

            // Akumulasi Grand Total
            $grand_qty += $qty;
            $grand_std_amount += $std_amount;
            $grand_std_amount_idr += $std_amount_idr;
            $grand_act_amount += $act_amount;
            $grand_act_amount_idr += $act_amount_idr;
            $grand_variance_amount += $variance_amount;

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $record->division . '</td>
                        <td>' . $record->category_name . '</td>
                        <td>' . $record->family_name . '</td>
                        <td>' . $record->receipt_no . '</td>
                        <td>' . $record->po_no . '</td>
                        <td>' . $record->supplier_name . '</td>
                        <td>' . $record->bc_document . '</td>
                        <td>' . $record->receipt_date . '</td>
                        <td>' . $record->part_no . '</td>
                        <td>' . $record->part_name . '</td>
                        <td style="text-align:right;">' . number_format($qty, 2) . '</td>
                        <td>' . $record->uom . '</td>

                        <td style="text-align:center;">' . $currency . '</td>
                        <td style="text-align:right;">' . number_format($standar_price, 4) . '</td>
                        <td style="text-align:right;">' . number_format($std_amount, 4) . '</td>
                        <td style="text-align:right;">' . number_format($std_amount_idr, 4) . '</td>
                        
                        <td style="text-align:center;">' . $currency . '</td>
                        <td style="text-align:right;">' . number_format($actual_price, 4) . '</td>
                        <td style="text-align:right;">' . number_format($act_amount, 4) . '</td>
                        <td style="text-align:right;">' . number_format($act_amount_idr, 4) . '</td>
                        
                        <td style="text-align:right;">' . number_format($variance_unit, 4) . '</td>
                        <td style="text-align:right;">' . number_format($variance_amount, 4) . '</td>
                    </tr>';
            $no++;
        }

        // --- BARIS GRAND TOTAL ---
        $html .= '<tr style="background-color: #e9ecef;">
                    <td colspan="12" style="text-align:right; padding-right:15px;"><b>GRAND TOTAL</b></td>
                    <td style="text-align:right;"><b>' . number_format($grand_qty, 2) . '</b></td>
                    <td colspan="2" style="background-color: #e9ecef; text-align:center;">-</td> 
                    <td style="text-align:right;"><b>' . number_format($grand_std_amount, 4) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($grand_std_amount_idr, 4) . '</b></td>
                    <td colspan="2" style="background-color: #e9ecef; text-align:center;">-</td>
                    <td style="text-align:right;"><b>' . number_format($grand_act_amount, 4) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($grand_act_amount_idr, 4) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($grand_variance_amount, 4) . '</b></td>
                    <td style="background-color: #e9ecef; text-align:center;"><b>-</b></td>
                </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
