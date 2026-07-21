<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mps extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mps');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    public function check_menu_loading() {
        $this->load->database();
        
        $query = $this->db->query("
            SELECT a.id, a.number as item_number 
            FROM item_fg a 
            WHERE NOT EXISTS (
                SELECT 1 FROM menu_loadings b WHERE b.item_fg_id = a.id
            ) and a.status = 0
        ");
    
        echo json_encode($query->result());
    }

    public function getData()
    {
        if ($this->input->get()) {
            file_put_contents('failed/generate_mps.txt', '');
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $period = date("Y-m-t", strtotime("-1 month", strtotime($filter_cutoff)));
            $period2 = $filter_year.$filter_month;

            $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
            $monthName_1 = date("Y-m", $monthStart);
            $monthName_2 = date("Y-m", strtotime("+1 month", $monthStart));
            $monthName_3 = date("Y-m", strtotime("+2 month", $monthStart));
            $monthName_4 = date("Y-m", strtotime("+3 month", $monthStart));
            $monthName_5 = date("Y-m", strtotime("+4 month", $monthStart));
            $monthName_6 = date("Y-m", strtotime("+5 month", $monthStart));
            $cutoff_start = date("Y-m-d", strtotime("+1 day", strtotime($filter_cutoff)));
            $cutoff_end = date("Y-m-t", strtotime($filter_cutoff));
            $lastDatePrevMonth = date("Y-m-t", strtotime("-1 month", $monthStart));

            //Configuration Planning
            $this->db->select('*');
            $this->db->from("config");
            $config = $this->db->get()->row();

            //

            //Select Query a.safety_stock
            $this->db->select("a.id, a.number, a.name, COALESCE(j.safety_stock,0) as safety_stock, a.number_customer,
                (CASE 
                    WHEN a.default_packing = 'BOX' THEN COALESCE(a.qty_box, 0) 
                    ELSE COALESCE(a.mpq, 0) 
                END) as mpq,
                COALESCE(c.pp, 0) as injection,
                COALESCE(c.p1, 0) as assembly,
                COALESCE(c.p2, 0) as onhold,
                COALESCE(c.p3, 0) as subcont,
                COALESCE(d.qty, 0) as os_mpp,
                COALESCE(e.stock_fg, 0) as stock_fg,
                COALESCE(f.os_so, 0) as os_so,
                COALESCE(g.month_1, 0) as month_1,
                COALESCE(g.month_2, 0) as month_2,
                COALESCE(g.month_3, 0) as month_3,
                COALESCE(g.month_4, 0) as month_4,
                COALESCE(g.month_5, 0) as month_5,
                COALESCE(g.month_6, 0) as month_6,
                COALESCE(g.month_7, 0) as month_7,
                COALESCE(h.cycle_time, 0) as cycle_time,
                COALESCE(h.shift_hour, 0) as shift_hour,
                COALESCE(h.productcivity, 0) as productivity,
                COALESCE(h.cavity_standard, 0) as cavity_standard,
                h.priority,
                (COALESCE(i.so_month_1, 0) + COALESCE(k.qty, 0)) as qty_so,
                COALESCE(i.so_month_2, 0) as so_month_2,
                COALESCE(i.so_month_3, 0) as so_month_3,
                COALESCE(i.so_month_4, 0) as so_month_4,
                COALESCE(i.so_month_5, 0) as so_month_5,
                COALESCE(i.so_month_6, 0) as so_month_6");
            $this->db->from('item_fg a');
            $this->db->join('item_fg_subs b', "a.id = b.item_fg_sa_id", 'left');
            $this->db->join('stock_wip c', "a.id = c.item_fg_id and c.p_month = '$filter_month' and c.p_year = '$filter_year' and c.revision = '$filter_revision'", 'left');
            $this->db->join("
                (
                    SELECT item_fg_id,
                        SUM(qty) AS qty
                    FROM os_mpp
                    WHERE p_month = '$filter_month'
                    AND p_year = '$filter_year'
                    AND revision = (
                        SELECT MAX(revision)
                        FROM os_mpp m2
                        WHERE m2.p_month = '$filter_month'
                        AND m2.p_year = '$filter_year'
                    )
                    GROUP BY item_fg_id
                ) d",
                "a.id = d.item_fg_id", "left"
            );
            $this->db->join("(SELECT a.id, (COALESCE(b.qty_scan, 0) + COALESCE(c.qty_nb, 0) + COALESCE(d.qty_adj_in, 0) + COALESCE(e.qty_wip, 0) - COALESCE(f.qty_adj_out, 0) - COALESCE(g.qty_dn, 0) - COALESCE(h.qty_repair, 0)) AS stock_fg
                FROM item_fg a
                LEFT JOIN (SELECT a.item_fg_id, SUM(a.qty) AS qty_scan FROM scan_item_receipts_fg a JOIN checksheets b ON a.checksheet_number = b.number AND a.item_fg_id = b.item_fg_id WHERE b.packing_date <= '$filter_cutoff' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_nb FROM scan_item_receipts_fg WHERE `type` = 'NBFG' AND packing_date <= '$filter_cutoff' GROUP BY item_fg_id) c ON a.id = c.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_adj_in FROM transaction_fg WHERE transaction_kind = 'IN' AND request_date <= '$filter_cutoff' GROUP BY item_fg_id) d ON a.id = d.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_wip FROM wip_receipts WHERE division = 'MTS' AND trans_date <= '$filter_cutoff' GROUP BY item_fg_id) e ON a.id = e.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_adj_out FROM transaction_fg WHERE transaction_kind = 'OUT' AND request_date <= '$filter_cutoff' GROUP BY item_fg_id) f ON a.id = f.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_dn FROM delivery_notes WHERE delivery_note_date <= '$filter_cutoff' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
                LEFT JOIN (SELECT a.item_fg_id, SUM(a.qty) AS qty_repair FROM scan_repair_of_goods a JOIN repair_of_goods b ON a.document_no = b.document_no AND a.item_fg_id = b.item_fg_id WHERE b.trans_date <= '$filter_cutoff' GROUP BY a.item_fg_id) h ON a.id = h.item_fg_id
                GROUP BY a.id) e", '(a.id = e.id or b.item_fg_id = e.id)', 'left');
            $this->db->join("(SELECT z.id, SUM(z.qty_outstanding) AS os_so FROM (
                SELECT b.id, b.number_customer, a.qty_so, COALESCE(c.qty_delivery, 0) AS qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                FROM (SELECT *, SUM(qty) AS qty_so FROM sales_orders WHERE `status` = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes WHERE delivery_note_date <= '$lastDatePrevMonth' GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                WHERE a.delivery_date <= '$lastDatePrevMonth'
                GROUP BY a.sales_order_no, a.item_fg_id) z
                GROUP BY z.id) f", '(a.id = f.id or b.item_fg_id = f.id)', 'left');

            // $this->db->join("
            // (
            //     SELECT 
            //         T.item_fg_id,

            //         MAX(CASE WHEN T.idx = 1 THEN T.final_value END) AS month_1,
            //         MAX(CASE WHEN T.idx = 2 THEN T.final_value END) AS month_2,
            //         MAX(CASE WHEN T.idx = 3 THEN T.final_value END) AS month_3,
            //         MAX(CASE WHEN T.idx = 4 THEN T.final_value END) AS month_4,
            //         MAX(CASE WHEN T.idx = 5 THEN T.final_value END) AS month_5,
            //         MAX(CASE WHEN T.idx = 6 THEN T.final_value END) AS month_6

            //     FROM
            //     (
            //         --
            //         -- Buat list (item_fg_id, customer_id) dari seluruh bulan yg relevan (target +/- fallback)
            //         --
            //         SELECT 
            //             fc.item_fg_id,
            //             fc.customer_id,
            //             m.idx,
            //             (
            //                 SELECT 
            //                     COALESCE(
            //                         /* 1. ambil forecast bulan target (month_1) */
            //                         (SELECT f1.month_1 
            //                         FROM forecasts f1 
            //                         WHERE f1.item_fg_id = fc.item_fg_id
            //                         AND f1.customer_id = fc.customer_id
            //                         AND CONCAT(f1.p_year,'-',LPAD(f1.p_month,2,'0')) = DATE_FORMAT(m.period,'%Y-%m')
            //                         ORDER BY f1.revision DESC LIMIT 1),

            //                         /* 2. fallback 1 bulan sebelum → month_2 */
            //                         (SELECT f2.month_2 
            //                         FROM forecasts f2 
            //                         WHERE f2.item_fg_id = fc.item_fg_id
            //                         AND f2.customer_id = fc.customer_id
            //                         AND CONCAT(f2.p_year,'-',LPAD(f2.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 1 MONTH),'%Y-%m')
            //                         ORDER BY f2.revision DESC LIMIT 1),

            //                         /* 3. fallback 2 bulan sebelum → month_3 */
            //                         (SELECT f3.month_3 
            //                         FROM forecasts f3 
            //                         WHERE f3.item_fg_id = fc.item_fg_id
            //                         AND f3.customer_id = fc.customer_id
            //                         AND CONCAT(f3.p_year,'-',LPAD(f3.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 2 MONTH),'%Y-%m')
            //                         ORDER BY f3.revision DESC LIMIT 1),

            //                         /* 4. fallback 3 bulan sebelum → month_4 */
            //                         (SELECT f4.month_4 
            //                         FROM forecasts f4 
            //                         WHERE f4.item_fg_id = fc.item_fg_id
            //                         AND f4.customer_id = fc.customer_id
            //                         AND CONCAT(f4.p_year,'-',LPAD(f4.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 3 MONTH),'%Y-%m')
            //                         ORDER BY f4.revision DESC LIMIT 1),

            //                         0
            //                     )
            //             ) AS final_value
            //         FROM 
            //             (
            //                 SELECT DISTINCT item_fg_id, customer_id
            //                 FROM forecasts
            //                 WHERE deleted = 0
            //                 AND (
            //                     /* ambil rentang periode: filter_month -3  sampai filter_month +5 (6 bulan forward + 3 fallback back) */
            //                     CONCAT(p_year,'-',LPAD(p_month,2,'0')) BETWEEN
            //                         DATE_FORMAT(DATE_SUB(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 3 MONTH),'%Y-%m')
            //                     AND
            //                         DATE_FORMAT(DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 5 MONTH),'%Y-%m')
            //                 )
            //             ) fc

            //         CROSS JOIN (
            //             /* generate 6 bulan dari filter */
            //             SELECT 1 AS idx, DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH) AS period
            //             UNION ALL SELECT 2, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 1 MONTH)
            //             UNION ALL SELECT 3, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 2 MONTH)
            //             UNION ALL SELECT 4, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 3 MONTH)
            //             UNION ALL SELECT 5, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 4 MONTH)
            //             UNION ALL SELECT 6, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 5 MONTH)
            //         ) m

            //     ) T

            //     GROUP BY T.item_fg_id

            // ) g", "a.id = g.item_fg_id OR b.item_fg_id = g.item_fg_id", "left");

            //dokumentasi : add month_7 dan cum item beda customer
            $this->db->join("
            (
                SELECT 
                    T.item_fg_id,
                    SUM(CASE WHEN T.idx = 1 THEN T.final_value END) AS month_1,
                    SUM(CASE WHEN T.idx = 2 THEN T.final_value END) AS month_2,
                    SUM(CASE WHEN T.idx = 3 THEN T.final_value END) AS month_3,
                    SUM(CASE WHEN T.idx = 4 THEN T.final_value END) AS month_4,
                    SUM(CASE WHEN T.idx = 5 THEN T.final_value END) AS month_5,
                    SUM(CASE WHEN T.idx = 6 THEN T.final_value END) AS month_6,
                    SUM(CASE WHEN T.idx = 7 THEN T.final_value END) AS month_7

                FROM
                (
                    --
                    -- Buat list (item_fg_id, customer_id) dari seluruh bulan yg relevan (target +/- fallback)
                    --
                    SELECT 
                        fc.item_fg_id,
                        fc.customer_id,
                        m.idx,
                        (
                            SELECT 
                                COALESCE(
                                    /* 1. ambil forecast bulan target (month_1) */
                                    (SELECT f1.month_1 
                                    FROM forecasts f1 
                                    WHERE f1.item_fg_id = fc.item_fg_id
                                    AND f1.customer_id = fc.customer_id
                                    AND CONCAT(f1.p_year,'-',LPAD(f1.p_month,2,'0')) = DATE_FORMAT(m.period,'%Y-%m')
                                    ORDER BY f1.revision DESC LIMIT 1),

                                    /* 2. fallback 1 bulan sebelum → month_2 */
                                    (SELECT f2.month_2 
                                    FROM forecasts f2 
                                    WHERE f2.item_fg_id = fc.item_fg_id
                                    AND f2.customer_id = fc.customer_id
                                    AND CONCAT(f2.p_year,'-',LPAD(f2.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 1 MONTH),'%Y-%m')
                                    ORDER BY f2.revision DESC LIMIT 1),

                                    /* 3. fallback 2 bulan sebelum → month_3 */
                                    (SELECT f3.month_3 
                                    FROM forecasts f3 
                                    WHERE f3.item_fg_id = fc.item_fg_id
                                    AND f3.customer_id = fc.customer_id
                                    AND CONCAT(f3.p_year,'-',LPAD(f3.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 2 MONTH),'%Y-%m')
                                    ORDER BY f3.revision DESC LIMIT 1),

                                    /* 4. fallback 3 bulan sebelum → month_4 */
                                    (SELECT f4.month_4 
                                    FROM forecasts f4 
                                    WHERE f4.item_fg_id = fc.item_fg_id
                                    AND f4.customer_id = fc.customer_id
                                    AND CONCAT(f4.p_year,'-',LPAD(f4.p_month,2,'0')) = DATE_FORMAT(DATE_SUB(m.period, INTERVAL 3 MONTH),'%Y-%m')
                                    ORDER BY f4.revision DESC LIMIT 1),

                                    0
                                )
                        ) AS final_value
                    FROM 
                        (
                            SELECT DISTINCT item_fg_id, customer_id
                            FROM forecasts
                            WHERE deleted = 0
                            AND (
                                /* ambil rentang periode: filter_month -3  sampai filter_month +6 (7 bulan forward + 3 fallback back) */
                                CONCAT(p_year,'-',LPAD(p_month,2,'0')) BETWEEN
                                    DATE_FORMAT(DATE_SUB(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 3 MONTH),'%Y-%m')
                                AND
                                    -- Ubah INTERVAL 5 MONTH menjadi 6 MONTH agar mencakup bulan ke-7
                                    DATE_FORMAT(DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 6 MONTH),'%Y-%m')
                            )
                        ) fc

                    CROSS JOIN (
                        /* generate 7 bulan dari filter */
                        SELECT 1 AS idx, DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH) AS period
                        UNION ALL SELECT 2, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 1 MONTH)
                        UNION ALL SELECT 3, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 2 MONTH)
                        UNION ALL SELECT 4, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 3 MONTH)
                        UNION ALL SELECT 5, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 4 MONTH)
                        UNION ALL SELECT 6, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 5 MONTH)
                        UNION ALL SELECT 7, DATE_ADD(DATE_ADD(MAKEDATE({$filter_year},1), INTERVAL {$filter_month}-1 MONTH), INTERVAL 6 MONTH) -- Tambahan bulan ke-7
                    ) m

                ) T

                GROUP BY T.item_fg_id

            ) g", "a.id = g.item_fg_id OR b.item_fg_id = g.item_fg_id", "left");

            $this->db->join("(SELECT DISTINCT a.item_fg_id, a.cycle_time, a.shift_hour, a.productcivity, b.cavity_standard, a.priority FROM menu_loadings a JOIN molds b ON a.mold_id = b.id WHERE a.priority = 1 GROUP BY a.item_fg_id) h ", 'a.id = h.item_fg_id', 'left');
            
            // $this->db->join("(SELECT z.id, SUM(z.qty_outstanding) AS qty FROM (
            //     SELECT b.id, b.number_customer, a.qty_so, COALESCE(c.qty_delivery, 0) AS qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
            //     FROM (SELECT *, SUM(qty) AS qty_so FROM sales_orders WHERE `status` = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
            //     JOIN item_fg b ON a.item_fg_id = b.id
            //     LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
            //     WHERE a.delivery_date like '%$monthName3%'
            //     GROUP BY a.sales_order_no, a.item_fg_id) z
            //     GROUP BY z.id) i", '(a.id = i.id or b.item_fg_id = i.id)', 'left');

            $this->db->join("(SELECT a.id, 
                COALESCE(b.qty_outstanding, 0) AS so_month_1, 
                COALESCE(c.qty_outstanding, 0) AS so_month_2,
                COALESCE(d.qty_outstanding, 0) AS so_month_3,
                COALESCE(e.qty_outstanding, 0) AS so_month_4,
                COALESCE(f.qty_outstanding, 0) AS so_month_5,
                COALESCE(g.qty_outstanding, 0) AS so_month_6
                FROM item_fg a
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_1%'
                    GROUP BY b.id) b ON a.id = b.id
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_2%'
                    GROUP BY b.id) c ON a.id = c.id
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_3%'
                    GROUP BY b.id) d ON a.id = d.id
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_4%'
                    GROUP BY b.id) e ON a.id = e.id
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_5%'
                    GROUP BY b.id) f ON a.id = f.id
                LEFT JOIN (SELECT b.id,SUM(a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                    FROM (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_so, delivery_date FROM sales_orders WHERE status = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                    WHERE a.delivery_date LIKE '%$monthName_6%'
                    GROUP BY b.id) g ON a.id = g.id
                GROUP BY a.id) i", '(a.id = i.id or b.item_fg_id = i.id)', 'left');
            
            $this->db->join("(SELECT z.id, SUM(z.qty_outstanding) AS qty FROM (
                SELECT b.id, b.number_customer, a.qty_so, COALESCE(c.qty_delivery, 0) AS qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                FROM (SELECT *, SUM(qty) AS qty_so FROM sales_orders WHERE `status` = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                WHERE a.delivery_date between '$cutoff_start' and '$cutoff_end'
                GROUP BY a.sales_order_no, a.item_fg_id) z
                GROUP BY z.id) k", '(a.id = k.id or b.item_fg_id = k.id)', 'left');
            $this->db->join("(SELECT item_fg_id, safety_stock FROM safety_stocks GROUP BY item_fg_id) j ", '(a.id = j.item_fg_id or b.item_fg_id = j.item_fg_id)', 'left');
            if ($filter_product_no != "") {
                $this->db->where('a.id', $filter_product_no);
            }
            $this->db->where('a.status', 0);
            $this->db->where('a.division_id', 'DIV01');
            $this->db->where_in('a.type', ['FG', 'SA']);
            $this->db->group_by('a.id');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                $totalStock = ($data['injection'] + $data['assembly'] + $data['onhold'] + $data['subcont'] + $data['os_mpp'] + $data['stock_fg']);
                if (@$stock_fg == null) {
                    $fg = "0";
                } else {
                    $fg = @$stock_fg;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff . " " . date("H:i:s"),
                    "item_fg_id" => $data['id'],
                    "wip_month" => strtoupper($monthBack),
                    "pp" => $data['injection'],
                    "p1" => $data['assembly'],
                    "p2" => $data['onhold'],
                    "p3" => $data['subcont'],
                    "fg" => $data['stock_fg'],
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => "$totalStock",
                    "os_so" => $data['os_so'],
                    "month_1" => $data['month_1'],
                    "month_2" => $data['month_2'],
                    "month_3" => $data['month_3'],
                    "month_4" => $data['month_4'],
                    "month_5" => $data['month_5'],
                    "month_6" => $data['month_6'],
                    "month_7" => $data['month_7'],
                    "cycle_time" => $data['cycle_time'],
                    "shift_hour" => $data['shift_hour'],
                    "productivity" => $data['productivity'],
                    "cavity_standard" => $data['cavity_standard'],
                    "priority" => $data['priority'],
                    "qty_so" => $data['qty_so'],
                    "so_month_2" => $data['so_month_2'],
                    "so_month_3" => $data['so_month_3'],
                    "so_month_4" => $data['so_month_4'],
                    "so_month_5" => $data['so_month_5'],
                    "so_month_6" => $data['so_month_6'],
                    "safety_stock" => $data['safety_stock'],
                    "mpq" => $data['mpq']
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function revision()
    {
        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');

        $this->db->select('revision');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('deleted', 0);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $this->db->limit(1);
        $record = $this->db->get()->row();
        echo @$record->revision ? $record->revision : 0;
    }

    public function checkForecast()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('forecasts');
        // $this->db->where("approved_to = ''");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();
        
        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkFg()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        // $this->db->select('*');
        // $this->db->from('stock_fg');
        // $this->db->where("approved_to = ''");
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        // } else {
            // echo json_encode(array("theme" => "error"));
        // }
    }

    public function checkOs()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_orders');
        $this->db->where("status", 0);
        $this->db->like("delivery_date", $period);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstSo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        // //Select Query
        // $this->db->select('*');
        // $this->db->from('os_so');
        // //$this->db->where('approved_to', '');
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
        //     echo json_encode(array("theme" => "success"));
        // } else {
        //     echo json_encode(array("theme" => "error"));
        // }

        $period = $filter_year . "-" . $filter_month;

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_orders');
        $this->db->where("status", 0);
        $this->db->like("delivery_date", $period);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkStockWip()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('stock_wip');
        // $this->db->where("approved_to = ''");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        // Select Query
        $this->db->select('*');
        $this->db->from('os_mpp');
        //$this->db->where("approved_to = ''");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
           echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkCalendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd = strtotime(date('Y-m-d', strtotime('+6 month', $monthStart)));

        $html = "";
        $no = 1;
        
        while ($monthStart < $monthEnd) {
            $monthName = date('m/Y', $monthStart);
            $start = strtotime(date('Y-m-01', $monthStart));
            $finish = strtotime(date('Y-m-t', $monthStart));

            $this->db->select('working_date');
            $this->db->from('calendars');
            $this->db->where('working_date >=', date('Y-m-01', $monthStart));
            $this->db->where('working_date <=', date('Y-m-t', $monthStart));
            $this->db->where("remarks !=", "");
            $this->db->where('deleted', 0); 
            $holidays = $this->db->get()->result_array();
            
            $datesWithRemarks = array_column($holidays, 'working_date');

            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += 86400) { // 86400 detik = 1 hari
                $working_date = date('Y-m-d', $z);
                $isSunday = (date('w', $z) == 0);
                
                $hasRemark = in_array($working_date, $datesWithRemarks);

                if ($isSunday) {
                    if ($hasRemark) {
                        $hkw += 1; // Hari Minggu ada remark = Lembur (Masuk)
                    }
                } else {
                    if (!$hasRemark) {
                        $hkw += 1; // Hari biasa tidak ada remark = Masuk normal
                    }
                }
            }

            $html .= '<div style="margin:15px;">
                        HKW ' . $no . ' : ' . $monthName . ' : <b>' . $hkw . '</b>
                    </div>';

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        echo $html;
    }

    public function create()
    {   
        if ($this->input->post()) {
            $post = $this->input->post('data');
            // var_dump($post);
            // die;
            // $postDetails = $post['details'];

            $i = 1;
            $beginBalance = 0;
            $soData = 0;
            $forecastData = 0;
            $deliveryRate = 0;
            $ito = 0;
            $safetyStock = 0;
            $prodPlan = 0;

            $varBackYear = date('Y', strtotime('+1 month', strtotime($post['p_year'] . "-" . $post['p_month'] . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($post['p_year'] . "-" . $post['p_month'] . "-01")));

            $monthStart = strtotime($post['p_year'] . "-" . $post['p_month'] . "-01");
            $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

            while ($monthStart < $monthEnd) {
                $monthName = date('F Y', $monthStart);
                $monthName2 = date('Y-m-01', $monthStart);

                $start = strtotime(date('Y-m-01', $monthStart));
                $finish = strtotime(date('Y-m-t', $monthStart));

                $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

                $hkw = 0;
                for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $z);

                    $this->db->select('remarks');
                    $this->db->from('calendars');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    if (date('w', $z) !== '0') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            $hkw += 0;
                        } else {
                            $hkw += 1;
                        }
                    } else {
                        $hkw += 0;
                    }
                }
                
                $hkw2 = 0;
                for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
                    $working_date2 = date('Y-m-d', $x);

                    $this->db->select('remarks');
                    $this->db->from('calendars');
                    $this->db->where('working_date', $working_date2);
                    $holiday2 = $this->db->get()->row();

                    if (date('w', $x) !== '0') {
                        if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
                            $hkw2 += 0;
                        } else {
                            $hkw2 += 1;
                        }
                    } else {
                        $hkw2 += 0;
                    }
                }

                //Dokumentasi
                // $mpq = isset($post['mpq']) ? (float)$post['mpq'] : 0;

                // if (!empty($post['cycle_time']) && !empty($post['productivity'])) {
                //     $cavity = $post['cavity_standard'];
                //     $cycle_time = $post['cycle_time'];
                //     $shift_hour = $post['shift_hour'];
                //     $productivity = $post['productivity'];

                //     $capacityPerShift = round((3600 / $cycle_time) * $cavity * $shift_hour * ($productivity/100));
                // } else {
                //     $this->db->select('number');
                //     $this->db->where('id', $post['item_fg_id']);
                //     $cek_item = $this->db->get('item_fg')->row();
                //     $item_number = (!empty($cek_item)) ? $cek_item->number : 'Unknown Item';

                //     echo json_encode([
                //         'theme'   => 'failed', 
                //         'title'   => 'FAILED',
                //         'message' => 'Please Check Item [' . $item_number . '] in Menu Loading.'
                //     ]);
                //     exit; // Hentikan eksekusi script untuk item ini
                // }

                $this->db->select('number');
                $this->db->where('id', $post['item_fg_id']);
                $cek_item = $this->db->get('item_fg')->row();
                $item_number = (!empty($cek_item)) ? $cek_item->number : 'Unknown Item';

                // 1A. Validasi Keberadaan Data (Jika kosong / tidak ada di Menu Loading)
                if (!isset($post['priority']) || $post['priority'] === "") {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Data Not Found in Menu Loading for Item [' . $item_number . ']'
                    ]);
                    exit;
                }

                if ($post['priority'] != 1) {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Priority for Item [' . $item_number . '] not 1 (Current Priority: ' . $post['priority'] . ')'
                    ]);
                    exit;
                }

                $mpq = isset($post['mpq']) ? (float)$post['mpq'] : 0;
                if ($mpq <= 0) {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Data MPQ for Item [' . $item_number . '] should not 0 or Null.'
                    ]);
                    exit;
                }

                if (empty($post['cycle_time']) || empty($post['productivity']) || empty($post['cavity_standard']) || (float)$post['cavity_standard'] <= 0) {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Please Check Item [' . $item_number . '] in Menu Loading. (Cavity, Cycle Time, or Productivity should not 0)'
                    ]);
                    exit;
                }

                // 4. Tarik nilai dan jadikan desimal (float) terlebih dahulu
                $cavity = isset($post['cavity_standard']) ? (float)$post['cavity_standard'] : 0;
                $cycle_time = isset($post['cycle_time']) ? (float)$post['cycle_time'] : 0;
                $shift_hour = isset($post['shift_hour']) ? (float)$post['shift_hour'] : 0;
                $productivity = isset($post['productivity']) ? (float)$post['productivity'] : 0;

                // Validasi SUPER KETAT: Murni memeriksa angka, bukan sekadar "empty"
                if ($cycle_time <= 0 || $productivity <= 0 || $cavity <= 0) {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Please Check Item [' . $item_number . '] in Menu Loading. (Cavity, Cycle Time, or Productivity should not be 0 or Null)'
                    ]);
                    exit;
                }

                $capacityPerShift = round((3600 / $cycle_time) * $cavity * $shift_hour * ($productivity/100));

                if ($capacityPerShift <= 0) {
                    echo json_encode([
                        'theme'   => 'error', 
                        'title'   => 'FAILED',
                        'message' => 'Capacity Per Shift for Item [' . $item_number . '] is 0. Please Check Data in Menu Loading.'
                    ]);
                    exit;
                }

                //Bulan Pertama - Keenam
                if ($i == 1) {
                    $beginBalance = $post['total_stock'] - $post['os_so'];
                    $soData = @round($post['qty_so']);
                    $forecastData = @round($post['month_1']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_2'] * (@$post['safety_stock'] / 100));//perubahan : perhitungan safetyStock : forecast next month
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                } else if ($i == 2) {
                    $beginBalance = (($prodPlan + $beginBalance) - $qtySoFc - $safetyStock);
                    $soData = @round($post['so_month_2']);
                    $forecastData = @round($post['month_2']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_3'] * (@$post['safety_stock'] / 100));
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                } elseif ($i == 3) {
                    $beginBalance = (($prodPlan + $beginBalance) - $qtySoFc - $safetyStock);
                    $soData = @round($post['so_month_3']);
                    $forecastData = @round($post['month_3']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_4'] * (@$post['safety_stock'] / 100));
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                } elseif ($i == 4) {
                    $beginBalance = (($prodPlan + $beginBalance) - $qtySoFc - $safetyStock);
                    $soData = @round($post['so_month_4']);
                    $forecastData = @round($post['month_4']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_5'] * (@$post['safety_stock'] / 100));
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                } elseif ($i == 5) {
                    $beginBalance = (($prodPlan + $beginBalance) - $qtySoFc - $safetyStock);
                    $soData = @round($post['so_month_5']);
                    $forecastData = @round($post['month_5']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_6'] * (@$post['safety_stock'] / 100));
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                } elseif ($i == 6) {
                    $beginBalance = (($prodPlan + $beginBalance) - $qtySoFc - $safetyStock);
                    $soData = @round($post['so_month_6']);
                    $forecastData = @round($post['month_6']);
                    if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                    $deliveryRate = @round($qtySoFc / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);
                    $safetyStock = @round($post['month_7'] * (@$post['safety_stock'] / 100));
                    // $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    $need = round($beginBalance - $qtySoFc - $safetyStock);
                    $need = ($need < 0) ? abs($need) : 0;
                    $prodplan = ceil($need / $capacityPerShift) * $capacityPerShift;
                    $prodplan = ceil($prodplan / $mpq) * $mpq;
                }

                $monthStart = strtotime("+1 month", $monthStart);
                $i++;

                $beginBalance = $beginBalance;
                $forecast = $forecastData;
                $deliveryRate = $deliveryRate;
                $ito = $ito;
                $safetyStock = $safetyStock;
                $prodPlan = $prodplan;
                $balance = (($prodPlan + $beginBalance) - $forecast);

                $generateMpsDetails = $this->crud->reads('generate_mps_details', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    // "customer_id" => $postDetail['customer_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "ltpp_month" => strtoupper($monthName),
                ]);

                $postFinalDetail = array(
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    // "customer_id" => $postDetail['customer_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "ltpp_month" => strtoupper($monthName),
                    "ltpp_month2" => $monthName2,
                    "hkw" => "$hkw2",
                    "begin_balance" => "$beginBalance",
                    "ito" => "$ito",
                    "so" => "$soData",//dari $sales_order
                    "forecast" => "$forecastData",
                    "delivery_rate" => "$deliveryRate",
                    "safety_stock" => "$safetyStock",
                    "need" => "$need",
                    "prod_plan" => "$prodplan"
                );

                if (count($generateMpsDetails) > 0) {
                    $send = $this->db->update('generate_mps_details', $postFinalDetail, [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        // "customer_id" => $postDetail['customer_id'],
                        "item_fg_id" => $post['item_fg_id'],
                        "ltpp_month" => strtoupper($monthName),
                    ]);
                } else {
                    $this->crud->createNotLog('generate_mps_details', $postFinalDetail);
                }
            }

            // ---------------------------------------------------------------
            // $generateMps = $this->crud->reads('generate_mps', [], [
            //     "p_month" => $post['p_month'],
            //     "p_year" => $post['p_year'],
            //     "revision" => $post['revision'],
            //     // "customer_id" => $post['customer_id'],
            //     "item_fg_id" => $post['item_fg_id'],
            //     "wip_month" => $post['wip_month']
            // ]);

            // // var_dump($post);
            // // die;

            // $postFinal = array(
            //     "p_month" => $post['p_month'],
            //     "p_year" => $post['p_year'],
            //     "revision" => $post['revision'],
            //     "cutoff" => $post['cutoff'],
            //     // "customer_id" => $post['customer_id'],
            //     "item_fg_id" => $post['item_fg_id'],
            //     "wip_month" => $post['wip_month'],
            //     "pp" => $post['pp'],
            //     "p1" => $post['p1'],
            //     "p2" => $post['p2'],
            //     "p3" => $post['p3'],
            //     "fg" => $post['fg'],
            //     "os_mpp" => $post['os_mpp'],
            //     "os_so" => $post['os_so'],
            //     "total_stock" => $post['total_stock'],
            //     "balance" => $balance
            // );

            // if (count($generateMps) > 0) {
            //     $send   = $this->db->update('generate_mps', $postFinal, [
            //         "p_month" => $post['p_month'],
            //         "p_year" => $post['p_year'],
            //         "revision" => $post['revision'],
            //         // "customer_id" => $post['customer_id'],
            //         "item_fg_id" => $post['item_fg_id'],
            //         "wip_month" => $post['wip_month']
            //     ]);
            //     // echo $send;
            //     echo json_encode([
            //         'theme' => 'success',
            //         'title' => 'Updated',
            //         'message' => 'Update data Successfully.'
            //     ]);
            // } else {
            //     $send = $this->crud->createNotLog('generate_mps', $postFinal);
            //     // echo $send;
            //     echo json_encode([
            //         'theme' => 'success',
            //         'title' => 'Created',
            //         'message' => 'Create data Successfully.'
            //     ]);
            // }
            //-------------------------------------------------------------------------
            $this->db->where('p_month', $post['p_month']);
            $this->db->where('p_year', $post['p_year']);
            $this->db->where('revision', $post['revision']);
            $this->db->where('item_fg_id', $post['item_fg_id']);
            $this->db->where('wip_month', $post['wip_month']);
            $cek_data_mps = $this->db->get('generate_mps')->num_rows();

            $postFinal = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                // "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month'],
                "pp" => $post['pp'],
                "p1" => $post['p1'],
                "p2" => $post['p2'],
                "p3" => $post['p3'],
                "fg" => $post['fg'],
                "os_mpp" => $post['os_mpp'],
                "os_so" => $post['os_so'],
                "total_stock" => $post['total_stock'],
                "balance" => $balance
            );

            if ($cek_data_mps > 0) {
                $send   = $this->db->update('generate_mps', $postFinal, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    // "customer_id" => $post['customer_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "wip_month" => $post['wip_month']
                ]);
                
                echo json_encode([
                    'theme' => 'success',
                    'title' => 'Updated',
                    'message' => 'Update data Successfully.'
                ]);
            } else {
                $send = $this->crud->createNotLog('generate_mps', $postFinal);
                
                echo json_encode([
                    'theme' => 'success',
                    'title' => 'Created',
                    'message' => 'Create data Successfully.'
                ]);
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_mps.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function monthName($id)
    {
        if ($id == "01") {
            return "JANUARY";
        } elseif ($id == "02") {
            return "FEBRUARY";
        } elseif ($id == "03") {
            return "MARCH";
        } elseif ($id == "04") {
            return "APRIL";
        } elseif ($id == "05") {
            return "MAY";
        } elseif ($id == "06") {
            return "JUNE";
        } elseif ($id == "07") {
            return "JULY";
        } elseif ($id == "08") {
            return "AUGUST";
        } elseif ($id == "09") {
            return "SEPTEMBER";
        } elseif ($id == "10") {
            return "OCTOBER";
        } elseif ($id == "11") {
            return "NOVEMBER";
        } elseif ($id == "12") {
            return "DECEMBER";
        }
    }

    public function ltpp_no($p_month, $p_year)
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        $datenow    = $p_year. $p_month;
        $sqlGetID   = $this->dummy->query("SELECT max(ltpp_doc) as kode FROM ltpp_header 
            WHERE ltpp_doc like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }

        return $datenow . "/LTPP/" . $autoID;
    }

    public function push_data_check(){
    	//Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Line No
        $this->db->select('*');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $this->db->where('deleted', 0);
        $records = $this->db->get()->result_array();

        if(count($records) > 0){
        	echo json_encode(array("title" => "Success", "message" => "Data Already", "theme" => "success"));
        }else{
        	echo json_encode(array("title" => "Not Ready", "message" => "Data MPS Not Found, Please Generate First", "theme" => "error"));
        }
    }

    // public function push_data_header(){
    //     $this->dummy = $this->load->database('dummy', TRUE);

    //     if($this->input->get()){
    //         //Filter Data
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_revision = base64_decode($this->input->get('filter_revision'));

    //         //Perhitungan HKW
    //         $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
    //         $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

    //         $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
    //         $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

    //         $no = 1;
    //         while ($monthStart < $monthEnd) {
    //             $start = strtotime(date('Y-m-01', $monthStart));
    //             $finish = strtotime(date('Y-m-t', $monthStart));

    //             //HKW 1
    //             $hkw = 0;
    //             for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
    //                 $working_date = date('Y-m-d', $z);

    //                 $this->db->select('remarks');
    //                 $this->db->from('calendars');
    //                 $this->db->where('working_date', $working_date);
    //                 $holiday = $this->db->get()->row();

    //                 // if (date('w', $z) !== '0' && date('w', $z) !== '6') {
    //                     if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                         $hkw += 0;
    //                     } else {
    //                         $hkw += 1;
    //                     }
    //                 // } else {
    //                 //     $hkw += 0;
    //                 // }
    //             }

    //             $arrHkw[] = array(
    //                 "hkw_".$no => $hkw
    //             );

    //             $no++;
    //             $monthStart = strtotime("+1 month", $monthStart);
    //         }

    //         $this->dummy->select('*');
    //         $this->dummy->from("ltpp_header");
    //         $this->dummy->where("period", $filter_year . $filter_month);
    //         $this->dummy->where("rev", $filter_revision);
    //         $ltpp_header = $this->dummy->get()->result_array();

    //         $arrData = array(
    //             "ltpp_doc" => $this->ltpp_no($filter_month, $filter_year),
    //             "period" => $filter_year . $filter_month,
    //             "date_period" => $filter_year ."-".$filter_month."-01",
    //             "lt" => "8",
    //             "hkw_1" => $arrHkw[0]['hkw_1'],
    //             "hkw_2" => $arrHkw[1]['hkw_2'],
    //             "hkw_3" => $arrHkw[2]['hkw_3'],
    //             "hkw_4" => $arrHkw[3]['hkw_4'],
    //             "hkw_5" => $arrHkw[4]['hkw_5'],
    //             "hkw_6" => $arrHkw[5]['hkw_6'],
    //             "rev" => $filter_revision,
    //             "fc_m4" => "0",
    //             "upload_user" => $this->session->username,
    //             "upload_time" => date("Y-m-d H:i:s"),
    //             "status" => "",
    //             "approval_sts" => "",
    //             "remark" => "",
    //         );

    //         if(count($ltpp_header) == 0){
    //             $this->dummy->insert("ltpp_header", $arrData);
    //             die(json_encode($arrData));
    //         }else{
    //             die(json_encode($ltpp_header));
    //         }
    //     }
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        $cutoff = $this->crud->read("generate_mps", [], ["p_month" => $filter_month, "p_year" => $filter_year,"revision" => $filter_revision]);


        //Select Customer
        // $this->db->select('a.*, b.name as customer_name');
        // $this->db->from('generate_mps a');
        // $this->db->join('customers b', 'a.customer_id = b.id');
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('a.p_month', $filter_month);
        //     $this->db->where('a.p_year', $filter_year);
        // }
        // $this->db->like('a.customer_id', $filter_customer);
        // $this->db->like('a.revision', $filter_revision);
        // $this->db->like('a.item_fg_id', $filter_product_no);
        // $this->db->group_by('a.customer_id');
        // $this->db->order_by('b.name', 'asc');
        // $customers = $this->db->get()->result_array();

        $header = "";
        $headerDetails = "";

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));
        $monthNameStart = date('F Y', $monthStart);

        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('F Y', $monthStart);

            // if ($no == 1) {
                $xbar = '<th style="text-align:center;">SO</th>';
                $colspan = '8';
            // } else {
            //     $xbar = "";
            //     $colspan = '8';
            // }

            $header .= '<th style="text-align:center;" colspan="' . $colspan . '" width="50">' . strtoupper($monthName) . '</th>';
            $headerDetails .= ' <th style="text-align:center;">BEGIN <br> BALANCE</th>
                                    <th style="text-align:center;">ITO</th>
                                    ' . $xbar . '
                                    <th style="text-align:center;">FC</th>
                                    <th style="text-align:center;">DELIVERY <br> RATE</th>
                                    <th style="text-align:center;">%SAFETY <br> STOCK</th>
                                    <th style="text-align:center;">SAFETY <br> STOCK</th>
                                    <th style="text-align:center;">NEED</th>
                                    <th style="text-align:center;">PROD <br> PLAN</th>';
            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <div style="width:2500px;">
        <table style="width: 100%;">
            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                <img src="' . $config->logo . '" width="30">
            </td>
            <td style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>PPC DEPARTEMENT</small>
            </td>
        </table>
        <center>
            <b style="font-size:18px;">GENERATE MPS</b>
        </center>
        <p style="font-size:12px; margin:0;">PERIOD ' . $this->monthName($filter_month) . ' ' . $filter_year . '</p>
        <p style="font-size:12px; margin:0;">REVISION ' . $filter_revision . '</p>
        <p style="font-size:12px; margin:0;">CUTOFF ' . @$cutoff->cutoff . '</p>
        <p style="font-size:12px; margin:0;">GENERATE DATE ' . date('Y-m-d', strtotime($cutoff->created_date)) . '</p>
        <p style="font-size:12px; margin:0;">PRINT DATE ' . date("d M Y H:m:s") . '</p>
        <p style="font-size:12px; margin:0;">PRINT BY ' . $this->session->username . '</p>
        <br>
        <table id="customers" border="1">';
        $html .= '<tr>
                <th style="text-align:center;" rowspan="2" width="20">No</th>
                <th style="text-align:center;" rowspan="2" width="100">PRODUCT NO</th>
                <th style="text-align:center;" rowspan="2" width="150">PRODUCT CUSTOMER</th>
                <th style="text-align:center;" rowspan="2" width="100">CUSTOMER</th>
                <th style="text-align:center;" rowspan="2" width="100">DESCRIPTION</th>
                <th style="text-align:center;" rowspan="2" width="100">TYPE</th>
                <th style="text-align:center;" rowspan="2" width="100">MPQ</th>
                <th style="text-align:center;" colspan="4" width="100">WIP</th>
                <th style="text-align:center;" rowspan="2" width="50">FG</th>
                <th style="text-align:center;" rowspan="2" width="50">OST<br>MPP</th>
                <th style="text-align:center;" rowspan="2" width="50">TOTAL<br>STOCK</th>
                <th style="text-align:center;" rowspan="2" width="50">OST<br>SO</th>';
        $html .= $header;
        $html .= '
                <th style="text-align:center;" rowspan="2" width="50">BAL</th>
            </tr>
            <tr>
                <th style="text-align:center;" width="50">INJ</th>
                <th style="text-align:center;" width="50">ON HOLD</th>
                <th style="text-align:center;" width="50">ASSEMBLY</th>
                <th style="text-align:center;" width="50">SUBCONT</th>';
        $html .= $headerDetails;
        $html .= '</tr>
            <tr>';
               

        // foreach ($customers as $customer) {
        //     if ($customer['customer_name'] == "") {
        //         $customer_name = "No Customer";
        //     } else {
        //         $customer_name = $customer['customer_name'];
        //     }

        //     $html .= '  <tr>
        //                     <th colspan="100" style="text-align:left;"><b>' . $customer_name . '</b></th>
        //                 </tr>';
            //Select Full
            $this->db->select('a.*, e.number as item_fg_number, e.name as item_fg_name, e.number_customer, e.mpq, e.type,
            GROUP_CONCAT(DISTINCT c.number ORDER BY c.number ASC SEPARATOR ", ") as all_customers');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_details b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id');
            $this->db->join('customer_items d', 'a.item_fg_id = d.item_fg_id', 'left');
            $this->db->join('customers c', 'd.customer_id = c.id', 'left');
            $this->db->join('item_fg e', 'a.item_fg_id = e.id');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            // $this->db->where('a.customer_id', $customer['customer_id']);
            $this->db->where("(a.pp > 0 or a.p1 > 0 or a.p2 > 0 or a.p3 > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_so > 0 or a.total_stock > 0 or a.balance > 0 or 
                b.begin_balance > 0 or b.ito > 0 or b.forecast > 0 or b.delivery_rate > 0 or b.safety_stock > 0 or b.prod_plan > 0)");
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.item_fg_id', $filter_product_no);
            $this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();

            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                            <td>' . $data['all_customers'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['mpq'] . '</td>
                            <td style="text-align:right;">' . $data['pp'] . '</td>
                            <td style="text-align:right;">' . $data['p2'] . '</td>
                            <td style="text-align:right;">' . $data['p1'] . '</td>
                            <td style="text-align:right;">' . $data['p3'] . '</td>
                            <td style="text-align:right;">' . $data['fg'] . '</td>
                            <td style="text-align:right;">' . $data['os_mpp'] . '</td>
                            <td style="text-align:right;">' . $data['total_stock'] . '</td>
                            <td style="text-align:right;">' . $data['os_so'] . '</td>';

                $this->db->select('a.*, COALESCE(j.safety_stock, 0) as safety_stock_pesen');
                $this->db->from('generate_mps_details a');
                $this->db->join("(SELECT item_fg_id, MAX(safety_stock) as safety_stock FROM safety_stocks GROUP BY item_fg_id) j ", 'a.item_fg_id = j.item_fg_id', 'left');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                // $this->db->where('a.customer_id', $data['customer_id']);
                $this->db->where('a.item_fg_id', $data['item_fg_id']);
                $this->db->where('a.deleted', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                foreach ($details2 as $detail2) {
                    // if ($nodetail == 1) {
                        // if ($detail2['qty'] == "") {
                        //     $xbarQty = 0;
                        // } else {
                        //     $xbarQty = $detail2['qty'];
                        // }

                        // $xbar2 = '<td style="text-align:right;">' . $xbarQty . '</td>';
                    // } else {
                    //     $xbar2 = "";
                    // }

                    $html .= '  <td style="text-align:right;">' . $detail2['begin_balance'] . '</td>
                                <td style="text-align:right;">' . $detail2['ito'] . '</td>
                                <td style="text-align:right;">' . $detail2['so'] . '</td>
                                <td style="text-align:right;">' . $detail2['forecast'] . '</td>
                                <td style="text-align:right;">' . $detail2['delivery_rate'] . '</td>
                                <td style="text-align:right;">' . $detail2['safety_stock_pesen'] . '</td>
                                <td style="text-align:right;">' . $detail2['safety_stock'] . '</td>
                                <td style="text-align:right;">' . $detail2['need'] . '</td>
                                <td style="text-align:right;">' . $detail2['prod_plan'] . '</td>';
                    $nodetail++;
                }
                $html .= '<td style="text-align:right;">' . $data['balance'] . '</td>
            </tr>';
                $no++;
            }
        // }

        $html .= '</table></div></body></html>';
        echo $html;
    }
}
