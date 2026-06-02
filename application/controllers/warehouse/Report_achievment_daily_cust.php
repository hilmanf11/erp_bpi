<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_achievment_daily_cust extends CI_Controller
{
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
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_achievment_daily_cust');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomer()
    {
        $send = $this->crud->query("SELECT b.id, b.number, b.name 
            FROM sales_order_deliveries a 
            JOIN customers b ON a.customer_id = b.id 
            WHERE a.status = 0
            GROUP BY a.customer_id ORDER BY b.name ASC");
        echo json_encode($send);
    }

    public function readTransdate($customer_id)
    {  
        $customer_id = base64_decode($customer_id);
        $send = $this->crud->query("SELECT a.trans_date
            FROM sales_order_deliveries a 
            JOIN customers b ON a.customer_id = b.id
            WHERE b.id = '$customer_id'
            AND a.status = 0
            GROUP BY a.trans_date ORDER BY a.trans_date DESC");
        echo json_encode($send);
    }

    public function readPlant($customer_id, $trans_date)
    {  
        $customer_id = base64_decode($customer_id);
        $trans_date = base64_decode($trans_date);
        $send = $this->crud->query("SELECT plant
            FROM sales_order_deliveries
            WHERE customer_id = '$customer_id'
            AND trans_date = '$trans_date'
            AND `status` = 0
            GROUP BY plant ORDER BY plant ASC");
        echo json_encode($send);
    }

    public function readMonth()
    {
        $months = array(
            '01' => 'January', '02' => 'February', '03' => 'March', 
            '04' => 'April', '05' => 'May', '06' => 'June', 
            '07' => 'July', '08' => 'August', '09' => 'September', 
            '10' => 'October', '11' => 'November', '12' => 'December'
        );

        $arr = []; // Inisialisasi awal
        foreach ($months as $key => $value) {
            $arr[] = array("number" => $key, "name" => $value);
        }

        echo json_encode($arr);
        exit;
    }

    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));

        $arr = []; // Inisialisasi awal
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array("number" => "$i");
        }

        echo json_encode($arr);
        exit;
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

            $this->db->select("a.*, 
                b.number as item_number,
                b.name as item_name,
                x.number as division,
                c.name as customer_name,
                COALESCE(d.qty_so,0) as qty_so,
                COALESCE(e.qty_dn,0) as qty_dn,
                SUM(a.qty) as qty_ds");
            $this->db->from('sales_order_deliveries a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('divisions x', 'b.division_id = x.id');
            $this->db->join(
                "(SELECT customer_id, item_fg_id, SUM(qty) as qty_so 
                FROM sales_orders 
                WHERE delivery_date between '{$filter_from}' and '{$filter_to}' 
                GROUP BY customer_id, item_fg_id) d",
                "a.customer_id = d.customer_id AND a.item_fg_id = d.item_fg_id",
                "left"
            );
            $this->db->join(
                "(SELECT customer_id, item_fg_id, SUM(qty) as qty_dn 
                FROM delivery_notes 
                WHERE delivery_note_date between '{$filter_from}' and '{$filter_to}' 
                GROUP BY customer_id, item_fg_id) e",
                "a.customer_id = e.customer_id AND a.item_fg_id = e.item_fg_id",
                "left"
            );

            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);

            if ($filter_customer != "") {
                $this->db->where('a.customer_id', $filter_customer);
            }
            if ($filter_item_fg != "") {
                $this->db->where('b.id', $filter_item_fg);
            }

            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.customer_id');
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('b.number', 'asc');

            // Total Data
            $totalRows = $this->db->count_all_results('', false);
            // Limit 1 - 10
            $this->db->limit($rows, $offset);
            // Get Data Array
            $records = $this->db->get()->result_array();


            $dataFinal = array();
            foreach ($records as $record) {
                $arrSales = array();
                $arrDelivery = array();
                $arrBalance = array();

                $typesArr = ["PLAN", "ACTUAL", "BALANCE"];
                foreach ($typesArr as $type) {
                    $currentDate = $filter_from;
                    // $arr = array();

                    $total_qty = 0;
                    $total_del = 0;

                    $day = 1;
                    // while (strtotime($currentDate) <= strtotime($filter_to)) {
                    //     $trans_date = date("Y-m-d", strtotime($currentDate));
                
                    //     $this->db->select('*');
                    //     $this->db->from('sales_order_deliveries');
                    //     $this->db->where('customer_id', $record['customer_id']);
                    //     $this->db->where('item_fg_id', $record['item_fg_id']);
                    //     $this->db->where('trans_date', $trans_date);
                    //     $row = $this->db->get()->row();
                
                    //     $this->db->select('*');
                    //     $this->db->from('delivery_notes');
                    //     $this->db->where('customer_id', $record['customer_id']);
                    //     $this->db->where('item_fg_id', $record['item_fg_id']);
                    //     $this->db->where('delivery_note_date', $trans_date);
                    //     $row2 = $this->db->get()->row();
                
                    //     $actual_qty = empty(@$row->qty) ? 0 : @$row->qty;
                    //     $delivery_qty = empty(@$row2->qty) ? 0 : @$row2->qty;

                    //     $arrSales = array_merge($arrSales, [
                    //         "qty_".$day => $actual_qty,
                    //     ]);

                    //     $arrDelivery = array_merge($arrDelivery, [
                    //         "qty_".$day => $delivery_qty,
                    //     ]);

                    //     $arrBalance = array_merge($arrBalance, [
                    //         "qty_".$day => number_format($actual_qty - $delivery_qty, 2),
                    //     ]);
                
                    //     $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
                    //     $day++;
                    //     $total_qty += $actual_qty;
                    //     $total_del += $delivery_qty;
                    // }

                    $prevBalance = 0; // Inisialisasi balance awal

                    while (strtotime($currentDate) <= strtotime($filter_to)) {
                        $trans_date = date("Y-m-d", strtotime($currentDate));

                        $this->db->select('SUM(qty) as qty');
                        $this->db->from('sales_order_deliveries');
                        $this->db->where('customer_id', $record['customer_id']);
                        $this->db->where('item_fg_id', $record['item_fg_id']);
                        $this->db->where('trans_date', $trans_date);
                        $row = $this->db->get()->row();

                        $this->db->select('SUM(qty) as qty');
                        $this->db->from('delivery_notes');
                        $this->db->where('customer_id', $record['customer_id']);
                        $this->db->where('item_fg_id', $record['item_fg_id']);
                        $this->db->where('delivery_note_date', $trans_date);
                        $row2 = $this->db->get()->row();

                        $actual_qty = empty(@$row->qty) ? 0 : @$row->qty;
                        $delivery_qty = empty(@$row2->qty) ? 0 : @$row2->qty;

                        // Balance untuk hari ini dihitung dengan menambahkan prevBalance
                        $currentBalance = $prevBalance + $delivery_qty - $actual_qty;

                        $arrSales = array_merge($arrSales, [
                            "qty_" . $day => $actual_qty,
                        ]);

                        $arrDelivery = array_merge($arrDelivery, [
                            "qty_" . $day => $delivery_qty,
                        ]);

                        $arrBalance = array_merge($arrBalance, [
                            "qty_" . $day => number_format($currentBalance, 2),
                        ]);

                        $prevBalance = $currentBalance; // Simpan balance saat ini untuk digunakan pada hari berikutnya

                        $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
                        $day++;
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                    }


                    if($type == "PLAN"){
                        $arr = array_merge($arrSales, ["total_qty" => $total_qty]);
                    }elseif($type == "ACTUAL"){
                        $arr = array_merge($arrDelivery, ["total_qty" => $total_del]);
                    }else{
                        $arr = array_merge($arrBalance, ["total_qty" => ($total_qty - $total_del)]);
                    }

                    $dataFinal[] = array_merge($arr, [
                        "id" => $record['id'],
                        "item_fg_id" => $record['item_fg_id'],
                        "customer_id" => $record['customer_id'],
                        "customer_name" => $record['customer_name'],
                        "item_number" => $record['item_number'],
                        "division" => $record['division'],
                        "item_name" => $record['item_name'],
                        "qty_so" => $record['qty_so'],
                        "qty_ds" => $record['qty_ds'],
                        "qty_dn" => $record['qty_dn'],
                        "ost_so" => $record['qty_so'] - $record['qty_dn'],
                        // "total_qty" => $total_qty,
                        "type" => $type,
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date'],
                        "status" => $record['status'],
                    ]);
                }
            }

            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_deliveries_$format.xls");
        }

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Filter Data
        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

        // Fetch Data
        $this->db->select("a.*, 
            b.number as item_number,
            b.name as item_name,
            c.name as customer_name,
            x.number as division,
            COALESCE(d.qty_so,0) as qty_so,
            COALESCE(e.qty_dn,0) as qty_dn,
            (COALESCE(d.qty_so,0) - COALESCE(e.qty_dn,0)) as ost_so,
            SUM(a.qty) as qty_ds");
        $this->db->from('sales_order_deliveries a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->join('divisions x', 'b.division_id = x.id');
        $this->db->join(
            "(SELECT customer_id, item_fg_id, SUM(qty) as qty_so 
            FROM sales_orders 
            WHERE delivery_date BETWEEN '{$filter_from}' AND '{$filter_to}' 
            GROUP BY customer_id, item_fg_id) d",
            "a.customer_id = d.customer_id AND a.item_fg_id = d.item_fg_id",
            "left"
        );
        $this->db->join(
            "(SELECT customer_id, item_fg_id, SUM(qty) as qty_dn 
            FROM delivery_notes 
            WHERE delivery_note_date BETWEEN '{$filter_from}' AND '{$filter_to}' 
            GROUP BY customer_id, item_fg_id, delivery_note_date) e",
            "a.customer_id = e.customer_id AND a.item_fg_id = e.item_fg_id",
            "left"
        );

        $this->db->where('a.trans_date >=', $filter_from);
        $this->db->where('a.trans_date <=', $filter_to);

        if ($filter_customer != "") {
            $this->db->where('a.customer_id', $filter_customer);
        }
        if ($filter_item_fg != "") {
            $this->db->where('b.id', $filter_item_fg);
        }

        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.customer_id');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        // Setting Header
        $styles = "";
        $date = '';
        $content = '';
        $no = 1;
        $colspan = 0;

        // Iterasi Tanggal Berdasarkan filter_from dan filter_to
        $currentDate = $filter_from;
        while (strtotime($currentDate) <= strtotime($filter_to)) {
            $working_date = date("Y-m-d", strtotime($currentDate));
            $working_date2 = date("d F", strtotime($currentDate));

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($currentDate)) !== '0' && date('w', strtotime($currentDate)) !== '6') {
                if (!empty($holiday->remarks)) {
                    $styles = 'background:#FFD974;';
                } else {
                    $styles = "";
                }
            } else {
                $styles = 'background:#FFD974;';
            }

            // Setting Header
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">' . $working_date2 . '</th>';

            $colspan++;
            $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
        }


        foreach ($records as $record) {
            $typesArr = ["PLAN", "ACTUAL", "BALANCE"];
            foreach ($typesArr as $type) {
                $currentDate = $filter_from;
                $isi = "";
                $total_qty = 0;
                $total_del = 0;
                $total_balance = 0;
                $day = 1;

                $prevBalance = 0;

                while (strtotime($currentDate) <= strtotime($filter_to)) {
                    $trans_date = date("Y-m-d", strtotime($currentDate));
        
                    // Ambil data actual_qty dari sales_order_deliveries
                    $this->db->reset_query();
                    $this->db->select('SUM(qty) as qty');
                    $this->db->from('sales_order_deliveries');
                    $this->db->where('customer_id', $record['customer_id']);
                    $this->db->where('item_fg_id', $record['item_fg_id']);
                    $this->db->where('DATE(trans_date)', $trans_date);
                    $row = $this->db->get()->row();
                    $actual_qty = (!empty($row->qty)) ? $row->qty : 0;
        
                    // Ambil data delivery_qty dari delivery_notes
                    $this->db->reset_query();
                    $this->db->select('SUM(qty) as qty');
                    $this->db->from('delivery_notes');
                    $this->db->where('customer_id', $record['customer_id']);
                    $this->db->where('item_fg_id', $record['item_fg_id']);
                    $this->db->where('delivery_note_date', $trans_date);
                    $row2 = $this->db->get()->row();
                    $delivery_qty = !empty($row2) ? $row2->qty : 0;
        
                    // Hitung balance
                    // $balance = $actual_qty - $delivery_qty;
                    $currentBalance = $prevBalance + $delivery_qty - $actual_qty;
        
                    // Isi data berdasarkan tipe
                    if ($type == "PLAN") {
                        $isi .= "<td style='text-align:right;'>" . number_format($actual_qty, 2) . "</td>";
                        $total_qty += $actual_qty;
                    } elseif ($type == "ACTUAL") {
                        $isi .= "<td style='text-align:right;'>" . number_format($delivery_qty, 2) . "</td>";
                        $total_del += $delivery_qty;
                    } elseif ($type == "BALANCE") {
                        $isi .= "<td style='text-align:right;'>" . number_format($currentBalance, 2) . "</td>";
                        $total_balance += $currentBalance;
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                    }
        
                    $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
                    $prevBalance = $currentBalance;
                }

        
                // Tambahkan total di akhir baris

                if ($type == "PLAN") {
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . number_format($total_qty, 2) . "</td>";
                } elseif ($type == "ACTUAL") {
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . number_format($total_del, 2) . "</td>";
                } elseif ($type == "BALANCE") {
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . number_format(($total_del - $total_qty), 2) . "</td>";
                }
        
                // Tambahkan baris ke konten tabel
                $content .= "<tr>
                                <td>" . $no . "</td>
                                <td style='mso-number-format:\\@;'>" . $record['item_number'] . "</td>
                                <td>" . $record['item_name'] . "</td>
                                <td>" . $record['division'] . "</td>
                                <td>" . $record['customer_name'] . "</td>
                                <td>" . number_format($record['qty_so'], 2) . "</td>
                                <td>" . number_format($record['qty_dn'], 2) . "</td>
                                <td>" . number_format($record['qty_ds'], 2) . "</td>
                                <td>" . number_format($record['ost_so'], 2) . "</td>
                                <td style='text-align:left; font-weight:bold;'>" . $type . "</td>" . $isi . "</tr>";
            }
            $no++;
        }
        

        $html = '<html><head><title>Print Data</title></head><body>
            <table id="customers" border="1" style="width:100%;">
                <tr>
                    <th style="text-align:center;" rowspan="2">NO</th>
                    <th style="text-align:center;" rowspan="2">Product No</th>
                    <th style="text-align:center;" rowspan="2">Product Name</th>
                    <th style="text-align:center;" rowspan="2">Division</th>
                    <th style="text-align:center;" rowspan="2">Customer</th>
                    <th style="text-align:center;" rowspan="2">Qty SO</th>
                    <th style="text-align:center;" rowspan="2">Delivered</th>
                    <th style="text-align:center;" rowspan="2">Schedule</th>
                    <th style="text-align:center;" rowspan="2">OST SO</th>
                    <th style="text-align:center;" rowspan="2">Type</th>
                    <th style="text-align:center;" colspan="' . $colspan . '">Report Plan VS Actual</th>
                </tr>' . $date . $content . '</table></body></html>';

        echo $html;
    }

}
