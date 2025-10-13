<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_invoices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/purchase_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $number = base64_decode($number);
        $this->db->select("a.*, a.item_no as item_number, a.item_name, a.uom, a.currency, a.account_number, 
            c.account_name, 'IDR' as currency_local, 
            (CASE WHEN e.middle != '' THEN (e.middle * a.total) ELSE a.total END) as total_local");
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_categories d', 'a.category_id = d.id');
        $this->db->join('account_coa c', 'a.account_number = c.account_number', 'left');
        $this->db->join('exchange_rates e', "e.start_date = DATE_FORMAT((a.trans_date - INTERVAL '1' MONTH), '%Y-%m-01') and e.currency_from = a.currency", 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->order_by('a.por_no', 'asc');
        $this->db->order_by('a.item_no', 'asc');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_number = $this->input->get('item_number');
        $item_rm_id = $this->input->get('item_rm_id');
        $item_family_id = $this->input->get('item_family_id');

        $this->db->select('b.*, c.number as item_number, a.mpq, a.moq, a.price, a.share_order, a.uom_default');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        // $this->db->where("c.number", $item_number);
        $this->db->where("c.id", $item_rm_id);
        // $this->db->like("d.id", $item_family_id);
        // $this->db->like("b.name", $post);
        $this->db->group_by('b.number');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readSupplierss()
    {
        $q = $this->input->post('q');  // Mengambil parameter pencarian dari POST
        $item_category_id = $this->input->get('item_category_id');

        // Mengamankan parameter input untuk mencegah SQL Injection
        $q = $this->db->escape_like_str($q);

        $sql = "SELECT DISTINCT b.id, b.name, b.number, b.payment_term, b.vat, b.vat_status
                FROM supplier_items a 
                JOIN suppliers b ON a.supplier_id = b.id 
                JOIN item_rm c ON a.item_rm_id = c.id
                JOIN item_categories d ON c.item_category_id = d.id
                WHERE a.deleted = 0 
                AND d.id = ? 
                AND (b.name LIKE ? OR b.number LIKE ?)
                ORDER BY b.name ASC";

        // Menggunakan query builder untuk parameterized query
        $records = $this->db->query($sql, array($item_category_id, "%$q%", "%$q%"))->result_array();

        echo json_encode($records);
    }

    public function readSupplierx()
    {
        $post = $this->input->post('q') ? $this->input->post('q') : "";
        $post = $this->db->escape_like_str($post);
        $sql = "SELECT DISTINCT id, name, number, payment_term, vat, vat_status
                FROM suppliers
                WHERE `status` = 0 
                AND `name` LIKE ? 
                GROUP BY `number` 
                ORDER BY `name` ASC";
    
        $records = $this->db->query($sql, array("%$post%"))->result_array();
        echo json_encode($records);
    }    

    public function readJournalType()
    {
        $id = $this->input->post('id');
        $id   = base64_decode($id);
        
        $journalData = null;

        if (!empty($id)) {
            $query = $this->db->query('SELECT id, name FROM journal_types WHERE id = ?', [$id]);
            if ($query->num_rows() > 0) {
                $journalData = $query->row_array();
            }
        }
        echo json_encode($journalData);
    }

    public function readJournal($journal_type_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $journal_type_id = base64_decode($journal_type_id);

        $journals = $this->crud->query("SELECT a.*, a.flag, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.account_number LIKE '%$post%' and a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");

        $arr = [];
        if (!empty($journals)) {
        foreach ($journals as $journal) {
            $arr[] = array(
                "account_number" => $journal->account_number,
                "account_name" => $journal->account_name,
                "debit" => "0.00",
                "credit" => "0.00",
                "flag" => $journal->flag,
            );
        }
        }

        echo json_encode($arr);
    }

    public function readJournals($number)
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("purchase_invoice_journals", [], ["number" => $number], "", "flag", "asc");
        echo json_encode($reads);
    }

    // public function calculateJournal()
    // {
    //     $journals = json_decode(file_get_contents("json/purchase_invoice_journals.json"), true);
    //     if (count($journals) > 0) {
    //         foreach ($journals as $journal) {
    //             $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);

    //             $total_debit = 0;
    //             $total_credit = 0;

    //             foreach ($jsonDatas as $jsonData) {
    //                 if ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "DEBIT") {
    //                     $total_debit += $jsonData['total'];
    //                 } elseif ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "CREDIT") {
    //                     $total_credit += $jsonData['total'];
    //                 }
    //             }

    //             if ($journal['debit'] > 0) {
    //                 $total_debit = $journal['debit'];
    //             }

    //             if ($journal['credit'] > 0) {
    //                 $total_credit = $journal['credit'];
    //             }

    //             $arr[] = array(
    //                 "account_number" => $journal['account_number'],
    //                 "account_name" => $journal['account_name'],
    //                 "debit" => round($total_debit, 2),
    //                 "credit" => round($total_credit, 2),
    //                 "flag" => $journal['flag'],
    //             );
    //         }
    //     } else {
    //         $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);
    //         $total = 0;
    //         $flag = 1;
    //         $mergedData = array();
    //         foreach ($jsonDatas as $jsonData) {
    //             $account_number = $jsonData["account_number"];
    //             $account_name = $jsonData["account_name"];
    //             $account_type = $jsonData["account_type"];
    //             $total = $jsonData["total"];

    //             if (isset($mergedData[$account_number])) {
    //                 // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
    //                 if ($jsonData['account_type'] == "DEBIT") {
    //                     $mergedData[$account_number]["debit"] += $total;
    //                 } elseif ($jsonData['account_type'] == "CREDIT") {
    //                     $mergedData[$account_number]["credit"] += $total;
    //                 }
    //             } else {
    //                 // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
    //                 if ($jsonData['account_type'] == "DEBIT") {
    //                     $mergedData[$account_number] = array(
    //                         "account_number" => $account_number,
    //                         "account_name" => $account_name,
    //                         "account_type" => $account_type,
    //                         "debit" => $total,
    //                         "flag" => $flag,
    //                     );
    //                 } elseif ($jsonData['account_type'] == "CREDIT") {
    //                     $mergedData[$account_number] = array(
    //                         "account_number" => $account_number,
    //                         "account_name" => $account_name,
    //                         "account_type" => $account_type,
    //                         "credit" => $total,
    //                         "flag" => $flag,
    //                     );
    //                 }
    //             }

    //             $flag++;
    //         }

    //         // Ubah hasil penggabungan menjadi indeks numerik jika diperlukan
    //         $arr = array_values($mergedData);
    //     }

    //     echo json_encode($arr);
    // }

    public function calculateJournal()
    {
        $journals = json_decode(file_get_contents("json/purchase_invoice_journals.json"), true);
        $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);
        $arr = [];

        // Jika purchase_invoice_journals.json TIDAK KOSONG
        if (!empty($journals)) {
            // Mengubah jsonDatas menjadi peta (map) agar mudah diakses
            $aggregatedData = [];
            foreach ($jsonDatas as $data) {
                $account_number = $data['account_number'];
                $account_type = $data['account_type'];
                
                if (!isset($aggregatedData[$account_number])) {
                    $aggregatedData[$account_number] = [
                        'debit' => 0,
                        'credit' => 0,
                        'local_debit' => 0,
                        'local_credit' => 0
                    ];
                }
                
                if ($account_type == "DEBIT") {
                    $aggregatedData[$account_number]['debit'] += $data['total'];
                    $aggregatedData[$account_number]['local_debit'] += $data['total_idr'] ?? $data['total'];
                } elseif ($account_type == "CREDIT") {
                    $aggregatedData[$account_number]['credit'] += $data['total'];
                    $aggregatedData[$account_number]['local_credit'] += $data['total_idr'] ?? $data['total'];
                }
            }

            // Loop melalui jurnal dan gabungkan dengan data yang sudah dihitung
            foreach ($journals as $journal) {
                $account_number = $journal['account_number'];
                $total_debit = $aggregatedData[$account_number]['debit'] ?? 0;
                $total_credit = $aggregatedData[$account_number]['credit'] ?? 0;
                $local_debit = $aggregatedData[$account_number]['local_debit'] ?? 0;
                $local_credit = $aggregatedData[$account_number]['local_credit'] ?? 0;

                $final_debit = max($total_debit, $journal['debit'] ?? 0);
                $final_credit = max($total_credit, $journal['credit'] ?? 0);
                $final_local_debit = max($local_debit, $journal['local_debit'] ?? 0);
                $final_local_credit = max($local_credit, $journal['local_credit'] ?? 0);
                
                $arr[] = [
                    "account_number" => $journal['account_number'],
                    "account_name" => $journal['account_name'],
                    "debit" => round($final_debit, 2),
                    "credit" => round($final_credit, 2),
                    "local_debit" => round($final_local_debit, 2),
                    "local_credit" => round($final_local_credit, 2),
                    "flag" => $journal['flag'],
                ];
            }
        } else {
            // Jika purchase_invoice_journals.json kosong
            $mergedData = [];
            foreach ($jsonDatas as $jsonData) {
                $account_number = $jsonData["account_number"];
                $account_name = $jsonData["account_name"];
                $account_type = $jsonData["account_type"];
                
                if (!isset($mergedData[$account_number])) {
                    $mergedData[$account_number] = [
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "debit" => 0,
                        "credit" => 0,
                        "local_debit" => 0,
                        "local_credit" => 0,
                        "flag" => 0,
                    ];
                }

                if ($account_type == "DEBIT") {
                    $mergedData[$account_number]["debit"] += $jsonData["total"];
                    $mergedData[$account_number]["local_debit"] += $jsonData["total_idr"] ?? $jsonData["total"];
                } elseif ($account_type == "CREDIT") {
                    $mergedData[$account_number]["credit"] += $jsonData["total"];
                    $mergedData[$account_number]["local_credit"] += $jsonData["total_idr"] ?? $jsonData["total"];
                }
            }
            
            // Account VAT IN otomatis muncul di Journal List Ketika Add To Journal walau tidak ada setup journal. Posisinya di DEBIT.
            $vatIn = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '170.170.00')->get()->row_array();
            if ($vatIn) {
                $mergedData[$vatIn['account_number']] = [
                    "account_number" => $vatIn['account_number'],
                    "account_name" => $vatIn['account_name'],
                    "debit" => 0,
                    "credit" => 0,
                    "local_debit" => 0,
                    "local_credit" => 0,
                    "flag" => 0,
                ];
            }

            $thirdParties = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '210.120.00')->get()->row_array();
            if ($thirdParties) {
                $mergedData[$thirdParties['account_number']] = [
                    "account_number" => $thirdParties['account_number'],
                    "account_name" => $thirdParties['account_name'],
                    "debit" => 0,
                    "credit" => 0,
                    "local_debit" => 0,
                    "local_credit" => 0,
                    "flag" => 0,
                ];
            }

            // Mengisi flag setelah penggabungan data selesai
            $flag = 1;
            foreach ($mergedData as &$data) {
                $data['flag'] = $flag++;
            }
            unset($data);

            $arr = array_values($mergedData);
        }
        
        echo json_encode($arr);
    }

    public function calculateJournal_backup()
    {
        $journals = json_decode(file_get_contents("json/purchase_invoice_journals.json"), true);
        if (count($journals) > 0) {
            foreach ($journals as $journal) {
                $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);

                $total_debit = 0;
                $total_credit = 0;
                $local_debit = 0;
                $local_credit = 0;

                foreach ($jsonDatas as $jsonData) {
                    if ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "DEBIT") {
                        $total_debit += $jsonData['total'];
                        // $local_debit += $jsonData['total_idr'];
                    } elseif ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "CREDIT") {
                        $total_credit += $jsonData['total'];
                        // $local_credit += $jsonData['total_idr'];
                    }
                }

                if (@$journal['debit'] > $total_debit) {
                    $total_debit = $journal['debit'];
                    // $local_debit = $journal['local_debit'];
                }

                if (@$journal['credit'] > $total_credit) {
                    $total_credit = $journal['credit'];
                    // $local_credit = $journal['local_credit'];
                }

                $arr[] = array(
                    "account_number" => $journal['account_number'],
                    "account_name" => $journal['account_name'],
                    "debit" => round($total_debit, 2),
                    "credit" => round($total_credit, 2),
                    "local_debit" => round($local_debit, 2),
                    "local_credit" => round($local_credit, 2),
                    "flag" => $journal['flag'],
                );
            }
        } else {
            $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);
            $total = 0;
            $flag = 1;
            $mergedData = array();
            foreach ($jsonDatas as $jsonData) {
                $account_number = $jsonData["account_number"];
                $account_name = $jsonData["account_name"];
                $account_type = $jsonData["account_type"];
                $total = $jsonData["total"];
                // $total_local = $jsonData["total_idr"];  // undefined
                $total_local = $jsonData["total"];

                if (isset($mergedData[$account_number])) {
                    // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number]["debit"] += $total;
                        $mergedData[$account_number]["local_debit"] += $total_local;
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number]["credit"] += $total;
                        $mergedData[$account_number]["local_credit"] += $total_local;
                    }
                } else {
                    // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "debit" => $total,
                            "local_debit" => $total_local,
                            "flag" => $flag,
                        );
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "credit" => $total,
                            "local_credit" => $total_local,
                            "flag" => $flag,
                        );
                    }
                }

                $flag++;
            }

            // Ubah hasil penggabungan menjadi indeks numerik jika diperlukan
            $arr = array_values($mergedData);
        }

        echo json_encode($arr);
    }

    public function readDueDate($trans_date, $payment_term)
    {
        $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime(base64_decode($trans_date))));
        die($due_date);
    }

    public function readReceipt($type = "purchase")
    {
        $supplier_id = $this->input->get('supplier_id');
        $item_category_id = $this->input->get('item_category_id');

        if ($type == "purchase") {
            $dp = "and d.total_dp = 0";
        } else {
            $dp = "and d.total_dp > 0";
        }

        $records = $this->crud->query("SELECT a.receipt_no, d.taxes, d.total_dp
            FROM purchase_order_receipts a
            JOIN item_rm b ON a.item_rm_id = b.id
            JOIN item_categories c ON b.item_category_id = c.id
            JOIN purchase_orders d ON a.po_no = d.po_no
            WHERE a.supplier_id = '$supplier_id' and c.id = '$item_category_id' and a.status = '0' $dp
            GROUP BY a.receipt_no 
            ORDER BY a.created_date DESC");
        echo json_encode($records);
    }

    public function readReceiptUpdate($type = "purchase")
    {
        $supplier_id = $this->input->get('supplier_id');
        $item_category_id = $this->input->get('item_category_id');
        $search_query_q = isset($_POST['q']) ? $_POST['q'] : ""; 

        $arrayPOR = [];
        if (!empty($search_query_q)) {
            $arrayPOR = array_map('trim', explode(',', $search_query_q));
            $arrayPOR = array_filter($arrayPOR);
        }

        $this->db->select('a.receipt_no, d.taxes, d.total_dp');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'join');
        $this->db->join('item_categories c', 'b.item_category_id = c.id', 'join');
        $this->db->join('purchase_orders d', 'a.po_no = d.po_no', 'join');

        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('c.id', $item_category_id);
        // $this->db->where('a.status', '0');

        // Add condition for total_dp based on $type
        if ($type == "purchase") {
            $this->db->where('d.total_dp', 0);
        } else {
            $this->db->where('d.total_dp >', 0);
        }

        if (!empty($arrayPOR)) {
            $this->db->where_in('a.receipt_no', $arrayPOR);
        }
        
        $this->db->group_by('a.receipt_no, d.taxes, d.total_dp');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result();

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readPurchaseInvoice($item_category)
    {
        $data = $this->crud->query("SELECT DISTINCT `number` FROM purchase_invoices WHERE `status` = '0' and category_id = '$item_category' ORDER BY `number` ASC");
        echo json_encode($data);
    }

    public function readPurchaseReceipt($item_category)
    {
        $data = $this->crud->query("SELECT DISTINCT `por_no` FROM purchase_invoices WHERE `status` = '0' and category_id = '$item_category' ORDER BY `por_no` ASC");
        echo json_encode($data);
    }

    public function readPurchaseOrder($item_category)
    {
        $data = $this->crud->query("SELECT DISTINCT `po_no` FROM purchase_invoices WHERE `status` = '0' and category_id = '$item_category' ORDER BY `po_no` ASC");
        echo json_encode($data);
    }

    public function readCurrencies()
    {
        $data = $this->crud->query("SELECT DISTINCT `name` FROM currencies WHERE `status` = '0'");
        echo json_encode($data);
    }

    public function readInvoice($item_category)
    {
        $data = $this->crud->query("SELECT DISTINCT `invoice_no` FROM purchase_invoices WHERE `status` = '0' and category_id = '$item_category' ORDER BY `invoice_no` ASC");
        echo json_encode($data);
    }

    public function readExchangeRates()
    {
        $period = $this->input->get('period'); 
        $currency = $this->input->get('currency');
        
        $this->db->select('middle,currency_from');
        $this->db->from('exchange_rates');
        $this->db->where("'$period' BETWEEN start_date AND end_date");
        $this->db->where('currency_from', $currency);
        $query = $this->db->get();
        $records = $query->result();
        
        echo json_encode($records);
    }

    public function check_faktur_no()
    {
        $faktur_no = base64_decode($this->input->get('faktur_no'));
        
        // Menggunakan query builder CodeIgniter
        $this->db->select('faktur_no');
        $this->db->from('purchase_invoices');
        $this->db->where('faktur_no', $faktur_no);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            echo json_encode(['exists' => true]);
        } else {
            echo json_encode(['exists' => false]);
        }
    }

    public function number($trans_date)
    {
        $datenow    = "PI-" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM purchase_invoices WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . "-" . $autoID;
    }

    public function numberInvoice($type)
    {
        if ($type == "dp") {
            $datenow    = "INVWDP";
        } else {
            $datenow    = "INVTMP";
        }

        echo $datenow . "-" . time();
    }

    public function datatablesTemp()//berubah : penambahan COALESCE(g. middle,1) as middle
    {
        $por_no = base64_decode($this->input->get('por_no'));
        $trans_date = base64_decode($this->input->get('trans_date'));
        $por_no_ex = explode(",", $por_no);

        $this->db->select("a.receipt_no as por_no, a.po_no, c.id as item_rm_id, c.number as item_number, c.name as item_name, c.uom, b.currency, e.item_supplier as supplier_product,
            a.qty_receipt as qty, f.price, f.discount, 'IDR' as currency_local, h.account_number, i.account_name,
            ((a.qty_receipt * f.price) - (a.qty_receipt * f.price) * (f.discount / 100)) as total,COALESCE(g. middle,1) as middle, 
            (CASE WHEN g.middle != '' THEN (g.middle * ((a.qty_receipt * f.price) - (a.qty_receipt * f.price) * (f.discount / 100))) ELSE ((a.qty_receipt * f.price) - (a.qty_receipt * f.price) * (f.discount / 100)) END) as total_local");
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        // $this->db->join('uom d', 'c.uom_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_rm_id');
        // $this->db->join('exchange_rates g', "g.start_date = DATE_FORMAT((a.receipt_date - INTERVAL '1' MONTH), '%Y-%m-01') and g.currency_from = b.currency", 'left');
        $this->db->join('exchange_rates g',"g.start_date <= '{$trans_date}' AND g.end_date >= '{$trans_date}' AND g.currency_from = b.currency",'left');
        $this->db->join('item_familys h', "c.item_family_id = h.id", 'left');
        $this->db->join('account_coa i', "h.account_number = i.account_number", 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where_in('a.receipt_no', $por_no_ex);
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.item_rm_id');
        $this->db->group_by('a.receipt_no');
        $this->db->order_by('a.receipt_no', 'asc');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $id = 1;
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $obj[] = array(
                "no_id" => $id,
                "por_no" => $record['por_no'],
                "po_no" => $record['po_no'],
                "item_rm_id" => $record['item_rm_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "supplier_product" => $record['supplier_product'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "currency_local" => $record['currency_local'],
                "qty" => $record['qty'],
                "discount" => $record['discount'],
                "price" => $record['price'],
                "total" => $record['total'],
                "rate" => $record['middle'],
                "total_local" => $record['total_local'],
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "account_type" => "DEBIT"
            );

            $id++;
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 4);
        die(json_encode($arr));
    }

    public function datatablesTemp2()//berubah : penambahan COALESCE(g. middle,1) as middle
    {
        $po_no = base64_decode($this->input->get('po_no'));

        $this->db->select("a.po_no, a.po_date, a.po_name, a.item_rm_id, b.number as item_number, b.name as item_name, a.qty, b.uom, d.item_supplier as supplier_product,
        e.currency, 'IDR' as currency_local, a.price, f.account_number, i.account_name,
        (a.qty * a.price) as total, COALESCE(g. middle,1) as middle,
        (CASE WHEN g.selling is null THEN (a.qty * a.price) ELSE
        (a.qty * (a.price * g.selling)) END) as total_local");
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id');
        $this->db->join('item_familys f', "b.item_family_id = f.id", 'left');
        $this->db->join('exchange_rates g', "e.currency = g.currency_from and g.currency_to = 'IDR'", 'left');
        $this->db->join('account_coa i', "f.account_number = i.account_number", 'left');
        $this->db->where('a.status', 0);
        $this->db->like('a.po_no', $po_no);
        $this->db->group_by('a.item_rm_id');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $obj[] = array(
                "por_no" => "-",
                "po_no" => $record['po_no'],
                "item_rm_id" => $record['item_rm_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "supplier_product" => $record['supplier_product'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "currency_local" => $record['currency_local'],
                "qty" => $record['qty'],
                "price" => $record['price'],
                "total" => $record['total'],
                "rate" => $record['middle'],
                "total_local" => $record['total_local'],
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "account_type" => "DEBIT",
            );
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 4);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_category_id = base64_decode($this->input->get('filter_category_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, e.number as gl_no, b.name as supplier_name, c.name as item_category_name, d.name as journal_type_name, 
                a.invoice_no as status_invoice,
                SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) as total_sub,
                ((SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) + a.total_vat) - a.total_pph) as total_grand");
            $this->db->select('GROUP_CONCAT(DISTINCT REPLACE(a.por_no, " ", "") SEPARATOR ",") as por_numbers');
            $this->db->select("'view' as details");
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_categories c', 'a.category_id = c.id', 'left');
            $this->db->join('journal_types d', 'a.journal_type_id = d.id', 'left');
            // $this->db->join("journal_postings e", 'a.number = e.document_no', 'left');
            $this->db->join('(SELECT document_no, MIN(number) AS number FROM journal_postings GROUP BY document_no) e','a.number = e.document_no','left');
            if ($filter_type == "PID") {
                $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
            } elseif ($filter_type == "PAY") {
                $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
            } else {
                $this->db->where("a.trans_date between '$date_from' and '$date_to'");
            }
            $this->db->like('a.category_id', $filter_category_id);
            $this->db->like('a.number', $filter_purchase_invoice);
            $this->db->like('a.por_no', $filter_purchase_receipt);
            $this->db->like('a.po_no', $filter_purchase_order);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.invoice_no', $filter_status_supplier);
            $this->db->like('a.invoice_no', $filter_invoice_no);
            $this->db->like('a.status', $filter_status);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.number');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select("a.*, 'datatbles' as origin");
            $this->db->select("'view' as details");
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    // AUTO INSERT TO FIXED ASSET (trigger from ajax)
    function autoFixedAsset($number) 
    {
        $pi_number = base64_decode($number);
        $main_pi = $this->db->select('account_number, currency, rate')->from('purchase_invoices')->where('number', $pi_number)->get()->row();
        $getAllPI  = $this->db->select('*, YEAR(trans_date) as trans_year, MONTH(trans_date) as trans_month')->from('purchase_invoices')->where('number', $pi_number)->get()->result_array();

        // Auto-insert to fixed asset jika CATEGORY = FIXED ASSET
        $this->db->select('account_coa.account_number, account_coa.account_name, account_group_details.name as account_group_detail_name');
        $this->db->from('account_coa');
        $this->db->join('account_group_details', 'account_group_details.id = account_coa.account_group_detail_id', 'left');
        $this->db->where('account_coa.deleted', 0);
        $this->db->where('account_coa.account_number', $main_pi->account_number);
        $checkAccount = $this->db->get()->row();

        if ($checkAccount && stripos($checkAccount->account_group_detail_name, 'Fixed Asset') !== false) {
            if (empty($getAllPI)) {
                echo json_encode(['success' => false, 'message' => 'Purchase Invoice is not found.']);
                return;
            } else {
                $result = [];
                // Get Item di dalam PI (jika terdapat item > 1)
                foreach ($getAllPI as $item) 
                {
                    $readItemFamily = $this->db->select('b.id as item_id, c.id as item_family_id, c.number as code, c.name as item_family_name, c.useful_life_of_asset_year')
                        ->from('item_rm b')
                        ->join('item_familys c', 'b.item_family_id = c.id')
                        ->where('b.id', $item['item_rm_id'])
                        ->get()->row();
                    $item_family_id = $readItemFamily->item_family_id ?? null;
                    $family_code    = $readItemFamily->code ?? "UNDEFINED";
                    $estimate_year  = $readItemFamily->useful_life_of_asset_year ?? 0;
                    
                    $readSupplier  = $this->db->select('id, name as supplier_name')->from('suppliers')->where('id', $item['supplier_id'])->get()->row();
                    $supplier_name = $readSupplier->supplier_name;

                    $this->db->select('c.number, a.request_no, a.po_no, b.item_rm_id, b.department');
                    $this->db->from('purchase_orders a');
                    $this->db->join('purchase_requests b', 'a.request_no = b.request_no');
                    $this->db->join('purchase_invoices c', 'a.po_no = c.po_no');
                    $this->db->where('b.department IS NOT NULL');
                    $this->db->where('c.number', $item['number']);
                    $this->db->where('b.item_rm_id', $item['item_rm_id']);
                    $this->db->order_by('c.number', 'asc');
                    $readDepartment      = $this->db->get()->row();                
                    $previous_department = $readDepartment->department;
                    $department          = $readDepartment->department;
                    
                    // Set default value
                    $method     = "Straightline"; 
                    $remark     = "Auto-create Fixed Asset via Purchase Invoices";
                    $trans_date = $item['trans_date'];
                    $item_name  = $item['item_name'];
                    $currency   = $item['currency'];
                    $rate       = $item['rate'];
                    
                    // Validasi Jika Currency bukan IDR tetapi rate=1, maka tidak dapat insert Fixed Asset
                    if ($currency !== "IDR" && $rate == "1") {
                        $result[] = ['success' => false, 'message' => 'Failed! Currency is not IDR but Rate=1.', 'theme' => 'error'];
                    } else {
                        // Convert to local currency (IDR)
                        if ($currency !== "IDR") {
                            $this->db->select('middle,currency_from');
                            $this->db->from('exchange_rates');
                            $this->db->where("'$trans_date' BETWEEN start_date AND end_date");
                            $this->db->where('currency_from', $currency);
                            $getExchange = $this->db->get()->row();

                            if (!empty($getExchange)) {
                                $rate = $getExchange->middle;
                            } else {
                                $rate = 0;
                            }                            
                        } else {
                            $rate = 1;
                        }
                        
                        // Validasi jika rate 0, maka tidak bisa proses save data
                        if ($rate == 0) {
                            $result[] = ['success' => false, 'message' => 'Rate is not available for this transaction date! Rate=0', 'theme' => 'error'];
                        } else {

                            $estimate_month = $estimate_year * 12;
                            $expired_date   = date("Y-m-d", strtotime("+" . $estimate_month . ' months', strtotime($item['trans_date'])));
                            $cost_local     = $item['price'] * $rate;
                            $total_local    = $item['total'] * $rate;
                
                            $depreciation = 0;
                            if ($estimate_year != "0" && $estimate_month != "0") {
                                $depreciation = ($cost_local / $estimate_month);
                            }

                            // Check asset yang sudah di-insert sebelumnya
                            $checkCondition = [
                                'purchase_invoice_number' => $pi_number,
                                'item_rm_id'              => $item['item_rm_id'],
                                'trans_date'              => $item['trans_date'],
                                'cost'                    => floatval($cost_local),
                                'total'                   => floatval($total_local)
                            ];
                            $this->db->select('*');
                            $this->db->from('asset_fixeds');
                            $this->db->where($checkCondition);
                            $this->db->like('number', $item['item_no'], 'after');
                            $existingAssets = $this->db->get()->result();

                            // UPDATE jika asset sudah ada
                            if ($existingAssets) {                               
                                foreach ($existingAssets as $existingAsset) {
                                    $data = [
                                        'name'                    => $item['item_name'],
                                        'item_family_id'          => $item_family_id,
                                        'supplier_name'           => $supplier_name,
                                        'qty'                     => 1, // Pastikan qty adalah 1 untuk setiap aset
                                        'cost'                    => $cost_local,
                                        'currency'                => $currency,
                                        'estimate_year'           => $estimate_year,
                                        'estimate_month'          => $estimate_month,
                                        'expired_date'            => $expired_date,
                                        'depreciation'            => $depreciation,
                                        'remarks'                 => "Auto-update Fixed Asset via Purchase Invoices",
                                        'method'                  => $method,
                                        'previous_department'     => $previous_department,
                                        'department'              => $department,
                                        'total'                   => $total_local,
                                    ];
                                    $result[] = $this->crud->update('asset_fixeds', ['id' => $existingAsset->id], $data); 
                                }
                            } 
                            else 
                            {
                                // ASSET NO
                                $trans_year = $item['trans_year'];
                                $trans_month = str_pad($item['trans_month'], 2, '0', STR_PAD_LEFT);
                                $asset_prefix = $family_code . "." . $trans_year . "." . $trans_month . ".";
            
                                // Ambil nomor aset terbesar sekali sebelum loop
                                $this->db->select_max('number', 'kode');
                                $this->db->like('number', $asset_prefix, 'after');
                                $sqlGetID = $this->db->get('asset_fixeds')->row();
                                
                                $urutan = 0;
                                if ($sqlGetID && $sqlGetID->kode) {
                                    $urutan = (int) substr($sqlGetID->kode, -5);
                                }

                                // AUTO-INSERT multiple qty > 1
                                for ($i = 0; $i < $item['qty']; $i++) {
                                    if ($item['qty'] > 1) {
                                        if ($sqlGetID->kode == NULL) {
                                            $asset_number = $asset_prefix . sprintf("%05d", ($i + 1));
                                        } else {                        
                                            $urutan++;
                                            $asset_number = $asset_prefix . sprintf("%05d", $urutan);
                                        }
                                    } else {
                                        $urutan++;
                                        $asset_number = $asset_prefix . sprintf("%05d", $urutan);
                                    }

                                    $data = [
                                        "purchase_invoice_number" => $item['number'],
                                        "item_rm_id"              => $item['item_rm_id'],
                                        "number"                  => $asset_number,
                                        "name"                    => $item_name,
                                        "item_family_id"          => $item_family_id,
                                        "asset_category_number"   => null,
                                        "trans_date"              => $trans_date,
                                        "supplier_name"           => $supplier_name,
                                        "qty"                     => 1,
                                        "cost"                    => floatval($cost_local),
                                        "currency"                => $currency,
                                        "estimate_year"           => $estimate_year,
                                        "estimate_month"          => $estimate_month,
                                        "expired_date"            => $expired_date,
                                        "depreciation"            => floatval($depreciation),
                                        "remarks"                 => $remark,
                                        "method"                  => $method,
                                        "previous_department"     => $previous_department ?? null,
                                        "previous_location"       => null,
                                        "department"              => $department ?? null,
                                        "location"                => null,
                                        "total"                   => floatval($total_local),
                                    ];
                                    
                                    $result[] = $this->crud->create('asset_fixeds', $data);
                                }
                            }

                        }   
                    }

                }
                echo json_encode($result);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Account Category is not Fixed Asset', 'theme' => 'error']);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            // $purchase_invoices = $this->crud->read('purchase_invoices', [], ["por_no" => $post['por_no'], "item_no" => $post['item_no'], "supplier_id" => $post['supplier_id'], "trans_date" => $post['trans_date']]);

            if (!empty($post['id'])) {
                $send = $this->crud->update('purchase_invoices', ["id" => $post['id']], $post);
                echo $send;

            } else {
                // Skenario tidak ada POR Number / input manual
                if (!empty($_POST['por_no']) && !empty($_POST['po_no']) && !empty($_POST['item_no']) ) {

                    // Skenario untuk Tambah data baru atau Update data yang "mirip" (jika por_no dan po_no sama tapi item_no berbeda / multi item)
                    $this->db->where('por_no', @$post['por_no']);
                    $this->db->where('po_no', @$post['po_no']);
                    $this->db->where('item_no', @$post['item_no']); 
                    $checkExisting = $this->db->get('purchase_invoices')->row();

                    if ($checkExisting) {
                        // Jika data dengan kombinasi por_no, po_no, dan item_no sudah ada, lakukan UPDATE pada record tersebut
                        $invoice_id_to_update = $checkExisting->id;
                        $this->db->update('purchase_invoices', $post, ["id" => $invoice_id_to_update]);
                        
                        if ($this->db->affected_rows() > 0) {
                            echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
                        } else {
                            echo json_encode(array("title" => "Info", "message" => "No changes detected, data remains the same.", "theme" => "info"));
                        }    
                    } else {
                        // Kombinasi por_no, po_no, dan item_no belum ada, lakukan CREATE record baru
                        $send = $this->crud->create('purchase_invoices', $post);
                        if ($send) {
                            if ($post['por_no'] != "-") {
                                if ($post['type'] != "dp") {
                                    $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => $post['por_no'], "po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                                }
                            } else {
                                $update = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                            }
                        }
                        echo $send;
                    }                

                } else {
                    // Kombinasi por_no, po_no, dan item_no belum ada, lakukan CREATE record baru
                    $send = $this->crud->create('purchase_invoices', $post);
                    if ($send) {
                        if ($post['por_no'] != "-") {
                            if ($post['type'] != "dp") {
                                $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => $post['por_no'], "po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                            }
                        } else {
                            $update = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                        }
                    }
                    echo $send;

                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createOld() // terjadi redudansi data ketika update
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            // $purchase_invoices = $this->crud->read('purchase_invoices', [], ["por_no" => $post['por_no'], "item_no" => $post['item_no'], "supplier_id" => $post['supplier_id'], "trans_date" => $post['trans_date']]);

            if (@$post['id'] != "") {
                $send = $this->crud->update('purchase_invoices', ["id" => $post['id']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('purchase_invoices', $post);
                if ($send) {
                    if ($post['por_no'] != "-") {
                        if ($post['type'] != "dp") {
                            $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => $post['por_no'], "po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                        }
                    } else {
                        $update = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                    }
                }
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        $jsonData2 = $this->input->post('jsonData2');

        // Simpan data JSON ke dalam file
        file_put_contents('json/purchase_invoices.json', $jsonData);
        file_put_contents('json/purchase_invoice_journals.json', $jsonData2);
    }

    public function createJournals()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $purchase_invoice_journals = $this->crud->read('purchase_invoice_journals', [], ["number" => $post['number'], "account_number" => $post['account_number'], "flag" => $post['flag']]);

            if (@$purchase_invoice_journals->id != "") {
                $send = $this->crud->update('purchase_invoice_journals', ["number" => $post['number'], "account_number" => $post['account_number'], "flag" => $post['flag']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('purchase_invoice_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('purchase_invoices', ["number" => $post['number']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_invoices', $data);
        echo $send;
    }

    public function deleteByPOR() 
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_invoices', $data);
        echo $send;

        if ($this->input->method() === 'post') 
        {
            $por_no = $this->input->post('por_no');

            if ($data !== null) 
            {
                // check availability first 
                $check_availability = $this->crud->read("purchase_invoices", [], ["por_no" => $por_no]);
                if (!empty($check_availability)) {
                    $this->db->where_in('por_no', $por_no);
                    $result = $this->db->delete('purchase_invoices'); // Mengembalikan TRUE/FALSE
                    echo json_encode($result);

                    // if ($result) {
                    //     $rows_affected = $this->db->affected_rows(); // lihat data yang telah dihapus
                    //     echo json_encode(['success' => true, 'message' => "Data $rows_affected berhasil dihapus."]);
                    // } else {
                    //     echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
                    // }
                }
            } else {
                $this->output->set_status_header(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Parameter ID item tidak lengkap.']);
            }
        } else {
            $this->output->set_status_header(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
        }
    }

    public function deleteJournal()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_invoice_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();

        $purchase_invoices = $this->crud->reads("purchase_invoices", [], ["number" => $data['number']]);
        foreach ($purchase_invoices as $purchase_invoice) {
            if ($purchase_invoice->por_no != "-") {
                $this->crud->update("purchase_order_receipts", [
                    "receipt_no" => $purchase_invoice->por_no,
                    "po_no" => $purchase_invoice->po_no,
                    "item_rm_id" => $purchase_invoice->item_rm_id,
                    "supplier_id" => $purchase_invoice->supplier_id
                ], ["status" => 0]);
            } else {
                $this->crud->update("purchase_order_others", [
                    "po_no" => $purchase_invoice->po_no,
                ], ["status" => 0]);
            }
        }

        $send = $this->crud->delete('purchase_invoices', $data);
        $this->crud->delete('purchase_invoice_journals', $data);
        echo $send;
    }


    // ---------- UPLOAD FUNCTIONS ------------
    //UPLOAD DATA
    public function upload()
    {
        header('Content-Type: application/json');

        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        try {
            $target = basename($_FILES['file_upload']['name']);
            
            // Use a more robust check for file upload success
            if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $target)) {
                throw new Exception("Failed to upload file.");
            }
            
            chmod($target, 0777);
            $file = $target;
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);
            $datas = [];

            for ($i = 4; $i <= $total_row; $i++) {
                $datas[] = [
                    //excel
                    'number'            => trim($data->val($i, 2)),
                    'invoice_no'        => trim($data->val($i, 3)),
                    'faktur_no'         => trim($data->val($i, 4)),
                    'supplier_id'       => trim($data->val($i, 5)),
                    'type'              => trim($data->val($i, 6)),
                    'trans_date'        => trim($data->val($i, 7)),
                    'category_id'       => trim($data->val($i, 8)),
                    'journal_type_code' => trim($data->val($i, 9)),
                    'payment_term'      => trim($data->val($i, 10)),
                    'remarks'           => trim($data->val($i, 11)),
                    'por_no'            => trim($data->val($i, 12)),
                    'po_no'             => trim($data->val($i, 13)),
                    'item_no'           => trim($data->val($i, 14)),
                    'item_name'         => trim($data->val($i, 15)),
                    'qty'               => trim($data->val($i, 16)),
                    'uom'               => trim($data->val($i, 17)),
                    'currency'          => trim($data->val($i, 18)),
                    'price'             => trim($data->val($i, 19)),
                    'rate'              => trim($data->val($i, 20)),
                    'account_number'    => trim($data->val($i, 21)),
                    'account_type'      => trim($data->val($i, 22)),
                    'action'            => trim($data->val($i, 23)),
                ];
            }
            
            $response = [
                'total' => count($datas),
                'data' => $datas
            ];
            
            echo json_encode($response);

        } catch (Exception $e) {
            // Handle upload errors gracefully
            http_response_code(500); // Set HTTP status code for server error
            echo json_encode(['error' => $e->getMessage()]);
        } finally {
            // Ensure the temporary file is deleted even if an error occurs
            if (isset($target) && file_exists($target)) {
                unlink($target);
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_invoices.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_invoices.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_invoices.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $supplier     = $this->crud->read('suppliers', ["id" => $data['supplier_id']]);
            $category     = $this->crud->read('item_categories', ["id" => $data['category_id']]);
            $journal_type = $this->crud->read('journal_types', ["number" => $data['journal_type_code']]);
            $account_coa  = $this->crud->read('account_coa', ["account_number" => $data['account_number']]);
            $item_rm      = $this->crud->read('item_rm', ["number" => $data['item_no']]);
            $item_rm_id   = !empty($item_rm) ? $item_rm->id : "";
            $purchase_invoices = $this->db->select('*')->from('purchase_invoices')->where('number', $data['number'])->where('po_no', $data['po_no'])->where('account_number', $data['account_number'])->like('price', $data['price'])->like('item_rm_id', $item_rm_id, 'both')->get()->row();
            $purchase_receipts = $this->db->select('*')->from('purchase_order_receipts')->where('receipt_no', $data['por_no'])->where('po_no', $data['po_no'])->where('supplier_id', $data['supplier_id'])->like('item_rm_id', $item_rm_id, 'both')->get()->row();
            
            // Tambahkan validasi jika Purchase Invoice No. sudah ada dari insert via Form bukan via upload (Bu Nina)
            $this->db->select('*');
            $this->db->from('purchase_invoices');
            $this->db->where('number', $data['number']);
            $this->db->where('upload', null);
            $this->db->where('upload_date', null);
            $invoice_manual = $this->db->get()->result();
            
            $trans_date = $data['trans_date'];
            $valid_date = ($d = DateTime::createFromFormat('Y-m-d', $trans_date)) && $d->format('Y-m-d') === $trans_date;
            
            // Validate required data
            if (!empty($invoice_manual)) {
                echo json_encode(["title" => "Duplicated", "message" => "Purchase Invoice No " . $data['number'] . " is already exists. Please provide a unique number.", "theme" => "error"]);
            
            } elseif (empty($data['action']) || (strtolower($data['action']) !== "new" && strtolower($data['action']) !== "update")) {
                echo json_encode(["title" => "Error", "message" => "ACTION must be NEW or UPDATE", "theme" => "error"]);
            
            } elseif (strtolower($data['action']) !== 'update' && !empty($purchase_invoices) && strtoupper($purchase_invoices->upload) === "YES") {
                echo json_encode(["title" => "Duplicated", "message" => "Action=NEW and Purchase Invoice No. " . $data['number'] . " is Duplicate Data", "theme" => "error"]);
            
            } elseif (!empty($purchase_invoices) && $purchase_invoices->status == "1") {
                echo json_encode(["title" => "Duplicated", "message" => "Purchase Invoice No. " . $data['number'] . " has been processed previously (Closed)", "theme" => "error"]);
            
            } elseif ($data['por_no'] !== "-" && !empty($purchase_receipts) && $purchase_receipts->status == "1") {
                echo json_encode(["title" => "Duplicated", "message" => "Purchase Order Receipt No. " . $data['por_no'] . " has been processed previously (Closed)", "theme" => "error"]);
            
            } elseif (empty($data['type']) || (strtoupper($data['type']) !== "PURCHASE" && strtoupper($data['type']) !== "OTHER")) {
                echo json_encode(["title" => "Error", "message" => "Type must be PURCHASE or OTHER", "theme" => "error"]);
                
            } elseif (empty($data['account_type']) || (strtoupper($data['account_type']) !== "DEBIT" && strtoupper($data['account_type']) !== "CREDIT")) {
                echo json_encode(["title" => "Error", "message" => "Account Type must be DEBIT or CREDIT", "theme" => "error"]);
                
            } elseif (empty($trans_date) || !$valid_date) {
                echo json_encode(["title" => "Error", "message" => "Date format must be 'YYYY-MM-DD'", "theme" => "error"]);
                
            } elseif (empty($supplier->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Supplier Code " . $data['supplier_id'] . " is Not Found", "theme" => "error"]);
                
            } elseif (empty($category->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Category ID " . $data['account_number'] . " is Not Found", "theme" => "error"]);
                
            } elseif (empty($account_coa->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " is Not Found", "theme" => "error"]);
            
            } elseif (empty($journal_type->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Journal Type " . $data['journal_type_code'] . " is Not Found", "theme" => "error"]);
                
            } elseif (!empty($purchase_invoices) && $data["por_no"] !== "-" && $data["po_no"] !== "-") {
                echo json_encode(["title" => "Duplicated", "message" => "Purchase Invoice No. " . $data['number'] . " & Receipt No. " . $data['por_no'] . " & PO No. " . $data['po_no'] . " has been processed previously (Closed)", "theme" => "error"]);
                
            } else {
                // Get PI number
                if (empty($data["number"]) || $data["number"] == "-") {
                    $datenow = "PI-" . date("Ymd", strtotime($data["trans_date"]));
                    $sqlGetID = $this->db->select_max('number', 'kode')->like('number', $datenow, 'after')->get('purchase_invoices')->row();
                    $urutan = ($sqlGetID && $sqlGetID->kode) ? (int) substr($sqlGetID->kode, -4) : 0;
                    $urutan++;
                    $autoID = sprintf("%04s", $urutan);
                    $number = $datenow . "-" . $autoID;
                    $data["number"] = $number;
                } else {
                    $number = $data["number"];
                }
                
                // Get Supplier invoice number
                $invoice_no = $data["invoice_no"];
                if (empty($invoice_no)) {
                    $invoice_no = ($data['type'] == "dp") ? "INVWDP-" . time() : "INVTMP-" . time();
                }
                
                // Get Rate
                $currency = $data['currency'];
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($data["trans_date"])));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);
                if ($currency != "IDR") {
                    if ($exchange) {
                        $rate = $exchange->middle;
                    } else {
                        $rate = (float)($data['rate'] ?? 1);
                    }
                } else {
                    $rate = 1;
                }
                
                // Get Supplier Product
                $item_rm_id       = $item_rm->id ?? "";
                $supplier_product = $this->crud->read('supplier_items', ["supplier_id" => $data['supplier_id'], "item_rm_id" => $item_rm_id]);

                // Prepare data
                $trans_date     = $data['trans_date'];
                $payment_term   = $supplier->payment_term ?? $data["payment_term"];
                $vat_rate       = $supplier->vat ?? 0;
                $due_date       = date('Y-m-d', strtotime('+' . $supplier->payment_term . ' days', strtotime($data['trans_date'])));
                $price          = (float)($data['price'] ?? $supplier_product->price ?? 0);
                $qty            = (float)($data['qty'] ?? 0);
                $discount_rate  = (float)($data['discount'] ?? 0);
                $subtotal_item  = (float)$price * $qty;
                $discount       = (float)$subtotal_item * ($discount_rate / 100);
                $total          = (float)$subtotal_item - $discount;
                $total_idr      = (float)$total * $rate;

                $dataFinal = [
                    "supplier_id"       => $supplier->id,
                    "journal_type_id"   => $journal_type->id,
                    "category_id"       => $category->id,
                    "number"            => $number,
                    "supplier_product"  => $supplier_product->item_supplier ?? "-",
                    "trans_date"        => $trans_date,
                    "due_date"          => $due_date,
                    "invoice_no"        => $invoice_no,
                    "payment_term"      => $payment_term,
                    "currency"          => $data['currency'] ?? "IDR",
                    "type"              => $data["type"],
                    "po_no"             => $data["po_no"] ?? $data["po_no"],
                    "por_no"            => $data["por_no"] ?? $data["por_no"],
                    "item_rm_id"        => $item_rm_id ?? "-",
                    "item_no"           => $item_rm->number ?? $data["item_no"],
                    "item_name"         => $item_rm->name ?? $data["item_name"],
                    "uom"               => $item_rm->uom ?? $data["uom"],
                    "qty"               => $qty ?? $data["qty"],
                    "price"             => $price ?? $data["price"],
                    "discount"          => $discount,
                    "rate"              => $rate,
                    "total"             => $total,
                    "total_idr"         => $total_idr,
                    "faktur_no"         => $data["faktur_no"] ?? null,
                    "account_number"    => $data['account_number'],
                    "account_type"      => $data["account_type"],
                    "remarks"           => $data["remarks"] ?? "PI via Upload",
                    "taxes"             => $vat_rate,
                    "pph"               => 0,
                    "total_discount"    => 0,
                    "total_vat"         => 0,
                    "total_dpp"         => 0,
                    "total_pph"         => 0,
                    "total_dp"          => 0,
                    "status"            => 0,
                    "upload"            => "YES",
                    "upload_date"       => date('Y-m-d'),
                ];
                

                // CREATE (INSERT)
                if (strtoupper($data['action']) === "NEW") {
                    $send = $this->crud->create('purchase_invoices', $dataFinal);
                } else {
                    if (!empty($purchase_invoices)) {
                        $send = $this->crud->update('purchase_invoices', ["number" => $dataFinal['number']], $dataFinal);
                    } else {
                        $send = $this->crud->create('purchase_invoices', $dataFinal);
                    }
                }

                if ($send) {
                    echo $send;
                } else {
                    echo json_encode(["title" => "Error", "message" => "Failed to create invoice " . $data['number'], "theme" => "error"]);
                }
            }
        } else {
            // Kirim respon error jika bukan dari metode POST
            echo json_encode(["title" => "Error", "message" => "Invalid request method", "theme" => "error"]);
        }
    }

    // UPLOAD GET JOURNAL
    public function uploadGetJournal()
    {
        $this->db->select('a.*, b.account_name');
        $this->db->from('purchase_invoices a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.upload', "YES");
        $this->db->where('a.upload_date', date("Y-m-d"));
        $records = $this->db->get()->result_array();

        // Array untuk menyimpan hasil akhir
        $journal_setup_map = [];
        $allJournals = [];
        $groupedInvoices = [];
        
        // Konversi Journal Setup ke dalam array asosiatif untuk akses cepat
        $journal_setups = $this->db->get_where('journal_setups')->result_array();
        foreach ($journal_setups as $setup) {
            $journal_setup_map[$setup['journal_type_id']][$setup['account_number']] = $setup;
        }

        // Kelompokkan invoice berdasarkan nomor unik (number)
        foreach ($records as $record) {
            $number = $record['number'];
            if (!isset($groupedInvoices[$number])) {
                $groupedInvoices[$number] = [
                    'main_record' => $record,
                    'items' => []
                ];
            }
            $groupedInvoices[$number]['items'][] = $record;
        }

        // Proses setiap grup invoice
        foreach ($groupedInvoices as $number => $group) {
            $main_record = $group['main_record'];
            $items       = $group['items'];
            $rate        = (float) $main_record['rate'];
            $journal_type_id = $main_record['journal_type_id'];

            $mergedData = [];
            $grandTotal = 0;

            // Hitung total dari semua item
            foreach ($items as $item) {
                $total = (float) $item['total'];
                $grandTotal += $total;

                if (isset($mergedData[$item['account_number']])) {
                    $mergedData[$item['account_number']]['debit'] += ($item['account_type'] == "DEBIT" ? $total : 0);
                    $mergedData[$item['account_number']]['credit'] += ($item['account_type'] == "CREDIT" ? $total : 0);
                    $mergedData[$item['account_number']]['flag'] = 1; // Menambahkan flag pada data yang sudah ada
                } else {
                    $mergedData[$item['account_number']] = [
                        "number"         => $number,
                        "account_number" => $item['account_number'],
                        "account_name"   => $item['account_name'],
                        "debit"          => ($item['account_type'] == "DEBIT" ? $total : 0),
                        "credit"         => ($item['account_type'] == "CREDIT" ? $total : 0),
                        "flag"           => 1, // Menambahkan flag pada data baru
                    ];
                }
            }

            // Perhitungan DPP, VAT, PPH, dan TRADE PAYABLE
            $subTotal = $grandTotal;
            $totalDpp = (float) ($subTotal * (11/12));
            $vatRate  = $main_record['taxes'] ?? 0;
            $pphTotal = 0;
            $discount = $main_record['discount'] ?? 0;
            $vatTotal = (float) ($totalDpp * ($vatRate / 100));
            $totalTrade = (float) ((($subTotal - $discount) + $vatTotal) - $pphTotal);
            // $totalTrade = (($totalDpp - $discount) + $vatTotal) - $pphTotal; // tidak balance dengan totalDpp
            
            $journalVatIn = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '170.170.00')->get()->row_array();
            $journalTrade = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '210.120.00')->get()->row_array();
            // $journalPph   = $journal_setup_map[$journal_type_id]['210.130.00'];

            // Tambahkan jurnal VAT
            if ($journalVatIn) {
                $debit  = $vatTotal;
                $credit = 0;
                $mergedData[$journalVatIn['account_number']] = [
                    "number"         => $number,
                    "account_number" => $journalVatIn['account_number'],
                    "account_name"   => $journalVatIn['account_name'],
                    "debit"          => $debit,
                    "credit"         => $credit,
                    "flag"           => null,
                ];
            }            

            // Hitung dan tambahkan jurnal TRADE PAYABLE
            if ($journalTrade) {
                $debit  = 0;
                $credit = $totalTrade ?? 0;
                $mergedData[$journalTrade['account_number']] = [
                    "number"         => $number,
                    "account_number" => $journalTrade['account_number'],
                    "account_name"   => $journalTrade['account_name'],
                    "debit"          => $debit,
                    "credit"         => $credit,
                    "flag"           => null,
                ];
            }

            // Hitung dan tambahkan jurnal PPH (belum di setting dari excel uplad)
            // if (isset($data['pph_23']) && $data['pph_23'] == 'yes') { // Get PPH masih dari excel bukan dari Journal Setups
            //     $pphTotal = round($totalDpp * (2/100), 2);
            // }
            // if ($journalPph) {
            //     $pphRate = 0;
            //     switch ($journalPph['account_number']) {
            //         case "210.130.00": $pphRate = 0.02; break; // PPH 2%
            //     }
            //     $pphTotal = $subTotal * $pphRate;

            //     $debit = ($journalPph['status'] == "DEBIT") ? $pphTotal : 0;
            //     $credit = ($journalPph['status'] == "CREDIT") ? $pphTotal : 0;
            //     $mergedData[$journalPph['account_number']] = [
            //         "number"         => $number,
            //         "account_number" => $journalPph['account_number'],
            //         "account_name"   => $journalPph['account_name'],
            //         "debit"          => $debit,
            //         "credit"         => $credit,
            //     ];
            // }

            // Update total VAT dan PPH
            // tidak pake model crud->update karena baru create (tanpa log update)
            $this->db->update('purchase_invoices', ["total_vat" => $vatTotal, "total_pph" => $pphTotal], ["number" => $number]);

            // Tambahkan hasil jurnal ke array utama
            foreach (array_values($mergedData) as $journalEntry) {
                $allJournals[] = $journalEntry;
            }
        }

        $flag_counter = 1;
        foreach ($allJournals as &$journal) {
            $journal['flag'] = $flag_counter++;
        }
        unset($journal);

        // Respons JSON untuk JavaScript
        $result = [
            'total' => count($allJournals),
            'data'  => $allJournals
        ];

        echo json_encode($result);
    }

    public function uploadCreateJournal()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $purchase_invoice_journals = $this->crud->read('purchase_invoice_journals', [], ["number" => $post['number'], "account_number" => $post['account_number']]);

            if (@$purchase_invoice_journals->id != "") {
                // update error foreign key constraint debit credit
                // $send = $this->crud->update('purchase_invoice_journals', ["number" => $post['number'], "account_number" => $post['account_number']], $post);
                
                // delete existing then re-create 
                $this->crud->delete('purchase_invoice_journals', $post);
                $send = $this->crud->create('purchase_invoice_journals', $post);
                echo $send;
            } else {
                $send = $this->crud->create('purchase_invoice_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    public function uploadCreate_backup()
    {
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error", "message" => "Invalid request. No data received.", "theme" => "error"]);
            return;
        }

        $this->db->trans_begin();
        try {
            $data = $this->input->post('data');
            if (empty($data)) {
                $this->db->trans_rollback();
                echo json_encode(["title" => "Not Found", "message" => "Input data is empty.", "theme" => "error"]);
                return;
            }

            // Validate required data first.
            $get_supplier = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);
            if (empty($data['supplier_id']) || empty($get_supplier)) {
                echo json_encode(["title" => "Not Found", "message" => "Supplier ID: " . $data['supplier_id'] . " is Not Found", "theme" => "error"]);
                return;
            }

            if (empty($data['type']) && (strtoupper($data['type']) !== "PURCHASE" || strtoupper($data['type']) !== "OTHER")) {
                echo json_encode(["title" => "Not Found", "message" => "Purchase Invoice Type must be PURCHASE or OTHER", "theme" => "error"]);
                return;
            }
            
            $is_valid_date = ($d = DateTime::createFromFormat('Y-m-d', $data['trans_date'])) && $d->format('Y-m-d') === $data['trans_date'];
            if (empty($data['trans_date']) || !$is_valid_date) {
                echo json_encode(["title" => "Not Found", "message" => "Date format must be 'YYYY-MM-DD'", "theme" => "error"]);
                return;
            }

            $get_category = $this->crud->read('item_categories', [], ["id" => $data['category_id']]);
            if (empty($data['category_id']) || empty($get_category)) {
                echo json_encode(["title" => "Not Found", "message" => "Category ID: " . $data['category_id'] . " is Not Found", "theme" => "error"]);
                return;
            }

            $get_journal_type = $this->crud->read('journal_types', [], ["number" => $data['journal_type_code']]);
            if (empty($data['journal_type_code']) || empty($get_journal_type)) {
                echo json_encode(["title" => "Not Found", "message" => "Journal Type Code is Not Match", "theme" => "error"]);
                return;
            }

            $get_account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);
            if (empty($data['account_number']) || empty($get_account_coa)) {
                echo json_encode(["title" => "Not Found", "message" => "Account Number : " . $data['account_number'] . " is Not Found", "theme" => "error"]);
                return;
            }

            if (empty($data['account_type']) && (strtoupper($data['account_type']) !== "DEBIT" || strtoupper($data['account_type']) !== "CREDIT")) {
                echo json_encode(["title" => "Not Found", "message" => "Account Type must be DEBIT or CREDIT", "theme" => "error"]);
                return;
            }

            $check_por = $this->db->like('receipt_no', $data['por_no'])->get('purchase_order_receipts')->row();
            if (!empty($check_por) && $check_por->status == "1") {
                $this->db->trans_rollback();
                echo json_encode(["title" => "Caution", "message" => $data['por_no'] . " has been processed previously. (Closed)", "theme" => "warning"]);
                return;
            }

            // Prepare data
            $trans_date = $data['trans_date'];
            $supplier_id = $get_supplier->id;
            $journal_type_id = $get_journal_type->id;
            $payment_term = $get_supplier->payment_term ?? $data["payment_term"];
            $vat_rate_supplier = $get_supplier->vat ?? 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($trans_date)));

            // PI number
            if (empty($data["number"]) || $data["number"] == "-") {
                $datenow = "PI-" . date("Ymd", strtotime($trans_date));
                $sqlGetID = $this->db->select_max('number', 'kode')->like('number', $datenow, 'after')->get('purchase_invoices')->row();
                $urutan = ($sqlGetID && $sqlGetID->kode) ? (int) substr($sqlGetID->kode, -4) : 0;
                $urutan++;
                $autoID = sprintf("%04s", $urutan);
                $number = $datenow . "-" . $autoID;
                $data["number"] = $number;
            } else {
                $number = $data["number"];
            }

            // Supplier invoice number
            $invoice_no = $data["invoice_no"];
            if (empty($invoice_no)) {
                $invoice_no = ($data['type'] == "dp") ? "INVWDP-" . time() : "INVTMP-" . time();
            }

            // Get data PI dari POR atau data mentah
            $records = [];
            if (!empty($data["por_no"]) && $data["por_no"] !== "-") {
                // Jika POR ada
                $por_no = $data['por_no'];

                $this->db->select("a.receipt_no as por_no, a.po_no, c.id as item_rm_id, c.number as item_number, c.name as item_name, c.uom, b.currency, e.item_supplier as supplier_product,
                    a.qty_receipt as qty, f.price, f.discount, h.account_number, i.account_name, COALESCE(g.middle,1) as rate");
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
                $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_rm_id', 'left');
                $this->db->join('exchange_rates g', "g.start_date <= '{$trans_date}' AND g.end_date >= '{$trans_date}' AND g.currency_from = b.currency",'left');
                $this->db->join('item_familys h', "c.item_family_id = h.id", 'left');
                $this->db->join('account_coa i', "h.account_number = i.account_number", 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where_in('a.receipt_no', $por_no);
                $this->db->order_by('a.receipt_no', 'asc');
                $records = $this->db->get()->result_array();

                if (empty($records)) {
                    $records[] = $data;
                }

            } else {
                // Jika POR tidak ada atau Jika POR = "-", maka gunakan data dari input
                $records[] = $data;
            }
            
            // Get Supplier Product
            $this->db->select('rm.number, s.item_supplier, s.item_rm_id, s.price, s.currency');
            $this->db->from('supplier_items s');
            $this->db->join('item_rm rm', 's.item_rm_id = rm.id');
            $this->db->where('s.supplier_id', $data['supplier_id']);
            $this->db->like('rm.number', $data['item_no']);
            $this->db->where('rm.status', "0");
            $supplier_product = $this->db->get()->row();

            $all_data_final = [];
            $total_sub_all = 0;
            $total_discount_all = 0;

            foreach ($records as $record) {
                $price = (float)($record['price'] ?? $supplier_product->price ?? 0);
                $qty = (float)($record['qty'] ?? 0);
                $discount_rate = (float)($record['discount'] ?? 0);

                // Get Rate
                $currency = $record['currency'];
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($trans_date)));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

                if ($currency != "IDR") {
                    if ($exchange) {
                        $rate = $exchange->middle;
                    } else {
                        $rate = (float)($record['rate'] ?? 1);
                    }
                } else {
                    $rate = 1;
                }

                $subtotal_item = $price * $qty;
                $discount_item = $subtotal_item * ($discount_rate / 100);
                $total_item = $subtotal_item - $discount_item;
                $total_idr_item = $total_item * $rate;

                $total_sub_all += $subtotal_item;
                $total_discount_all += $discount_item;

                $data_per_item = [
                    "supplier_id"       => $supplier_id,
                    "journal_type_id"   => $journal_type_id,
                    "category_id"       => $data["category_id"],
                    "item_rm_id"        => $record['item_rm_id'] ?? $supplier_product->item_rm_id ?? "-",
                    "number"            => $number,
                    "supplier_product"  => $record["supplier_product"] ?? $supplier_product->item_supplier ?? "-",
                    "trans_date"        => $trans_date,
                    "due_date"          => $due_date,
                    "invoice_no"        => $invoice_no,
                    "payment_term"      => $payment_term,
                    "currency"          => $record['currency'] ?? $data["currency"],
                    "rate"              => $rate,
                    "po_no"             => $record["po_no"] ?? "-",
                    "por_no"            => $record["por_no"] ?? "-",
                    "item_no"           => $record['item_number'] ?? $data['item_no'] ?? "-",
                    "item_name"         => $record['item_name'] ?? $data['item_name'] ?? "-",
                    "qty"               => $qty,
                    "uom"               => $record['uom'] ?? $data['uom'] ?? "-",
                    "price"             => $price,
                    "discount"          => $discount_rate,
                    "total"             => $total_item,
                    "total_idr"         => $total_idr_item,
                    "faktur_no"         => $data["faktur_no"] ?? null,
                    "account_number"    => $record['account_number'] ?? $data["account_number"],
                    "account_type"      => $data["account_type"],
                    "type"              => $data["type"] ?? null,
                    "remarks"           => $data["remarks"] ?? "PI via Upload",
                    "status"            => 0,
                ];
                $all_data_final[] = $data_per_item;
            }

            // Calculate DPP, VAT, Third Parties, etc
            $total_dpp = round($total_sub_all * (11/12), 2);
            $total_vat = round($total_dpp * ($vat_rate_supplier / 100), 2);
            $total_pph = 0;
            if (isset($data['pph_23']) && $data['pph_23'] == 'yes') { // Get PPH masih dari excel bukan dari Journal Setups
                $total_pph = round($total_dpp * (2/100), 2);
            }
            $grand_total = (($total_dpp - $total_discount_all) + $total_vat) - $total_pph;

            // assign to variable
            foreach ($all_data_final as &$item) {
                $item["total_discount"] = $total_discount_all;
                $item["total_vat"] = $total_vat;
                $item["total_dpp"] = $total_dpp;
                $item["total_pph"] = $total_pph;
                $item["total_dp"]  = 0;
                $item["taxes"]     = $vat_rate_supplier;
                $item["pph"]       = ($total_pph > 0) ? 2 : 0;

                $send = $this->crud->create('purchase_invoices', $item);
            }
            unset($item);

            // Update status POR/PO Others
            $records_to_update = $this->db->select('*')->from('purchase_order_receipts')->where_in('receipt_no', $data['por_no'])->get()->result_array();
            foreach ($records_to_update as $record) {
                if ($record['receipt_no'] !== "-") {
                    if ($data['type'] !== "dp") {
                        $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => $record['receipt_no'], "po_no" => $record['po_no'], "item_rm_id" => $record['item_rm_id'], "supplier_id" => $supplier_id], ["status" => 1]);
                    }
                } else {
                    $update = $this->crud->update('purchase_order_others', ["po_no" => $record['po_no'], "item_rm_id" => $record['item_rm_id'], "supplier_id" => $supplier_id], ["status" => 1]);
                    
                }
            }

            // Mapping PI Journals
            $grouped_journals = [];
            foreach ($all_data_final as $row) {
                $account_number = $row['account_number'];
                $account_name = $row['account_name'] ?? ($this->db->select('account_name')->from('account_coa')->where('account_number', $account_number)->get()->row()->account_name ?? 'N/A');
                
                if (!isset($grouped_journals[$account_number])) {
                    $grouped_journals[$account_number] = [
                        "number" => $data['number'], 
                        "account_number" => $account_number, 
                        "account_name" => $account_name, 
                        "debit" => 0, 
                        "credit" => 0, 
                        "flag" => null
                    ];
                }
                
                if (strtolower($row["account_type"]) === "debit") {
                    $grouped_journals[$account_number]["debit"] += $row['total'];
                } elseif (strtolower($row["account_type"]) === "credit") {
                    $grouped_journals[$account_number]["credit"] += $row['total'];
                }
            }

            $vatIn = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '170.170.00')->get()->row_array();
            if ($vatIn && $total_vat > 0) {
                $account_number = $vatIn['account_number'];
                if (!isset($grouped_journals[$account_number])) {
                    $grouped_journals[$account_number] = ["number" => $data['number'], "account_number" => $account_number, "account_name" => $vatIn['account_name'], "debit" => 0, "credit" => 0, "flag" => null];
                }
                $grouped_journals[$account_number]["debit"] += $total_vat;
            }

            $thirdParties = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '210.120.00')->get()->row_array();
            if ($thirdParties) {
                $account_number = $thirdParties['account_number'];
                if (!isset($grouped_journals[$account_number])) {
                    $grouped_journals[$account_number] = ["number" => $data['number'], "account_number" => $account_number, "account_name" => $thirdParties['account_name'], "debit" => 0, "credit" => 0, "flag" => null];
                }
                $grouped_journals[$account_number]["credit"] += $grand_total;
            }

            if ($total_pph > 0) {
                $pph23 = $this->db->select('account_number, account_name')->from('account_coa')->where('account_number', '210.110.10')->get()->row_array();
                if ($pph23) {
                    $account_number = $pph23['account_number'];
                    if (!isset($grouped_journals[$account_number])) {
                        $grouped_journals[$account_number] = ["number" => $data['number'], "account_number" => $account_number, "account_name" => $pph23['account_name'], "debit" => 0, "credit" => 0, "flag" => null];
                    }
                    $grouped_journals[$account_number]["credit"] += $total_pph;
                }
            }
            
            $data_journals = [];
            $flag = 1;
            foreach ($grouped_journals as $journal) {
                $journal["flag"] = $flag++;
                $data_journals[] = $journal;
            }
            
            // Insert or Update Journals
            foreach ($data_journals as $journal_row) {
                $unique_condition = [
                    "number"         => $journal_row['number'],
                    "account_number" => $journal_row['account_number'],
                    "flag"           => $journal_row['flag']
                ];

                $existing_journal = $this->crud->read('purchase_invoice_journals', [], $unique_condition);
                if (!empty($existing_journal)) {
                    // Ambil data pertama dari hasil query
                    $existing_data = $existing_journal;
                    
                    // Ambil nilai debit dan credit yang sudah ada (pastikan tipe data numerik)
                    $existing_debit  = (float) $existing_data->debit;
                    $existing_credit = (float) $existing_data->credit;
                    
                    // Ambil nilai price baru (pastikan tipe data numerik)
                    $new_debit      = (float) $journal_row['debit'];
                    $updated_debit  = $existing_debit + $new_debit;
                    $update_data    = ['debit' => $updated_debit];

                    $new_credit     = (float) $journal_row['credit'];
                    $updated_credit = $existing_credit + $new_credit;
                    $update_data    = ['credit' => $updated_credit];
                    
                    // Lakukan UPDATE dengan data yang sudah dijumlahkan
                    $sendjournal = $this->db->where($unique_condition)->update('purchase_invoice_journals', $update_data); 
                } else {
                    // Jika data belum ada, lakukan INSERT (CREATE)
                    $sendjournal = $this->crud->create('purchase_invoice_journals', $journal_row);
                }
            }
            
            // Jika semua operasi sukses, commit transaksi
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(["title" => "Error", "message" => "Failed to insert Data! Error database", "theme" => "error"]);
                return;
            }

            // $this->db->trans_commit();
            // echo $send;
            if (!empty($send)) {
                $this->db->trans_commit();
                echo $send;
            } else {
                $this->db->trans_rollback();
                echo json_encode(["title" => "Error", "message" => "Failed to insert Data!", "theme" => "error"]);
            }

        } catch (Exception $e) {
            // Jika ada kesalahan, rollback transaksi dan kirim respons error
            $this->db->trans_rollback();
            echo json_encode(["title" => "Error", "message" => $e->getMessage(), "theme" => "error"]);
        }
    }
    
    // ------- PRINT FUNCTIONS ---------
    public function print_invoicing($invoice, $is_hyperlink = null) // is_hyperlink for Show GL and link each transaction (Report_general_ledgers)
    {
        $invoice_no = base64_decode($invoice);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.number', $invoice_no);
        $this->db->order_by('a.trans_date', 'DESC');
        //$this->db->group_by('a.number');
        $total_invoice = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($total_invoice) / $rows);
        //Generate QRcode
        $this->createQrcode(@$invoice_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $invoice_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>';
            
        // Use Default HTML if not show GL and Hyperlink each transaction (Report_general_ledgers)
        if (empty($is_hyperlink) && $is_hyperlink !== 'GL') {
            $html .= '<div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                <div class="print">';
        }
        
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $grand_total_all = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, a.item_no as item_number, a.item_name, a.remarks');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->like('a.number', $invoice_no);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="450" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $invoice_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u style="padding:5px;">PURCHASE INVOICING</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Supplier Name</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Supplier Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['invoice_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Purchase Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$invoice_no . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Date</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['trans_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Payment Term</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['payment_term'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Payment Due</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['due_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Remarks</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['remarks'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:100%; text-align: right; font-size:12px;">Page '.$hal.'/'.$page.'</div>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">POR No</th>
                                        <th rowspan="2">PO No</th>
                                        <th rowspan="2">Product No</th>
                                        <th rowspan="2">Product Name</th>
                                        <th rowspan="2">Uom</th>
                                        <th rowspan="2">Qty</th>
                                        <th colspan="3">Original Currency</th>
                                        <th colspan="2">Local Currency</th>
                                    </tr>
                                    <tr>
                                        <th>Currency</th>
                                        <th>Unit Price</th>
                                        <th>Amount</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                    </tr>';
            $grand_total = 0;
            $grand_total_local = 0;
            foreach ($records as $record) {
                $currency = $record['currency'];

                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record['trans_date'])));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

                if ($currency != "IDR") {
                    if ($exchange) {
                        $price = $exchange->middle;
                    } else {
                        $price = 0;
                    }
                } else {
                    $price = 1;
                }
                
                $amount = ($record['total'] * $price);
                if($record['account_type'] == "DEBIT"){
                    $grand_total += $record['total'];
                    $grand_total_all += $record['total'];
                    $grand_total_local += $amount;
                }else{
                    $grand_total -= $record['total'];
                    $grand_total_all -= $record['total'];
                    $grand_total_local -= $amount;
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['por_no'] . '</td>
                                <td>' . $record['po_no'] . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['qty']), 2) . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['price'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['total'], 2) . '</td>
                                <td>IDR</td>
                                <td style="text-align:right;">' . @number_format($amount, 2) . '</td>
                            </tr>';
                $no++;
            }

            $html .= '  <tr>
                            <th colspan="9" style="text-align:right">TOTAL</th>
                            <th style="text-align:right">'.number_format($grand_total, 2).'</th>
                            <th></th>
                            <th style="text-align:right">'.number_format($grand_total_local, 2).'</th>
                        </tr>
                    </table>
                </div>
            </div>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            
            $hal++;
        }

        $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM purchase_invoice_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.number = '$invoice_no' ORDER BY a.flag ASC");

        $html .= '<br><br>
                <div style="width:100%;">
                    <div style="width:50%; float:left;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td style="font-weight:bold;">Account No</td>
                                <td style="font-weight:bold;">Account Name</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
            $total_debit = 0;
            $total_credit = 0;
            foreach ($journals as $journal) {
                $total_debit += $journal->debit;
                $total_credit += $journal->credit;

                $html .= '  <tr>
                                <td>' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                            </tr>';
            }

            $html .= '      <tr>
                                <td colspan="2">Balance</td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>
                    <div style="width:30%; float:left;">
                        &nbsp;
                    </div>
                    <div style="width:20%; float:left;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td style="font-weight:bold;">Sub Total</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format($grand_total_all, 2) . '</td>
                            </tr>
                             <tr>
                                <td style="font-weight:bold;">DPP</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_dpp'], 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">VAT</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_vat'], 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">PPH</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_pph'], 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Grand Total</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(((@$grand_total_all + $records[0]['total_vat']) - $records[0]['total_pph']), 2) . '</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <table style="width:100%; margin-top: 150px; font-size:12px;">
                    <tr>
                        <td style="text-align:center; font-weight:bold;">Prepared By</td>
                        <td style="text-align:center; font-weight:bold;">Checked By</td>
                        <td style="text-align:center; font-weight:bold;">Approved By</td>
                        <td style="text-align:center; font-weight:bold;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">' . $this->session->name . '</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Purchasing</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>';

        if (empty($is_hyperlink) && $is_hyperlink !== 'GL') {
            $html .= "</div></div><script>window.print()</script></body>";
            
        } else {
            $html .= "</div> </body>";
        }

        die($html);
    }

    public function print($option = "") 
    {    
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_category_id = base64_decode($this->input->get('filter_category_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        //GET HEADER - query disamakan dengan function datatables()
        $this->db->select("a.*, e.number as gl_no, b.name as supplier_name, c.name as item_category_name, d.name as journal_type_name, 
            a.invoice_no as status_invoice,
            SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) as total_sub,
            ((SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) + a.total_vat) - a.total_pph) as total_grand");
        $this->db->select('GROUP_CONCAT(DISTINCT REPLACE(a.por_no, " ", "") SEPARATOR ",") as por_numbers');
        $this->db->select("'view' as details");
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_categories c', 'a.category_id = c.id', 'left');
        $this->db->join('journal_types d', 'a.journal_type_id = d.id', 'left');
        // $this->db->join("journal_postings e", 'a.number = e.document_no', 'left');
        $this->db->join('(SELECT document_no, MIN(number) AS number FROM journal_postings GROUP BY document_no) e','a.number = e.document_no','left');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        } else {
            $this->db->where("a.trans_date between '$date_from' and '$date_to'");
        }
        $this->db->like('a.category_id', $filter_category_id);
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.number');
        $records = $this->db->get()->result_array();

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

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
                                <small>REPORT PURCHASE INVOICING</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th colspan="2">Grand Total</th>
                </tr>';
        
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            //GET DETAIL - query disamakan dengan reads()
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.deleted', 0);
            // $this->db->where('a.status', 0);
            $this->db->where('a.number', $number);
            $this->db->order_by('a.por_no', 'asc');
            $this->db->order_by('a.item_no', 'asc');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 2, ",", ".") . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 2, ",", ".") . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 2, ",", ".") . '</td>
                            <td colspan="2" style="text-align:right;">' . number_format($data['total_grand'], 2, ",", ".") . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="12" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>POR No</th>
                            <th>PO No</th>
                            <th>Created By</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                            <th>Credit/Debit</th>
                        </tr>';
            
            foreach ($details as $detail) {
                $html .= '<tr>
                            <td></td>
                            <td>' . $detail['por_no'] . '</td>
                            <td>' . $detail['po_no'] . '</td>
                            <td >' . $detail['created_by'] . '</td>
                            <td>' . $detail['item_no'] . '</td>
                            <td>' . $detail['item_name'] . '</td>
                            <td style="text-align:right">' . number_format($detail['qty'], 2, ",", ".") . '</td>
                            <td>' . $detail['uom'] . '</td>
                            <td>' . $detail['currency'] . '</td>
                            <td style="text-align:right">' . number_format($detail['price'], 2, ",", ".") . '</td>
                            <td style="text-align:right">' . number_format($detail['total'], 2, ",", ".")  . '</td>
                            <td style="text-align:center">' . $detail['account_type'] . '</td>
                        </tr>';
            }

            $html .= '  <tr>
                            <td colspan="12">&nbsp;</td>
                        </tr>';
            $no++;
        }

        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }

    public function print_backup($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_category_id = base64_decode($this->input->get('filter_category_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, SUM(a.total) as total_sub, (SUM(a.total) + a.total_vat - a.total_pph) as total_grand');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('a.category_id', $filter_category_id);
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.number');
        $records = $this->db->get()->result_array();

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
                                <small>REPORT PURCHASE INVOICING</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th>Grand Total</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select('a.*');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.por_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_grand'], 4) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>POR No</th>
                            <th>PO No</th>
                            <th>Created By</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['por_no'] . '</td>
                                <td>' . $detail['po_no'] . '</td>
                                <td >' . $detail['created_by'] . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 4)  . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }

    public function printDetail($option)
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_details_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_category_id = base64_decode($this->input->get('filter_category_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
            $period_due =  "-";
        } elseif ($filter_type == "PAY") {
            $period_due =  $filter_due_date_from . ' to ' . $filter_due_date_to;
            $periode =  "-";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, c.name as journal_type_name, d.total_sub, e.account_name');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id', 'left');
        $this->db->join("(SELECT number, SUM(total) as total_sub FROM purchase_invoices GROUP BY number) d", 'a.number = d.number');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('a.category_id', $filter_category_id);
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();

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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT PURCHASE INVOICING DETAIL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $periode . '</td>
                </tr>
                <tr>
                    <td width="100">Payment Due</td>
                    <td width="20">:</td>
                    <td>' . $period_due . '</td>
                </tr>
            </table>
            <br><br>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Payment Due</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th>Grand Total</th>
                    <th>POR No</th>
                    <th>PO No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Uom</th>
                    <th>Currency</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                    <th>Account No</th>
                    <th>Account Name</th>
                    <th>Debit/Credit</th>
                    <th>Created By</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . $data['total_sub'] . '</td>
                            <td style="text-align:right;">' . $data['total_vat'] . '</td>
                            <td style="text-align:right;">' . $data['total_pph'] . '</td>
                            <td style="text-align:right;">' . ($data['total_sub'] + $data['total_vat'] - $data['total_pph']) . '</td>
                            <td>' . $data['por_no'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['price'] . '</td>
                            <td>' . $data['total'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['account_type'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printJournal($option)
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_journals_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_category_id = base64_decode($this->input->get('filter_category_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
            $period_due =  "-";
        } elseif ($filter_type == "PAY") {
            $period_due =  $filter_due_date_from . ' to ' . $filter_due_date_to;
            $periode =  "-";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.invoice_no, b.trans_date, b.due_date, b.payment_term, b.currency, c.name as supplier_name, d.name as journal_type_name, e.account_name');
        $this->db->from('purchase_invoice_journals a');
        $this->db->join('purchase_invoices b', 'a.number = b.number');
        $this->db->join('suppliers c', 'b.supplier_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("b.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("b.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('b.category_id', $filter_category_id);
        $this->db->like('b.number', $filter_purchase_invoice);
        $this->db->like('b.por_no', $filter_purchase_receipt);
        $this->db->like('b.po_no', $filter_purchase_order);
        $this->db->like('b.supplier_id', $filter_supplier);
        $this->db->like('b.invoice_no', $filter_status_supplier);
        $this->db->like('b.invoice_no', $filter_invoice_no);
        $this->db->like('b.status', $filter_status);
        $this->db->group_by('a.number');
        $this->db->group_by('a.account_number');
        $this->db->order_by('b.supplier_id', 'ASC');
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.flag', 'ASC');
        $records = $this->db->get()->result_array();

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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT PURCHASE INVOICING JOURNALS</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $periode . '</td>
                </tr>
                <tr>
                    <td width="100">Payment Due</td>
                    <td width="20">:</td>
                    <td>' . $period_due . '</td>
                </tr>
            </table>
            <br><br>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Payment Due</th>
                    <th>Payment Term</th>
                    <th>Currency</th>
                    <th>Account No</th>
                    <th>Account Name</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['debit'] . '</td>
                            <td>' . $data['credit'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    function exportAccurate($id) {

        $ids = explode(',', base64_decode($id));

        // Buffer output
        ob_start();
    
        // Set headers for CSV output
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=csv_to_accurate_purchase_invoicing" . date("Ymd") . ".csv");
    
        // Initialize output
        $output = fopen('php://output', 'w');
    
        // Write the CSV headers once
        fputcsv($output, ['HEADER', 'No Form', 'No Faktur', 'Tgl Faktur', 'No Pemasok', 'Alamat Faktur', 'Kena PPN', 'Total Termasuk PPN', 'Nomor Faktur Pajak', 'Tagihan Dimuka', 
        'Diskon Faktur (%)', 'Diskon Faktur (Rp)', 'Keterangan', 'Nama Cabang', 'Pengiriman', 'Tgl Pengiriman', 'FOB', 'Syarat Pembayaran', 'Bank Pembayaran', 'Nilai Pembayaran', 
        'Kustom Karakter 1', 'Kustom Karakter 2', 'Kustom Karakter 3', 'Kustom Karakter 4', 'Kustom Karakter 5', 'Kustom Karakter 6', 'Kustom Karakter 7', 'Kustom Karakter 8', 'Kustom Karakter 9', 'Kustom Karakter 10',
        'Kustom Angka 1', 'Kustom Angka 2', 'Kustom Angka 3', 'Kustom Angka 4', 'Kustom Angka 5', 'Kustom Angka 6', 'Kustom Angka 7', 'Kustom Angka 8', 'Kustom Angka 9', 'Kustom Angka 10',
        'Kustom Tanggal 1', 'Kustom Tanggal 2', 'Nomor Akun Hutang', 'Nomor Bukti', 'Tgl Faktur Pajak']);
        fputcsv($output, ['ITEM', 'Kode Barang', 'Nama Barang', 'Kuantitas', 'Satuan', 'Harga Satuan', 'Diskon Barang (%)', 'Diskon Barang (Rp)', 'Catatan Barang', 'Nama Gudang', 'Nama Dept Barang', 'No Proyek Barang', 
        'Kustom Karakter 1', 'Kustom Karakter 2', 'Kustom Karakter 3', 'Kustom Karakter 4', 'Kustom Karakter 5', 'Kustom Karakter 6', 'Kustom Karakter 7', 'Kustom Karakter 8', 'Kustom Karakter 9', 'Kustom Karakter 10', 'Kustom Karakter 11', 'Kustom Karakter 12', 'Kustom Karakter 13', 'Kustom Karakter 14', 'Kustom Karakter 15', 
        'Kustom Angka 1', 'Kustom Angka 2', 'Kustom Angka 3', 'Kustom Angka 4', 'Kustom Angka 5', 'Kustom Angka 6', 'Kustom Angka 7', 'Kustom Angka 8', 'Kustom Angka 9', 'Kustom Angka 10', 
        'Kustom Tanggal 1', 'Kustom Tanggal 2', 'Kategori Keuangan 1', 'Kategori Keuangan 2', 'Kategori Keuangan 3', 'Kategori Keuangan 4', 'Kategori Keuangan 5', 'Kategori Keuangan 6', 'Kategori Keuangan 7', 'Kategori Keuangan 8', 'Kategori Keuangan 9', 'Kategori Keuangan 10']);
        fputcsv($output, ['EXPENSE', 'No Biaya', 'Nama Biaya', 'Nilai Biaya', 'Catatan Biaya', 'Nama Dept Biaya', 'No Proyek Biaya', 
        'Kustom Tanggal 1', 'Kustom Tanggal 2', 'Kategori Keuangan 1', 'Kategori Keuangan 2', 'Kategori Keuangan 3', 'Kategori Keuangan 4', 'Kategori Keuangan 5', 'Kategori Keuangan 6', 'Kategori Keuangan 7', 'Kategori Keuangan 8', 'Kategori Keuangan 9', 'Kategori Keuangan 10']);
        //Config
        $config = $this->db->select('*')
                           ->from('config')
                           ->get()
                           ->row();
        
        // Fetch FK and LT data (ensure no duplicates)
        $this->db->select('a.faktur_no, a.trans_date, b.id as supp_id, b.name as supp_name, b.address, a.number as invoice_number, a.total_vat, a.total, a.taxes, a.invoice_no');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.number', $ids); // Use where_in to handle array of IDs
        $this->db->group_by('a.number'); // Group by invoice number to avoid duplicates
        $invoiceRecords = $this->db->get()->result_array();
    
        // Write HEADER and LT rows
        foreach ($invoiceRecords as $invoice) {

            if($invoice['total_vat'] > 0 ){
                $ppn = 'Ya';
            }else{
                $ppn = 'Tidak';
            }

            // HEADER row
            fputcsv($output, [
                'HEADER', $invoice['invoice_number'], $invoice['faktur_no'], date('d/m/Y', strtotime($invoice['trans_date'])), $invoice['supp_id'], $invoice['address'], $ppn,'Tidak', $invoice['faktur_no'], 'Ya', 
                '', '', $invoice['invoice_no'], '', '', '', '', '', '', '', 
                '', '', '', '', '', '', '', '', '', '', 
                '', '', '', '', ''
            ]);
    
            // Fetch items (OF rows) for this invoice
            $this->db->select('e.id as item_id, e.number as item_number, e.name as item_name, a.price, a.qty, a.total, a.discount, e.uom');
            $this->db->from('purchase_invoices a');
            $this->db->join('item_rm e', 'a.item_rm_id = e.id','left');
            $this->db->where('a.number', $invoice['invoice_number']); // Mengambil item untuk invoice tertentu
            $itemRecords = $this->db->get()->result_array();

            // $total_sub = 0; // Inisialisasi total_sub

            // Menulis baris ITEM (satu per item)
            foreach ($itemRecords as $item) {
                // // Hitung total untuk setiap item (price * qty)
                // $item_total = $item['price'] * $item['qty'];
                
                // // Tambahkan item_total ke total_sub
                // $total_sub += $item_total;
                
                // Menampilkan detail item di CSV
                fputcsv($output, [
                    'ITEM',$item['item_id'], $item['item_number'], intval($item['qty']), $item['uom'], intval($item['price']), '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', '', '',
                ]);
            }

            // EXPENSE row
            // fputcsv($output, [
            //     'EXPENSE', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            // ]);

            // // Menghitung total VAT dan total grand berdasarkan total_sub
            // $vat = $invoice['taxes'];
            // $total_vat = $total_sub * ($vat / 100);
            // $total_grand = $total_sub + $total_vat;

            // // Menulis SUB TOTAL, VAT, dan GRAND TOTAL ke CSV
            // fputcsv($output, [
            //     '', '', '', '', '', '', '', 'SUB TOTAL', $total_sub, '', '', '', '', '', '', '', '', '', ''
            // ]);

            // fputcsv($output, [
            //     '', '', '', '', '', '', '', 'VAT', $total_vat, '', '', '', '', '', '', '', '', '', ''
            // ]);

            // fputcsv($output, [
            //     '', '', '', '', '', '', '', 'GRAND TOTAL', $total_grand, '', '', '', '', '', '', '', '', '', ''
            // ]);

        }
    
        // Close output
        fclose($output);
        ob_end_flush();
    }
}
