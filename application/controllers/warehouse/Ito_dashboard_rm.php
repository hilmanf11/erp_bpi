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
class Ito_dashboard_rm extends CI_Controller
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
            $this->load->view('warehouse/ito_dashboard_rm');
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

        // Main Query base on Transaction History RM
        $query = " SELECT aa.prodfam, sum(aa.begin_stock+aa.qty_in-aa.qty_out) as ending_stock, 
        sum((aa.begin_stock+aa.qty_in-aa.qty_out)*coalesce(bb.price,0)*coalesce(bb.middle,1))/1000000 as qty_in_amount,
        sum(aa.qty_out_minus1) as stock_min1, sum(aa.qty_out_minus2) as stock_min2, sum(aa.qty_out_minus3) as stock_min3
        from (
            SELECT 
                a.id,
                a.number, 
                a.name, 
                a.division, 
                b.name as prodfam, 
                a.uom,
                c.name as category_name,
                COALESCE(j.begin_stock, 0) AS begin_stock,
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out,
                (COALESCE(h1.qty_issued, 0) + COALESCE(i1.qty_trans_rm_out, 0)) AS qty_out_minus1,
                (COALESCE(h2.qty_issued, 0) + COALESCE(i2.qty_trans_rm_out, 0)) AS qty_out_minus2,
                (COALESCE(h3.qty_issued, 0) + COALESCE(i3.qty_trans_rm_out, 0)) AS qty_out_minus3,
                (
                COALESCE(begin_whs.begin_bpi, 0)
                + COALESCE(j.begin_stock, 0)
                + COALESCE(in_bpi_now.total_in_bpi, 0)
                - COALESCE(out_bpi_now.total_out_bpi, 0)
                + COALESCE(trf_now.in_bpi, 0)
                - COALESCE(trf_now.out_bpi, 0)
                ) AS qty_bpi,

                (
                COALESCE(begin_whs.begin_plant1, 0)
                + COALESCE(trf_now.in_plant1, 0)
                - COALESCE(trf_now.out_plant1, 0)
                ) AS qty_plant1

            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in_bpi FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' AND a.plant = 'BPI' GROUP BY b.item_rm_id) d_bpi ON a.id = d_bpi.item_rm_id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in_plant1 FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' AND a.plant = 'PLANT 1' GROUP BY b.item_rm_id) d_plant1 ON a.id = d_plant1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' GROUP BY item_rm_id) h1 ON a.id = h1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i1 ON a.id = i1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' GROUP BY item_rm_id) h2 ON a.id = h2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i2 ON a.id = i2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' GROUP BY item_rm_id) h3 ON a.id = h3.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i3 ON a.id = i3.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id

            LEFT JOIN (
                SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                FROM item_rm a
                LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            ) j ON a.id = j.id
            LEFT JOIN (
                SELECT 
                    item_rm_id,
                    SUM(CASE WHEN transfer_from = 'BPI' THEN qty_from ELSE 0 END 
                        + CASE WHEN transfer_to = 'BPI' THEN qty_to ELSE 0 END) AS begin_bpi,
                    SUM(CASE WHEN transfer_from = 'PLANT 1' THEN qty_from ELSE 0 END 
                        + CASE WHEN transfer_to = 'PLANT 1' THEN qty_to ELSE 0 END) AS begin_plant1
                FROM upload_stock_whs_tf
                WHERE trans_date >= '2025-09-18'
                GROUP BY item_rm_id
            ) begin_whs ON a.id = begin_whs.item_rm_id

            LEFT JOIN (
                SELECT 
                    x.item_rm_id,
                    SUM(x.total_in) AS total_in_bpi
                FROM (
                    SELECT b.item_rm_id, SUM(a.qty) AS total_in
                    FROM scan_item_receipts a
                    JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                    WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY b.item_rm_id
                    UNION ALL
                    SELECT item_rm_id, SUM(qty) FROM os_rm
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_rm_id
                    UNION ALL
                    SELECT item_rm_id, SUM(qty) FROM transaction_rm
                    WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND transaction_kind = 'IN'
                    GROUP BY item_rm_id
                    UNION ALL
                    SELECT a.item_rm_id, SUM(c.qty)
                    FROM return_materials a
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                    WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.item_rm_id
                    UNION ALL
                    SELECT item_rm_id, SUM(qty) FROM scan_item_bpm
                    WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_rm_id
                ) x
                GROUP BY x.item_rm_id
            ) in_bpi_now ON a.id = in_bpi_now.item_rm_id

            LEFT JOIN (
                SELECT 
                    x.item_rm_id,
                    SUM(x.total_out) AS total_out_bpi
                FROM (
                    SELECT item_rm_id, SUM(qty) AS total_out
                    FROM issued_material_details
                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d')
                        BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_rm_id
                    UNION ALL
                    SELECT item_rm_id, SUM(qty) AS total_out
                    FROM transaction_rm
                    WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND transaction_kind = 'OUT'
                    GROUP BY item_rm_id
                ) x
                GROUP BY x.item_rm_id
            ) out_bpi_now ON a.id = out_bpi_now.item_rm_id

            LEFT JOIN (
                SELECT 
                    item_rm_id,
                    SUM(CASE WHEN transfer_to = 'BPI' THEN qty ELSE 0 END) AS in_bpi,
                    SUM(CASE WHEN transfer_from = 'BPI' THEN qty ELSE 0 END) AS out_bpi,
                    SUM(CASE WHEN transfer_to = 'PLANT 1' THEN qty ELSE 0 END) AS in_plant1,
                    SUM(CASE WHEN transfer_from = 'PLANT 1' THEN qty ELSE 0 END) AS out_plant1
                FROM scan_rm_transfer
                WHERE DATE_FORMAT(transaction_date, '%Y-%m-%d')
                    BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY item_rm_id
            ) trf_now ON a.id = trf_now.item_rm_id

            WHERE 
            a.item_category_id NOT IN ('C06','C11')
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number
        ) aa 
        left join (
            SELECT 
                a.*, coalesce(b.middle,1) as middle
            FROM standard_price_rm a
            left join standard_exchange_rates b on a.currency = b.currency_from and b.deleted = 0 and b.end_date >='$currency_date_to' and b.currency_to='IDR'
            WHERE a.start_date='$currency_date_from' and a.end_date='$currency_date_to'  and a.deleted=0
            ORDER BY a.id ASC
        ) bb on aa.id = bb.item_rm_id
        group by aa.prodfam order by aa.prodfam asc
        ";

        // echo $query;
        
        
        $records = $this->db->query($query)->result_array();
        // die($this->db->last_query());

        $main_res = array();
        $pie_res = array();
        $bar_res = array();

        $grandtotal_qty =0;
        $grandtotal_qty_in_amount=0;

        foreach ($records as $row) {
            // --- Contoh Perhitungan Tambahan di luar Query ---
            // Misal: kamu mau hitung selisih atau persentase manual
            $ending_stock = (float)$row['ending_stock'];
            $qty_in_amount = (float)$row['qty_in_amount'];
            $stock_min1 = (float)$row['stock_min1'];
            $stock_min2 = (float)$row['stock_min2'];
            $stock_min3 = (float)$row['stock_min3'];

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
                $ito_month = $ending_stock / $avg_3month;
            }

            
            $ito_days = $ito_month / 22;

            
           
            
            $main_res[] = array(
                'stock_min1' => $stock_min1,
                'stock_min2' => $stock_min2,
                'stock_min3' => $stock_min3,
                'prodfam' => $row['prodfam'],
                'ending_stock'  => $ending_stock,
                'qty_in_amount'  => $qty_in_amount,
                'avg_3month'  => $avg_3month,
                'ito_month'  => $ito_month,
                'ito_days'  => $ito_days,
                'pembagi'  => $pembagi,
            );

            $pie_res[] = array(
                'prodfam' => $row['prodfam'],
                'ito_days'  => $ito_days,
            );

            $bar_res[] = array(
                'prodfam' => $row['prodfam'],
                'stock_in'  => $ending_stock,
                'avg_3month'  => $avg_3month,
                'ito_days'  => $ito_days,

            );

            $grandtotal_qty += $ending_stock;
            $grandtotal_qty_in_amount += $qty_in_amount;
            
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

}
