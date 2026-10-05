<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 */
class Ito_dashboard_fg extends CI_Controller
{
    private $_get_rates = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('warehouse/ito_dashboard_fg');
        } else {
            redirect('error_access');
        }
    }

    public function get_dashboard_datatables() 
    {
        $filter_month       = @base64_decode($this->input->get('month'));
        $filter_year        = @base64_decode($this->input->get('year'));
        $filter_display     = @base64_decode($this->input->get('display'));

        $currency_date_from = $filter_year .'-01-01';
        $currency_date_to = $filter_year .'-12-31';

        // Membuat tanggal awal bulan (selalu tanggal 01)
        if($filter_display=='MONTHLY'){
            $filter_from = $filter_year . '-' . $filter_month . '-01';
            $filter_to = $filter_year . '-' . $filter_month . '-' . date('t', strtotime($filter_from));
        }else{
            $filter_from = $filter_year . '-01-01';
            $filter_to = $filter_year . '-12-31';
        }
        

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";


        $query_delivery_notes = "SELECT a.item_fg_id, a.customer_id, SUM(a.qty) as initial_out_g, SUM(a.qty*1) as initial_out_g_amount 
        FROM delivery_notes a
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'SALES'
        GROUP BY a.item_fg_id, a.customer_id";
        

        $query_delivery_notes_sales_minus1 = "SELECT x.item_fg_id, x.customer_id, sum(x.qty) as qty_notes_sales, SUM(x.amount) as qty_notes_sales_amount  from (
        
        SELECT a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id, sum(a.qty) as qty, SUM(a.qty*1) as amount
        FROM delivery_notes a 
        WHERE delivery_note_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND trans_type = 'SALES'
        GROUP BY a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id
        
        ) x
        group by  x.customer_id
        ";

        $query_delivery_notes_sales_minus2 = "SELECT x.item_fg_id, x.customer_id, sum(x.qty) as qty_notes_sales, SUM(x.amount) as qty_notes_sales_amount  from (
        
        SELECT a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id, sum(a.qty) as qty, SUM(a.qty*1) as amount
        FROM delivery_notes a 
        WHERE delivery_note_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND trans_type = 'SALES'
        GROUP BY a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id
        
        ) x
        group by x.item_fg_id, x.customer_id
        ";

        $query_delivery_notes_sales_minus3 = "SELECT x.item_fg_id, x.customer_id, sum(x.qty) as qty_notes_sales, SUM(x.amount) as qty_notes_sales_amount  from (
        
        SELECT a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id, sum(a.qty) as qty, SUM(a.qty*1) as amount
        FROM delivery_notes a 
        WHERE delivery_note_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND trans_type = 'SALES'
        GROUP BY a.delivery_note_no,a.item_fg_id, a.sales_order_no,a.customer_id
        
        ) x
        group by x.item_fg_id, x.customer_id
        ";

        // echo $query_delivery_notes_sales_minus1.";<br><br>";
        // echo $query_delivery_notes_sales_minus2.";<br><br>";
        // echo $query_delivery_notes_sales_minus3.";<br><br>";
        // exit();

        //get price from standard price FG
        $query_standard_price_fb = "SELECT 
            a.*, coalesce(b.middle,1) as middle
        FROM standard_price_fg a
        left join standard_exchange_rates b on a.currency = b.currency_from and b.deleted = 0 and b.end_date >='$currency_date_to' and b.currency_to='IDR'
        WHERE a.start_date='$currency_date_from' and a.end_date='$currency_date_to'  and a.deleted=0
        ORDER BY a.id ASC";



        // Step 9: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            qg.customer_id,            
            COALESCE(qg.initial_out_g, 0) AS qty_out,
            COALESCE(qg.initial_out_g_amount, 0) AS qty_out_amount,
            
            COALESCE(dns1.qty_notes_sales, 0) as qty_out_sales_minus1,
            COALESCE(dns2.qty_notes_sales, 0) as qty_out_sales_minus2,
            COALESCE(dns3.qty_notes_sales, 0) as qty_out_sales_minus3
   
            FROM item_fg a
            LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
            LEFT JOIN ($query_delivery_notes_sales_minus1) dns1 ON a.id = dns1.item_fg_id and qg.customer_id=dns1.customer_id
            LEFT JOIN ($query_delivery_notes_sales_minus2) dns2 ON a.id = dns2.item_fg_id and qg.customer_id=dns2.customer_id
            LEFT JOIN ($query_delivery_notes_sales_minus3) dns3 ON a.id = dns3.item_fg_id and qg.customer_id=dns3.customer_id
            WHERE a.status = 0 and qg.customer_id is not null
            ORDER BY a.number
            ";

        // Main Query base on Transaction History RM
        $query = " SELECT aa.customer_id, sum(aa.qty_out) as qty_out, 
        sum((aa.qty_out*coalesce(cc.price,0))/1000000) as qty_out_amount,
        sum(aa.qty_out_sales_minus1) as stock_min1, sum(aa.qty_out_sales_minus2) as stock_min2, sum(aa.qty_out_sales_minus3) as stock_min3,
        bb.name as customer_name
        from (
           $query_main
        ) aa
        left join customers bb on aa.customer_id=bb.id
        left join ($query_standard_price_fb) cc on aa.id=cc.item_fg_id
        group by aa.customer_id, bb.name
        order by bb.name asc
        ";

        // echo $query_main."<br><br><br>";
        // exit();
        
        
        $records = $this->db->query($query)->result_array();
        // $records2 = $this->db->query($query_delivery_notes_sales_minus1)->result_array();
        // echo json_encode($records)."<br><br>";
        //  echo json_encode($records2);
        // exit();

        $main_res = array();
        $pie_res = array();
        $bar_res = array();

        $grandtotal_qty =0;
        $grandtotal_qty_in_amount=0;

        foreach ($records as $row) {
            // --- Contoh Perhitungan Tambahan di luar Query ---
            // Misal: kamu mau hitung selisih atau persentase manual
            $qty_out = (float)$row['qty_out'];
            $qty_out_amount = (float)$row['qty_out_amount'];
            $stock_min1 = (float)$row['stock_min1'];
            $stock_min2 = (float)$row['stock_min2'];
            $stock_min3 = (float)$row['stock_min3'];

            // echo $qty_out."<br><br>";

            $pembagi = 0;

            if($stock_min1>0){
                $pembagi = $pembagi + 1;
            }
            if($stock_min2>0){
                $pembagi = $pembagi + 1;
            }
            if($stock_min3>0){
                $pembagi = $pembagi + 1;
            }

            if($pembagi==0){
                $pembagi=1;
            }

            $avg_3month=($stock_min1+$stock_min2+$stock_min3)/$pembagi;

            if($avg_3month==0){
                $ito_month = 0;
            }else{
                $ito_month = $qty_out / $avg_3month;
            }

            
            $ito_days = $ito_month / 22;

            
           
            
            $main_res[] = array(
                'stock_min1' => $stock_min1,
                'stock_min2' => $stock_min2,
                'stock_min3' => $stock_min3,
                'customer_name' => $row['customer_name'],
                'qty_out'  => $qty_out,
                'qty_out_amount'  => $qty_out_amount,
                'avg_3month'  => $avg_3month,
                'ito_month'  => $ito_month,
                'ito_days'  => $ito_days,
                'pembagi'  => $pembagi,
            );

            $pie_res[] = array(
                'customer_name' => $row['customer_name'],
                'ito_days'  => $ito_days,
            );

            $bar_res[] = array(
                'customer_name' => $row['customer_name'],
                'stock_out'  => $qty_out,
                'avg_3month'  => $avg_3month,
                'ito_days'  => $ito_days,

            );

            $grandtotal_qty += $qty_out;
            $grandtotal_qty_in_amount += $qty_out_amount;
            
        }

        $color = array();

       

        // $color = $this->_generateColors(count($main_res));
        $color = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'];

        usort($main_res, function($a, $b) {
            // Kita bandingkan field ito_days
            // Jika $b > $a, maka $b akan naik ke atas (Descending)
            if ($a['ito_days'] == $b['ito_days']) {
                return 0;
            }
            return ($b['ito_days'] < $a['ito_days']) ? -1 : 1;
        });
        
        //  echo json_encode($main_res);

        // exit();




        $result = array(
            "total" => count($main_res), // Jika tanpa pagination server-side
            "rows"  => $main_res,
            "grandtotal_qty" => $grandtotal_qty,
            "grandtotal_qty_in_amount" => $grandtotal_qty_in_amount,
            "pie_data" => $pie_res,
            "bar_data" => $bar_res,
            "color" => $color,

        );

        echo json_encode($result);
    }

    private function _generateColors($total) {
        $colors = [];
        // Golden ratio conjugate (0.618033988749895)
        // Angka ini membuat lompatan warna yang tersebar merata tapi berjauhan
        $golden_ratio_conjugate = 0.618033988749895;
        $h = rand(0, 360) / 360; // Start dari hue acak

        for ($i = 0; $i < $total; $i++) {
            $h += $golden_ratio_conjugate;
            $h = fmod($h, 1); // Jaga agar tetap di antara 0 dan 1
            
            $hue = $h * 360;
            // Kita gunakan Saturation 75% dan Lightness 60% agar lebih cerah
            $colors[] = $this->_hslToHex($hue, 75, 60);
        }
        return $colors;
    }

    // Fungsi pembantu untuk konversi HSL ke HEX
    private function _hslToHex($h, $s, $l) {
        $l /= 100;
        $a = $s * min($l, 1 - $l) / 100;
        $f = function($n) use ($h, $l, $a) {
            $k = ($n + $h / 30) % 12;
            $color = $l - $a * max(min($k - 3, 9 - $k, 1), -1);
            return str_pad(dechex(round($color * 255)), 2, '0', STR_PAD_LEFT);
        };
        return "#" . $f(0) . $f(8) . $f(4);
    }

    public function get_dashboard_datatables_peritem() 
    {
        $filter_month       = @base64_decode($this->input->get('month'));
        $filter_year        = @base64_decode($this->input->get('year'));
        $filter_display     = @base64_decode($this->input->get('display'));

        $currency_date_from = $filter_year .'-01-01';
        $currency_date_to = $filter_year .'-12-31';

        // Membuat tanggal awal bulan (selalu tanggal 01)
        if($filter_display=='MONTHLY'){
            $filter_from = $filter_year . '-' . $filter_month . '-01';
            $filter_to = $filter_year . '-' . $filter_month . '-' . date('t', strtotime($filter_from));
        }else{
            $filter_from = $filter_year . '-01-01';
            $filter_to = $filter_year . '-12-31';
        }
        

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

        // Step 1: Hitung qty_in dari checksheet
        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus1 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus2 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus3 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY f.item_fg_id";

        // Step 7: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        //-----------------------------------------------------------------

        $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date < '$filter_from'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods2 = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY f.item_fg_id";

        // Step 8: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date < '$filter_from'
        GROUP BY i.item_fg_id";

        //get price from standard price FG
        $query_standard_price_fb = "SELECT 
            a.*, coalesce(b.middle,1) as middle
        FROM standard_price_fg a
        left join standard_exchange_rates b on a.currency = b.currency_from and b.deleted = 0 and b.end_date >='$currency_date_to' and b.currency_to='IDR'
        WHERE a.start_date='$currency_date_from' and a.end_date='$currency_date_to'  and a.deleted=0
        ORDER BY a.id ASC";



        // Step 9: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            
            COALESCE(dns1.qty_notes_sales, 0) as qty_out_sales_minus1,
            COALESCE(dns2.qty_notes_sales, 0) as qty_out_sales_minus2,
            COALESCE(dns3.qty_notes_sales, 0) as qty_out_sales_minus3,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
            LEFT JOIN ($query_delivery_notes_sales_minus1) dns1 ON a.id = dns1.item_fg_id
            LEFT JOIN ($query_delivery_notes_sales_minus2) dns2 ON a.id = dns2.item_fg_id
            LEFT JOIN ($query_delivery_notes_sales_minus3) dns3 ON a.id = dns3.item_fg_id
            LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
            LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id

            LEFT JOIN ( SELECT a.id,
                (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
                (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
                FROM item_fg a
                LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
                LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
                LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
                LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
                LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
                LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
                LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id
                GROUP BY a.id) x ON a.id = x.id
            WHERE  a.status = 0
            ORDER BY a.number
            ";
            

        // Main Query base on Transaction History RM
        $query = " SELECT aa.id, sum(aa.qty_out) as qty_out, 
        sum((aa.qty_out*coalesce(cc.price,0))/1000000) as qty_out_amount,
        sum(aa.qty_out_sales_minus1) as stock_min1, sum(aa.qty_out_sales_minus2) as stock_min2, sum(aa.qty_out_sales_minus3) as stock_min3,
        sum(aa.end_stock) as end_stock,
        sum((aa.end_stock*coalesce(cc.price,0))/1000000) as end_stock_amount
        from (
           $query_main
        ) aa
        -- left join customers bb on aa.customer_id=bb.id
        left join ($query_standard_price_fb) cc on aa.id=cc.item_fg_id
        group by aa.id
        order by aa.id asc
        ";

        // echo $query_main."<br><br><br>";
        // exit();
        
        
        $records = $this->db->query($query)->result_array();
        // $records2 = $this->db->query($query_delivery_notes_sales_minus1)->result_array();
        // echo json_encode($records)."<br><br>";
        //  echo json_encode($records2);
        // exit();

        $main_res = array();
        $pie_res = array();
        $bar_res = array();

        $grandtotal_qty =0;
        $grandtotal_qty_in_amount=0;

        foreach ($records as $row) {
            // --- Contoh Perhitungan Tambahan di luar Query ---
            // Misal: kamu mau hitung selisih atau persentase manual
            $qty_out = (float)$row['qty_out'];
            $qty_out_amount = (float)$row['qty_out_amount'];
            $stock_min1 = (float)$row['stock_min1'];
            $stock_min2 = (float)$row['stock_min2'];
            $stock_min3 = (float)$row['stock_min3'];
            $end_stock = (float)$row['end_stock'];
            $end_stock_amount = (float)$row['end_stock_amount'];

            // echo $qty_out."<br><br>";

            $pembagi = 0;

            if($stock_min1>0){
                $pembagi = $pembagi + 1;
            }
            if($stock_min2>0){
                $pembagi = $pembagi + 1;
            }
            if($stock_min3>0){
                $pembagi = $pembagi + 1;
            }

            if($pembagi==0){
                $pembagi=1;
            }

            $avg_3month=($stock_min1+$stock_min2+$stock_min3)/$pembagi;

            if($avg_3month==0){
                $ito_month = 0;
            }else{
                $ito_month = $end_stock / $avg_3month;
            }

            
            $ito_days = $ito_month / 22;

            
           
            
            $main_res[] = array(
                'stock_min1' => $stock_min1,
                'stock_min2' => $stock_min2,
                'stock_min3' => $stock_min3,
                'id' => $row['id'],
                'end_stock' => $row['end_stock'],
                'qty_out'  => $qty_out,
                'qty_out_amount'  => $qty_out_amount,
                'avg_3month'  => $avg_3month,
                'ito_month'  => $ito_month,
                'ito_days'  => $ito_days,
                'pembagi'  => $pembagi,
            );

            $pie_res[] = array(
                'id' => $row['id'],
                'ito_days'  => $ito_days,
            );

            $bar_res[] = array(
                'id' => $row['id'],
                'stock_out'  => $qty_out,
                'avg_3month'  => $avg_3month,
                'ito_days'  => $ito_days,
                'end_stock'  => $row['end_stock'],
            );

            $grandtotal_qty += $qty_out;
            $grandtotal_qty_in_amount += $qty_out_amount;
            
        }
    }

}
