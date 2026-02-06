<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

// Memastikan namespace dikenali
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 * @property Convertcurrency $convertcurrency
 */
class Sales_invoices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->library('Convertcurrency');
        $this->load->model('crud');

        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/sales_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)//untuk memanggil data saat update
    {
        $number = base64_decode($number);
        $this->db->select('a.*, c.id as item_fg_id, f.account_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.type = e.number', 'left');
        $this->db->join('account_coa f', 'a.account_number = f.account_number', 'left');
        // $this->db->join('customer_items e', 'b.id = e.customer_id and c.id = e.item_fg_id');
        // $this->db->join('sales_orders f', 'a.sales_order_no = f.number and b.id = f.customer_id and c.id = f.item_fg_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->order_by('a.delivery_note_no', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readAccountDivision() 
    {
        $customer_id = $this->input->get('customer_id');
        $account_division = $this->input->get('division');
        
        $data = [];

        $getAccount = $this->db->get_where('customer_account_numbers', ['division' => $account_division, 'customer_id' => $customer_id])->row();
        if (!empty($getAccount)) {
            $data = $getAccount;
        }
        echo json_encode($data);
    }

    // public function readExchangeRates()
    // {
    //     $customer_id = $this->input->get('customer_id');

    //     $records = $this->crud->query("SELECT b.selling
    //         FROM customers a 
    //         JOIN exchange_rates b ON a.currency = b.currency_from and b.currency_to = 'IDR'
    //         WHERE a.id = '$customer_id'
    //         GROUP BY a.currency 
    //         ORDER BY b.created_date desc");
    //     echo json_encode($records);
    // }

    public function readExchangeRates()
    {
        $currency = $this->input->get('currency');
        $trans_date = $this->input->get('trans_date');//sales_invoice_date

        $records = $this->crud->query("SELECT COALESCE((
            SELECT middle 
            FROM exchange_rates
            WHERE currency_from = '$currency' 
            AND currency_to = 'IDR' 
            AND '$trans_date' BETWEEN start_date AND end_date
            ORDER BY created_date DESC
            LIMIT 1
        ), 1) AS middle");
        echo json_encode($records);
    }

    // Get Rate (block hijau ketika klik button Add to Journal)
    function readExchangeRate()
    {
        $trans_date = $this->input->post('trans_date');
        $currency = $this->input->post('currency');

        $this->db->select('middle, currency_from, currency_to');
        $this->db->from('exchange_rates');
        $this->db->where('currency_from', $currency);
        $this->db->where('currency_to', 'IDR');
        $this->db->where("'$trans_date' BETWEEN start_date AND end_date", null, false); // penting: raw SQL

        $query = $this->db->get()->row();

        if ($query) {
            $amount = $query->middle;
            $currency_from = $query->currency_from;
            $currency_to = $query->currency_to;
        } else {
            $amount = 0;
            $currency_from = '-';
            $currency_to = '-';
        }

        echo json_encode([
            'amount' => $amount,
            'label' => "Rate $currency_from to $currency_to: Rp. " . number_format($amount, 2)
        ]);
    }

    // public function readDelivery()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $customer_id = $this->input->get('customer_id');
    //     $records = $this->crud->query("SELECT delivery_note_no, delivery_note_date
    //         FROM delivery_notes
    //         WHERE customer_id = '$customer_id' and `status` = '0' and delivery_note_no like '%$post%'
    //         GROUP BY delivery_note_no 
    //         ORDER BY delivery_note_no asc");
    //     echo json_encode($records);
    // }

    public function readDelivery()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $address_id = $this->input->get('address_id');
        $records = $this->crud->query("SELECT delivery_note_no, delivery_note_date
            FROM delivery_notes
            WHERE address_id = '$address_id' and `status` = '0' and delivery_note_no like '%$post%'
            GROUP BY delivery_note_no 
            ORDER BY delivery_note_no asc");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readDeliveryx()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $address_id = $this->input->get('address_id');
        $records = $this->crud->query("SELECT delivery_note_no, delivery_note_date
            FROM delivery_notes
            WHERE address_id = '$address_id' and `status` = '0' and delivery_note_no like '%$post%'
            GROUP BY delivery_note_no 
            ORDER BY delivery_note_no asc");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readPlant()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get('customer_id');
        $records = $this->crud->query("SELECT plant, address_billing, address, id
            FROM customer_address
            WHERE customer_id = '$customer_id' and plant like '%$post%'
            ORDER BY plant asc");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readPayment()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT bank_name, bank_account
            FROM account_banks
            WHERE `deleted` = '0' and bank_name like '%$post%' and bank_account like '%$post%'
            GROUP BY bank_name 
            ORDER BY bank_name asc");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readDeliverys()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get('customer_id');
        $division_number = $this->input->get('division_number');
        $records = $this->crud->query("SELECT a.delivery_note_no, a.delivery_note_date, b.plant
            FROM delivery_notes a
            LEFT JOIN customer_address b ON a.address_id = b.id
            WHERE a.customer_id = '$customer_id' 
            AND a.status = '0' 
            AND a.delivery_note_date >= '2025-01-01' 
            AND a.trans_type = 'SALES' 
            AND a.delivery_note_no LIKE '%$post%' 
            AND a.division = '$division_number'
            GROUP BY a.delivery_note_no 
            ORDER BY a.delivery_note_date ASC, a.delivery_note_no ASC");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readDeliverys_existing()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get('customer_id');
        $division_number = $this->input->get('division_number');
        $records = $this->crud->query("SELECT a.delivery_note_no, a.delivery_note_date, b.plant
            FROM delivery_notes a
            LEFT JOIN customer_address b ON a.address_id = b.id
            WHERE a.customer_id = '$customer_id' and a.status = '0' AND YEAR(a.delivery_note_date) = 2025 AND a.trans_type = 'SALES' and a.delivery_note_no like '%$post%' and a.division = '$division_number'
            GROUP BY a.delivery_note_no 
            ORDER BY a.delivery_note_date ASC, a.delivery_note_no ASC");

        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    function readDeliveryUpdate()
    {
        $customer_id = $this->input->get('customer_id');
        $division_number = $this->input->get('division_number');
        $search_query_q = isset($_POST['q']) ? $_POST['q'] : ""; 
        
        $arrayDN = [];
        if (!empty($search_query_q)) {
            $arrayDN = array_map('trim', explode(',', $search_query_q));
            $arrayDN = array_filter($arrayDN);
        }

        $filter_year = $this->input->get('filter_year');
        if (empty($filter_year)) {
            $filter_year = date('Y'); // Default ke tahun saat ini jika tidak disediakan
        }

        // Mulai membangun query menggunakan CodeIgniter Active Record
        $this->db->select('a.delivery_note_no, a.delivery_note_date, b.plant');
        $this->db->from('delivery_notes a');
        $this->db->join('customer_address b', 'a.address_id = b.id', 'left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.division', $division_number);
        $this->db->where("YEAR(a.delivery_note_date)", $filter_year); // Gunakan tahun dinamis
        $this->db->where('a.trans_type', 'SALES');

        if (!empty($arrayDN)) {
            $this->db->where_in('a.delivery_note_no', $arrayDN);
        }
        $this->db->group_by('a.delivery_note_no'); 
        $this->db->order_by('a.delivery_note_date ASC, a.delivery_note_no ASC');
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

    public function readFakturCode()
    {
        $id = $this->input->get('id');
        if ($id) {
            $this->db->select('faktur_code');
            $this->db->from('customers');
            $this->db->where('id', $id);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $result = $query->row();
                // Kirim data dalam format JSON yang sesuai
                echo json_encode([['faktur_code' => $result->faktur_code]]);
            } else {
                echo json_encode([]); // Jika tidak ada data
            }
        } else {
            echo json_encode([]); // Jika ID tidak ada
        }
    }

    public function readSalesInvoices()
    {
        $data = $this->crud->query("SELECT DISTINCT `number` FROM sales_invoices WHERE `status` = '0' ORDER BY `number` ASC");
        echo json_encode($data);
    }

    public function readFakturNo()
    {
        $data = $this->crud->query("SELECT DISTINCT faktur_no FROM sales_invoices WHERE status = '0' ORDER BY faktur_no ASC");
        echo json_encode($data);
    }

    public function readDeliveryNote()
    {
        $data = $this->crud->query("SELECT DISTINCT `delivery_note_no` FROM sales_invoices WHERE `status` = '0' ORDER BY `delivery_note_no` ASC");
        echo json_encode($data);
    }

    public function readVoucher()
    {
        $data = $this->crud->query("SELECT DISTINCT `voucher` FROM sales_invoices WHERE `status` = '0' ORDER BY `voucher` ASC");
        echo json_encode($data);
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

    // Autofill Journal Type by Division - Company Name
    public function readJournalTypeByDivision()
    {
        $division = $this->input->post('division');
        $customer_id = $this->input->post('customer_id');

        // Customer
        $customer = $this->db->get_where('customers', ['id' => $customer_id])->row();
        $customer_name = "";
        $normalized_customer_name = "";
        if (!empty($customer)) {
            $customer_name = $customer->name;
            // Menghapus titik (.), koma (,), dan spasi ganda
            $cleaned_name = str_replace(array('.', ',', '  '), '', $customer_name);            
            $normalized_customer_name = strtolower(trim($cleaned_name));
        }

        // Division
        $division_search = $division;
        if ($division == "MTS") {
            $division_search = "MDS";
        }
        $division_search = strtolower($division_search);

        $this->db->select('id, name'); 
        $this->db->from('journal_types');        
        $this->db->like('name', $division_search, 'right');
        if (!empty($normalized_customer_name)) {
            // Hapus "pt " di awal nama (jika ada di database) sebelum like:
            $search_term = str_replace('pt ', '', $normalized_customer_name);    
            $this->db->like('name', $search_term, 'both'); 
        }
        $result = $this->db->get()->row();

        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode([]);
        }
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
                "local_debit"  => "0.00",
                "local_credit" => "0.00",
            );
        }
        }

        echo json_encode($arr);
    }

    public function check_faktur_no()
    {
        $faktur_no = base64_decode($this->input->get('faktur_no'));
        
        // Menggunakan query builder CodeIgniter
        $this->db->select('faktur_no');
        $this->db->from('sales_invoices');
        $this->db->where('faktur_no', $faktur_no);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            echo json_encode(['exists' => true]);
        } else {
            echo json_encode(['exists' => false]);
        }
    }

    // public function readJournalDetail($journal_type_id, $account_number)
    // {

    //     $journal_type_id = base64_decode($journal_type_id);
    //     $account_number = base64_decode($account_number);

    //     $journals = $this->crud->query("SELECT a.*, b.account_name FROM journal_setups a 
    //         JOIN account_coa b ON a.account_number = b.account_number 
    //         WHERE a.account_number = '$account_number' and a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");

    //     foreach ($journals as $journal) {
    //         $arr[] = array(
    //             "account_number" => $journal->account_number,
    //             "account_name" => $journal->account_name,
    //             "account_type" => $journal->status,
    //             "flag" => $journal->flag,
    //         );
    //     }

    //     echo json_encode($arr);
    // }

    public function readJournals($number)
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("sales_invoice_journals", [], ["number" => $number], "", "flag", "asc");
        echo json_encode($reads);
    }

    public function calculateJournal()
    {
        $journals = json_decode(file_get_contents("json/sales_invoice_journals.json"), true);
        $sales_invoices_data = json_decode(file_get_contents("json/sales_invoices.json"), true);
        $arr = [];

        // Jika sales_invoice_journals.json TIDAK KOSONG
        if (count($journals) == 0) {
            $mergedData = [];
            $flag = 1;
            
            foreach ($sales_invoices_data as $jsonData) {
                $account_number = $jsonData["account_number"];
                $account_name = $jsonData["account_name"];
                $total = $jsonData["total"];

                // Inisialisasi entri jika belum ada
                if (!isset($mergedData[$account_number])) {
                    $mergedData[$account_number] = [
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "debit" => 0,
                        "credit" => 0,
                        "flag" => $flag++, 
                    ];
                }

                // Tambahkan nilai ke Debit atau Credit yang sesuai
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number]["debit"] += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number]["credit"] += $total;
                }
            }

            // Ubah hasil penggabungan menjadi array berindeks numerik
            $arr = array_values($mergedData);

        } else {
            // --- Jika Jurnal SUDAH ADA (Membandingkan/Memperbarui) ---
            
            // 1. Agregasi data sales_invoices
            $aggregated_invoices = [];
            foreach ($sales_invoices_data as $jsonData) {
                $account_number = $jsonData["account_number"];
                $total = $jsonData["total"];
                
                if (!isset($aggregated_invoices[$account_number])) {
                    $aggregated_invoices[$account_number] = ["debit" => 0, "credit" => 0];
                }

                if ($jsonData['account_type'] == "DEBIT") {
                    $aggregated_invoices[$account_number]["debit"] += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $aggregated_invoices[$account_number]["credit"] += $total;
                }
            }

            // 2. Loop melalui jurnal yang sudah ada untuk memperbarui nilainya
            foreach ($journals as $journal) {
                $account_number = $journal['account_number'];
                $total_debit = $journal['debit'];
                $total_credit = $journal['credit'];
                
                // Cari total baru dari aggregated_invoices dan lakukan overwrite
                if (isset($aggregated_invoices[$account_number])) {
                    $total_debit = $aggregated_invoices[$account_number]['debit'];
                    $total_credit = $aggregated_invoices[$account_number]['credit'];
                    
                    // Hapus dari aggregated_invoices setelah diproses
                    unset($aggregated_invoices[$account_number]); 
                }
                
                // Tambahkan ke array hasil
                $arr[] = [
                    "account_number" => $account_number,
                    "account_name" => $journal['account_name'],
                    "debit" => round($total_debit, 4),
                    "credit" => round($total_credit, 4),
                    "flag" => $journal['flag'],
                ];
            }
            
            // 3. Tambahkan akun-akun BARU
            $flag_counter = count($journals) + 1;
            foreach ($aggregated_invoices as $account_number => $data) {
                $account_name = $this->getAccountNameFromInvoiceData($sales_invoices_data, $account_number);

                $arr[] = [
                    "account_number" => $account_number,
                    "account_name" => $account_name,
                    "debit" => round($data['debit'], 4),
                    "credit" => round($data['credit'], 4),
                    "flag" => $flag_counter++,
                ];
            }
        }
        
        // Output akhir hanya berupa array jurnal
        echo json_encode($arr);
    }
    // match account_number between json sales_invoice and journal
    private function getAccountNameFromInvoiceData($sales_invoices_data, $account_number) 
    {
        foreach ($sales_invoices_data as $data) {
            if ($data['account_number'] == $account_number) {
                return $data['account_name'];
            }
        }
        return "N/A";
    }
    // get Exchange Rate
    private function getLocalValue($total, $currency, $trans_date) {
        if ($currency == "IDR") {
            return $total;
        }

        $exchange = $this->db->select('middle')
            ->from('exchange_rates')
            ->where('currency_from', $currency)
            ->where('currency_to', 'IDR')
            ->where("'$trans_date' BETWEEN start_date AND end_date", null, false)
            ->get()->row();

        return $exchange ? ($total * $exchange->middle) : 0;
    }

    public function calculateJournal_existing()
    {
        $journals = json_decode(file_get_contents("json/sales_invoice_journals.json"), true);
        // $sales_invoices = array("1121101", "1121102", "1121103");

        if (count($journals) > 0) {
            // $total_ap = 0;

            // foreach ($journals as $journal) {
            //     if ($journal['debit'] > 0) {
            //         $total_ap += $journal['debit'];
            //     }
            // }

            foreach ($journals as $journal) {
                $jsonDatas = json_decode(file_get_contents("json/sales_invoices.json"), true);
                $total_debit = 0;
                $total_credit = 0;
                $total_ap2 = 0;

                foreach ($jsonDatas as $jsonData) {
                    if($jsonData['account_number'] != "5311006"){
                        if ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "DEBIT") {
                            $total_debit += $jsonData['total'];
                        } elseif ($jsonData['account_number'] == $journal['account_number'] && $jsonData['account_type'] == "CREDIT") {
                            $total_credit += $jsonData['total'];
                        }
                    }
                }

                // foreach ($jsonDatas as $jsonData) {
                //     if ($jsonData['account_type'] == "DEBIT") {
                //         $total_ap2 -= $jsonData['total'];
                //     } elseif ($jsonData['account_type'] == "CREDIT") {
                //         $total_ap2 += $jsonData['total'];
                //     }
                // }

                // if (in_array($journal['account_number'], $sales_invoices)) {
                //     $total_debit += $total_ap + $total_ap2;
                // }

                if ($journal['credit'] > 0) {
                    $total_credit = $journal['credit'];
                }

                if ($journal['debit'] > 0) {
                    $total_debit = $journal['debit'];
                }

                $arr[] = array(
                    "account_number" => $journal['account_number'],
                    "account_name" => $journal['account_name'],
                    "debit" => round($total_debit, 4),
                    "credit" => round($total_credit, 4),
                    "flag" => $journal['flag'],
                );
            }
        } else {
            $jsonDatas = json_decode(file_get_contents("json/sales_invoices.json"), true);
            $total = 0;
            $flag = 1;
            $mergedData = array();
            foreach ($jsonDatas as $jsonData) {
                $account_number = $jsonData["account_number"];
                $account_name = $jsonData["account_name"];
                $account_type = $jsonData["account_type"];
                $total = $jsonData["total"];

                if (isset($mergedData[$account_number])) {
                    // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number]["debit"] += $total;
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number]["credit"] += $total;
                    }
                } else {
                    // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "debit" => $total,
                            "credit" => 0,
                            "flag" => $flag,
                        );
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "debit" => 0,
                            "credit" => $total,
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

    public function number($trans_date, $number)
    {
        $datenow    = "SI-" . $number . date("Ym", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM sales_invoices WHERE `number` like '%$datenow%'");
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

    public function datatablesTemp()
    {
        // $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
        $delivery_note_no = explode(",", base64_decode($this->input->get('delivery_note_no')));
        $division = $this->input->get('division');

        // $this->db->select('a.delivery_note_no, 
        // a.customer_order_no, 
        // d.sales_order_no, 
        // a.item_fg_id, 
        // b.number as item_number, 
        // b.name as item_name, 
        // b.uom, 
        // e.account_number, 
        // e.account_name,
        // e.currency, 
        // d.qty_del as qty, 
        // g.price, 
        // (d.qty_del * g.price) as total, 
        // j.id as si_id');
        // $this->db->from('delivery_orders d');
        // $this->db->join('item_fg b', 'd.item_fg_id = b.id');
        // $this->db->join('customer_items c', 'd.customer_id = c.customer_id and d.item_fg_id = c.item_fg_id','left');
        // $this->db->join('delivery_notes a', 'a.delivery_order_no = d.delivery_order_no and a.item_fg_id = d.item_fg_id');
        // $this->db->join('customers e', 'a.customer_id = e.id');
        // $this->db->join('sales_orders g', 'd.sales_order_no = g.sales_order_no and a.customer_id = g.customer_id and a.item_fg_id = g.item_fg_id and a.customer_order_no = g.customer_order_no');
        // $this->db->join('sales_orders g2', 'd.sales_order_no_rm = g2.sales_order_no and a.customer_id = g2.customer_id and a.item_fg_id = g2.item_fg_id and a.customer_order_no = g2.customer_order_no');
        // $this->db->join('item_familys h', 'b.type = h.number', 'left');
        // $this->db->join('account_coa i', 'h.account_number = i.account_number', 'left');
        // $this->db->join('sales_invoices j', 'a.delivery_note_no = j.delivery_note_no and a.item_fg_id = j.item_fg_id', 'left');
        // // $this->db->where('a.delivery_note_no', $delivery_note_no);
        // $this->db->where_in('a.delivery_note_no', $delivery_note_no);
        // // $this->db->group_by('a.customer_order_no');
        // // $this->db->group_by('d.sales_order_no');
        // // $this->db->group_by('a.item_fg_id');
        // $this->db->order_by('a.delivery_note_no', 'asc');
        // $records = $this->db->get()->result_array();

        // nomor SO tidak di COALESCE(d.sales_order_no, d.sales_order_no_rm) as sales_order_no,

        $this->db->select('a.delivery_note_no, 
            a.customer_order_no,  
            a.division,
            a.customer_id,
            d.sales_order_no,
            d.sales_order_no_rm,
            a.item_fg_id, 
            b.number as item_number, 
            b.name as item_name, 
            b.uom, 
            e.account_number, 
            e.account_name,
            c.currency, 
            d.qty_del as qty, 
            (CASE WHEN g.price IS NOT NULL THEN g.price ELSE g2.price END) as price,
            (d.qty_del * (CASE WHEN g.price IS NOT NULL THEN g.price ELSE g2.price END)) as total,
            j.id as si_id');
            // Dokumentasi : Perubahan pengambilan Currency menjadi ke Customer_items
        $this->db->from('delivery_orders d');
        $this->db->join('item_fg b', 'd.item_fg_id = b.id');
        $this->db->join('customer_items c', 'd.customer_id = c.customer_id and d.item_fg_id = c.item_fg_id', 'left');
        $this->db->join('delivery_notes a', 'a.delivery_order_no = d.delivery_order_no and a.item_fg_id = d.item_fg_id');
        $this->db->join('customers e', 'a.customer_id = e.id');
        $this->db->join('sales_orders g', 'd.sales_order_no = g.sales_order_no and a.customer_id = g.customer_id and a.item_fg_id = g.item_fg_id and a.customer_order_no = g.customer_order_no', 'left');
        $this->db->join('sales_order_rm g2', 'd.sales_order_no_rm = g2.sales_order_no and a.customer_id = g2.customer_id and a.item_fg_id = g2.item_fg_id and a.customer_order_no = g2.customer_order_no', 'left');
        $this->db->join('item_familys h', 'b.type = h.number', 'left');
        $this->db->join('account_coa i', 'h.account_number = i.account_number', 'left');
        $this->db->join('sales_invoices j', 'a.delivery_note_no = j.delivery_note_no and a.item_fg_id = j.item_fg_id', 'left');
        $this->db->group_by('a.customer_order_no');
        $this->db->group_by('d.sales_order_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.id');
        $this->db->where_in('a.delivery_note_no', $delivery_note_no);
        $this->db->order_by('a.delivery_note_no', 'asc');

        $records = $this->db->get()->result_array();

        $obj = [];
        $total_sub = 0;
        foreach ($records as $record) {
            $total_sub += $record['total'];

            $customer_id = $record['customer_id'];
            $qty = $record['qty'];

            $price = 0;
            $amount = 0;

            if (!empty($record['sales_order_no'])) {
                $sales_order_no = $record['sales_order_no'];
            } else {
                $sales_order_no = $record['sales_order_no_rm'];
            }

            // get price and amount by sales_order_no or sales_order_no_rm
            $sales_fg = $this->db->select('sales_order_no, price')->from('sales_orders')->where('sales_order_no', $sales_order_no)->get()->row();
            $sales_rm = $this->db->select('sales_order_no, price')->from('sales_order_rm')->where('sales_order_no', $sales_order_no)->get()->row();
            if ($sales_fg) {
                $price  = $sales_fg->price;
                $amount = $price * $qty;
            } elseif ($sales_rm) {
                $price  = $sales_rm->price;
                $amount = $price * $qty;
            } else {
                $price  = 0;
                $amount = 0;
            }

            // Account Number ambil dari child table customer_account_numbers
            $account_division = $division ?? '';
            $account_number = '';
            $account_name = '';

            $getAccount = $this->db->get_where('customer_account_numbers', [
                'customer_id' => $customer_id, 
                'division' => $account_division
            ])->row();

            if (!empty($getAccount)) {
                $account_number = $getAccount->account_number;
                $account_name = $getAccount->account_name;
                
            } else {
                // Get dari customers jika tidak ada pada customer_account_numbers
                $getDefaultAccount = $this->db->get_where('customers', [
                    'id' => $customer_id, 
                ])->row();
                
                if (!empty($getDefaultAccount)) {
                    $account_number = $getDefaultAccount->account_number;
                    $account_name = $getDefaultAccount->account_name;
                }
            }
            
            $obj[] = array(
                "id" => $record['si_id'],
                "delivery_note_no" => $record['delivery_note_no'],
                "sales_order_no" => $sales_order_no,
                "customer_order_no" => $record['customer_order_no'],
                "item_fg_id" => $record['item_fg_id'],
                "item_no" => $record['item_number'],
                "item_name" => $record['item_name'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "qty" => $record['qty'],
                "price" => $record['price'] ?? $price,
                "total" => $record['total'] ?? $amount,
                "account_number" => $account_number ?? '',
                "account_name"   => $account_name ?? '',
                "account_type" => "CREDIT",
            );
        }

        $arr['rows'] = @$obj;
        $arr['total_sub'] = "$total_sub";
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice'));
        $filter_delivery_note_no = base64_decode($this->input->get('filter_delivery_note_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_faktur_no = base64_decode($this->input->get('filter_faktur_no'));
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
            
            $this->db->query("SET SESSION group_concat_max_len = 100000");

            //Select Query
            $this->db->select('a.*, c.number as gl_no, b.type, b.name as customer_name, GROUP_CONCAT(DISTINCT REPLACE(a.delivery_note_no, " ", "") SEPARATOR ",") as delivery_note_nos');
            $this->db->select("'view' as details");
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('journal_postings c', 'a.number = c.document_no', 'left');
            if ($filter_type == "PID") {
                $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
            } elseif ($filter_type == "PAY") {
                $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
            } else {
                $this->db->where("a.trans_date between '$date_from' and '$date_to'");
            }
            $this->db->like('a.number', $filter_sales_invoice);
            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.faktur_no', $filter_faktur_no);
            $this->db->like('a.status', $filter_status);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.number');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*');
            $this->db->select('c.hs_code');
            $this->db->select("'view' as details");
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'c.id = a.item_fg_id', 'left');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
        }
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request! Method not allowed.");
            return;
        }

        $jsonData = file_get_contents("php://input");
        $post = json_decode($jsonData, true);

        $sales_invoices = $post['dataSi'];
        $sales_invoice_journals = $post['dataJournal'];

        // Validasi total DEBIT dan CREDIT itu Balance
        $balance_array = $post['dataBalance'] ?? null;
        $dataBalance = ($balance_array && is_array($balance_array) && count($balance_array) > 0) ? $balance_array[0] : null;
        if (empty($dataBalance) || !isset($dataBalance['balance_debit']) || !isset($dataBalance['balance_credit'])) {
            echo json_encode(["title" => "Error", "message" => "Balance data is missing or improperly formatted.", "theme" => "error"]);
            return;
        }

        $debit  = (float)$dataBalance['balance_debit'];
        $credit = (float)$dataBalance['balance_credit'];
        if (round($debit, 2) != round($credit, 2)) {
            echo json_encode([
                "title"   => "Error", 
                "message" => "Total Debit ($debit) and Total Credit ($credit) are Not Balance! Transaction blocked.", 
                "theme"   => "error"
            ]);
            return;
        }

        foreach ($sales_invoices as $sales_invoice) {
            $dn_no = $sales_invoice['delivery_note_no'] ?? null;
            $co_no = $sales_invoice['customer_order_no'] ?? null;
            $so_no = $sales_invoice['sales_order_no'] ?? null;
            $item_id = $sales_invoice['item_fg_id'] ?? null;
            
            // --- Jika DN input manual ---
            $is_manual_entry = ($dn_no === "-");            
            if ($is_manual_entry) {
                $where_invoices = [
                    "number"            => $sales_invoice['number'] ?? null,
                    "delivery_note_no"  => $dn_no,
                    "sales_order_no"    => $so_no,
                    "customer_order_no" => $co_no,
                    "item_fg_id"        => $item_id,
                ];
            } else {
                $where_invoices = [
                    "delivery_note_no"  => $dn_no,
                    "sales_order_no"    => $so_no,
                    "customer_order_no" => $co_no,
                    "item_fg_id"        => $item_id,
                ];
            }
            
            $sales_invoices_read = $this->crud->read('sales_invoices', [], $where_invoices);

            if (!empty($sales_invoice['id'])) {
                $send = $this->db->update('sales_invoices', $sales_invoice, ["id" => $sales_invoice['id']]);
            } else {
                if (!$sales_invoices_read) {
                    $send = $this->crud->createNotLog('sales_invoices', $sales_invoice);
                    
                    // Update delivery_notes hanya jika bukan entri manual
                    if (!$is_manual_entry) {
                        $this->db->update('delivery_notes', ["status" => "1"], [
                            "delivery_note_no" => $dn_no,
                            "customer_order_no" => $co_no
                        ]);
                    }
                } else {
                    $send = $this->db->update('sales_invoices', $sales_invoice, $where_invoices);
                }
            }

        }

        foreach ($sales_invoice_journals as $sales_invoice_journal) {
                $sales_invoice_journals_read = $this->crud->read('sales_invoice_journals', [], [
                "number" => $sales_invoice_journal['number'], 
                "account_number" => $sales_invoice_journal['account_number']
            ]);

            if (@$sales_invoice_journal['id'] != "") {
                $send = $this->db->update('sales_invoice_journals', $sales_invoice_journal, ["number" => $sales_invoice_journal['number'], "account_number" => $sales_invoice_journal['account_number']]);
            } else {
                if(!$sales_invoice_journals_read){
                    $send = $this->crud->createNotLog('sales_invoice_journals', $sales_invoice_journal);
                } 
            }
        }

        die($send);
    }

    public function createOld() // Perbaikan Create/Update ketika Input DN Manual Error Create SI Loading
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $sales_invoices = $post['dataSi'];
            $sales_invoice_journals = $post['dataJournal'];

            foreach ($sales_invoices as $sales_invoice) {
                $sales_invoices_read = $this->crud->read('sales_invoices', [], [
                    "delivery_note_no" => $sales_invoice['delivery_note_no'], 
                    "sales_order_no" => $sales_invoice['sales_order_no'],
                    "customer_order_no" => $sales_invoice['customer_order_no'],
                    "item_fg_id" => $sales_invoice['item_fg_id'],
                ]);

                if (!empty($sales_invoice['id'])) {
                    $send = $this->db->update('sales_invoices', $sales_invoice, ["id" => $sales_invoice['id']]);
                } else {
                    if(!$sales_invoices_read){
                        $send = $this->crud->createNotLog('sales_invoices', $sales_invoice);
                        $this->db->update('delivery_notes',["status" => "1"], ["delivery_note_no" => $sales_invoice['delivery_note_no'], "customer_order_no" => $sales_invoice['customer_order_no']]);
                    }
                }
            }

            foreach ($sales_invoice_journals as $sales_invoice_journal) {
                 $sales_invoice_journals_read = $this->crud->read('sales_invoice_journals', [], [
                    "number" => $sales_invoice_journal['number'], 
                    "account_number" => $sales_invoice_journal['account_number']
                ]);

                if (@$sales_invoice_journal['id'] != "") {
                    $send = $this->db->update('sales_invoice_journals', $sales_invoice_journal, ["number" => $sales_invoice_journal['number'], "account_number" => $sales_invoice_journal['account_number']]);
                } else {
                    if(!$sales_invoice_journals_read){
                        $send = $this->crud->createNotLog('sales_invoice_journals', $sales_invoice_journal);
                    } 
                }
            }

            die($send);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        $jsonData2 = $this->input->post('jsonData2');

        // Simpan data JSON ke dalam file
        file_put_contents('json/sales_invoices.json', $jsonData);
        file_put_contents('json/sales_invoice_journals.json', $jsonData2);
    }

    // public function createJournals()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $sales_invoice_journals = $this->crud->read('sales_invoice_journals', [], ["number" => $post['number'], "account_number" => $post['account_number']]);

    //         if (@$sales_invoice_journals->id != "") {
    //             $send = $this->crud->update('sales_invoice_journals', ["number" => $post['number'], "account_number" => $post['account_number']], $post);
    //             echo $send;
    //         } else {
    //             $send = $this->crud->create('sales_invoice_journals', $post);
    //             echo $send;
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('sales_invoices', ["number" => $post['number']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_invoices', array("id" => $data['id']));
        $update = $this->db->update('delivery_notes', ["status" => "0"], ["delivery_note_no" => $data['delivery_note_no'], "item_fg_id"=> $data['item_fg_id']]);
        echo $send;
    }

    public function deleteOnUncheck() 
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_invoices', $data);
        echo $send;

        if ($this->input->method() === 'post') 
        {
            $delivery_note_no = $this->input->post('delivery_note_no');

            if ($data !== null) 
            {
                // check availability first 
                $check_availability = $this->crud->read("sales_invoices", [], ["delivery_note_no" => $delivery_note_no]);
                if (!empty($check_availability)) {
                    $this->db->where_in('delivery_note_no', $delivery_note_no);
                    $result = $this->db->delete('sales_invoices'); // Mengembalikan TRUE/FALSE
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
        $send = $this->crud->delete('sales_invoice_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["number" => $data['number']]);

        // if (count($packing_lists) > 0) {
        //     echo json_encode(array("theme" => "error", "message" => "Please delete packing list first"));
        // } else {
            $send = $this->crud->delete('sales_invoices', ["number" => $data['number']]);
            $this->crud->delete('sales_invoice_journals', ["number" => $data['number']]);
            foreach ($sales_invoices as $sales_invoice) {
                $update = $this->db->update('delivery_notes', ["status" => "0"], ["delivery_note_no" => $sales_invoice->delivery_note_no]);
            }
            echo $send;
        // }
    }

    public function get_delivery_notes()
    {
        $number = $this->input->post('number');

        $this->db->select('delivery_note_no');
        $this->db->from('sales_invoices');
        $this->db->where('number', $number);
        $this->db->where('deleted', 0);

        $query = $this->db->get();
        $notes = [];

        foreach ($query->result() as $row) {
            if (!empty($row->delivery_note_no)) {
                $notes[] = trim($row->delivery_note_no);
            }
        }

        echo json_encode([
            'status' => count($notes) > 0 ? 'success' : 'empty',
            'delivery_note_nos' => $notes
        ]);
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
                echo json_encode(["title" => "Error", "message" => "Failed to upload file.", "theme" => "error"]);
                return;
            }
            
            chmod($target, 0777);
            $file = $target;
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);
            $datas = [];

            for ($i = 4; $i <= $total_row; $i++) {
                $datas[] = [
                    //excel
                    'number'              => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 2)),
                    'customer_id'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 3)),
                    'division_id'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 4)),
                    'trans_date'          => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 5)),
                    'journal_type_code'   => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 6)),
                    'faktur_code'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 7)),
                    'faktur_no'           => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 8)),
                    'bc_no'               => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 9)),
                    'payment_term'        => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 10)),
                    'keterangan_tambahan' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 11)),
                    'cap_fasilitas'       => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 12)),
                    'payment_to'          => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 13)),
                    'remarks'             => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 14)),
                    'delivery_note_no'    => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 15)),
                    'sales_order_no'      => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 16)),
                    'customer_order_no'   => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 17)),
                    'item_no'             => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 18)),
                    'item_name'           => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 19)),
                    'qty'                 => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 20)),
                    'uom'                 => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 21)),
                    'currency'            => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 22)),
                    'price'               => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 23)),
                    'account_number'      => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 24)),
                    'account_type'        => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 25)),
                    'action'              => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 26)),
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
            echo json_encode(["title" => "Error", "message" => "Error upload file! " . $e->getMessage(), "theme" => "error"]);
        } finally {
            // Ensure the temporary file is deleted even if an error occurs
            if (isset($target) && file_exists($target)) {
                unlink($target);
            }
        }
    }
    
    public function uploadclearFailed()
    {
        @unlink('failed/sales_invoices.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/sales_invoices.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/sales_invoices.txt";
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
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error", "message" => "Invalid request method", "theme" => "error"]);
            return;
        }
        
        $data = $this->input->post('data');

        $customers      = $this->crud->read('customers', ["id" => $data['customer_id']]);
        $journal_types  = $this->crud->read('journal_types', ["number" => $data['journal_type_code']]);
        $account_coa    = $this->crud->read('account_coa', ["account_number" => $data['account_number']]);
        $divisions      = $this->crud->read('divisions', ["id" => $data['division_id']]);
        $account_banks  = $this->db->select('*')->from('account_banks')->like('bank_account', trim($data['payment_to']), 'both')->get()->row();
        $item_fg        = $this->db->select('*')->from('item_fg')->like('division_id', $data['division_id'])->like('number', $data['item_no'], 'both')->get()->row();
        $item_fg_id     = !empty($item_fg) ? $item_fg->id : "";
        $sales_invoices = $this->db->select('*')->from('sales_invoices')->where('number', $data['number'])->where('account_number', $data['account_number'])->like('delivery_note_no', $data['delivery_note_no'])->like('sales_order_no', $data['sales_order_no'])->like('price', $data['price'])->like('item_fg_id', $item_fg_id, 'both')->get()->row();
        $delivery_notes = $this->db->select('*')->from('delivery_notes')->where('delivery_note_no', $data['delivery_note_no'])->like('sales_order_no', $data['sales_order_no'])->get()->row();

        // Tambahkan validasi jika Sales Invoice No. sudah ada dari insert via Form bukan via upload (Bu Nina)
        $this->db->select('*');
        $this->db->from('sales_invoices');
        $this->db->where('number', $data['number']);
        $this->db->where('upload', null);
        $this->db->where('upload_date', null);
        $invoice_manual = $this->db->get()->result();
        
        $trans_date = $data['trans_date'];
        $valid_date = ($d = DateTime::createFromFormat('Y-m-d', $trans_date)) && $d->format('Y-m-d') === $trans_date;
        
        // Validate required data
        if (!empty($invoice_manual)) {
            echo json_encode(["title" => "Duplicated", "message" => "Sales Invoice No " . $data['number'] . " is already exists. Please provide a unique number.", "theme" => "error"]);
        
        } elseif (empty($data['action']) || (strtolower($data['action']) !== "new" && strtolower($data['action']) !== "update")) {
            echo json_encode(["title" => "Error", "message" => "ACTION must be NEW or UPDATE", "theme" => "error"]);
        
        } elseif (strtolower($data['action']) !== 'update' && !empty($sales_invoices) && strtoupper($sales_invoices->upload) === "YES") {
            echo json_encode(["title" => "Duplicated", "message" => "Action=NEW and Sales Invoice No. " . $data['number'] . " is Duplicate Data", "theme" => "error"]);
        
        } elseif (!empty($sales_invoices) && $sales_invoices->status == "1" && strtolower($data['action']) !== 'update') {
            echo json_encode(["title" => "Duplicated", "message" => "Sales Invoice No. " . $data['number'] . " has been processed previously (Closed)", "theme" => "error"]);
        
        } elseif ($data['delivery_note_no'] !== "-" && !empty($delivery_notes) && $delivery_notes->status == "1" && strtolower($data['action']) !== 'update') {
            echo json_encode(["title" => "Duplicated", "message" => "Delivery Note No. " . $data['delivery_note_no'] . " has been processed previously (Closed)", "theme" => "error"]);
        
        } elseif (empty($data['account_type']) || (strtoupper($data['account_type']) !== "DEBIT" && strtoupper($data['account_type']) !== "CREDIT")) {
            echo json_encode(["title" => "Error", "message" => "Account Type must be DEBIT or CREDIT", "theme" => "error"]);
        
        } elseif (empty($trans_date) || !$valid_date) {
            echo json_encode(["title" => "Error", "message" => "Date format must be 'YYYY-MM-DD'", "theme" => "error"]);
        
        } elseif (empty($customers)) {
            echo json_encode(["title" => "Not Found", "message" => "Customer No " . $data['customer_id'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($journal_types)) {
            echo json_encode(["title" => "Not Found", "message" => "Journal Type Code " . $data['journal_number'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($account_coa)) {
            echo json_encode(["title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($divisions)) {
            echo json_encode(["title" => "Not Found", "message" => "Division ID " . $data['division_id'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($account_banks)) {
            echo json_encode(["title" => "Not Found", "message" => "Bank Account No. " . $data['bank_account'] . " Not Found", "theme" => "error"]);
        
        } else {
            // Get SI number
            $number_from_excel = $data['number'] ?? '-';
            $existing_data = null;
            if ($number_from_excel !== '-') {
                $existing_data = $this->crud->read('sales_invoices', [], ["number" => $number_from_excel]);
            }
            
            if (empty($number_from_excel) || $number_from_excel === '-') {
                // KONDISI 1: Jika data dari excel kosong atau '-', buat nomor baru.
                $customer_code = $customers->code;
                $datenow    = "SI-" . $customer_code . date("Ym", strtotime(base64_decode($trans_date)));
                $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM sales_invoices WHERE `number` like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                if ($kode == NULL) {
                    $autoID = sprintf("%04s", $kode + 1);
                } else {
                    $urutan = (int) substr($kode, -4);
                    $urutan++;
                    $autoID = sprintf("%04s", $urutan);
                }
                
                $number = $datenow . "-" . $autoID;

            } elseif (!empty($existing_data)) {
                // KONDISI 2: Jika number dari excel ada di database, gunakan yang sudah ada.
                $number = $existing_data->number;

            } else {
                // KONDISI 3: Jika number dari excel tidak kosong dan tidak ada di database, gunakan nilai dari excel.
                $number = $number_from_excel;
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

            // Prepare data
            $payment_term   = $customers->payment_term ?? $data["payment_term"];
            $vat_rate       = $customers->taxes ?? 0;
            $due_date       = date('Y-m-d', strtotime('+' . $customers->payment_term . ' days', strtotime($data['trans_date'])));
            $payment_to     = $account_banks->bank_name ?? $data["payment_to"];
            $price          = (float)($data['price'] ?? 0);
            $qty            = (float)($data['qty'] ?? 0);
            $discount_rate  = (float)($data['discount'] ?? 0);
            $subtotal_item  = (float)$price * $qty;
            $discount       = (float)$subtotal_item * ($discount_rate / 100);
            $total          = (float)$subtotal_item - $discount;
            $total_idr      = (float)$total * $rate;

            $faktur_no      = $data['faktur_no'] ?? null;
            $faktur_code    = $data['faktur_code'] ?? "01";
            $fp_pengganti   = "00"; // default
            $kode_trans     = $faktur_code . $fp_pengganti;
            $nomor_urut     = !empty($data['faktur_no']) ? substr($data['faktur_no'], 6) : null;

            $dataFinal = [
                "customer_id"           => $customers->id,
                "item_fg_id"            => $item_fg_id ?? null,
                "journal_type_id"       => $journal_types->id,
                "number"                => $number,
                "division"              => $divisions->id,
                "trans_date"            => $trans_date ?? $data['trans_date'],
                "due_date"              => $due_date,
                "taxes"                 => $vat_rate,
                "payment_term"          => $payment_term,
                "payment_to"            => $payment_to,
                "currency"              => $data['currency'] ?? "IDR",
                "remarks"               => $data["remarks"] ?? "PI via Upload",
                "fp_pengganti"          => $fp_pengganti,
                "faktur_no"             => $faktur_no ?? null,
                "faktur_code"           => $faktur_code ?? null,
                "kode_trans"            => $kode_trans ?? null,
                "kode_cabang"           => $data['kode_cabang'] ?? null,
                "tahun_pemeriksaan"     => date('y'),
                "country_name"          => $customers->country_name ?? null,
                "bc1"                   => null,
                "bc2"                   => null,
                "bc3"                   => null,
                "bc4"                   => date('Y'),
                "bc_no"                 => $data['bc_no'] ?? null,
                "keterangan_tambahan"   => $data['keterangan_tambahan'] ?? null,
                "cap_fasilitas"         => $data['cap_fasilitas'] ?? null,
                "no_urut"               => $nomor_urut ?? null,
                "sales_order_no"        => $data['sales_order_no'],
                "delivery_note_no"      => $data['delivery_note_no'],
                "customer_order_no"     => $data['customer_order_no'],
                "item_no"               => $data['item_no'],
                "item_name"             => $item_fg->name ?? $data['item_name'],
                "qty"                   => $qty ?? $data["qty"],
                "uom"                   => $item_fg->uom ?? $data["uom"],
                "price"                 => $price ?? $data["price"],
                "discount"              => $discount,
                "account_number"        => $data['account_number'],
                "account_type"          => $data['account_type'],
                "disc_pr"               => 0,
                "disc_dp"               => 0,
                "down_payment"          => 0,
                "total_sub"             => 0,
                "total_invoice"         => 0,
                "total_discount"        => 0,
                "total_vat"             => 0,
                "total_pph"             => 0,
                "total_grand"           => 0,
                "total"                 => $total ?? $data['total'],
                "total_local"           => $total_idr,
                "upload"                => "YES",
                "upload_date"           => date("Y-m-d"),
            ];


            // CREATE (INSERT)
            if (strtoupper($data['action']) === "NEW") {
                $send = $this->crud->create('sales_invoices', $dataFinal);
            } else {
                if (!empty($sales_invoices)) {
                    $whereParams = ["number" => $dataFinal["number"], "account_number" => $dataFinal["account_number"], "delivery_note_no" => $dataFinal["delivery_note_no"], "sales_order_no" => $dataFinal["sales_order_no"], "item_fg_id" => $sales_invoices->item_fg_id];
                    $send = $this->crud->update('sales_invoices', $whereParams, $dataFinal);
                } else {
                    $send = $this->crud->create('sales_invoices', $dataFinal);
                }
            }

            if ($send) {
                echo $send;
            } else {
                echo json_encode(["title" => "Error", "message" => "Failed to create invoice " . $data['number'], "theme" => "error"]);
            }
        }
    }

    public function uploadGetJournal()
    {
        // Akun Third Parties yang ditentukan
        $ar_account_keys = ['140.110.00', '140.120.00', '140.210.00', '140.220.00', '140.230.00', '140.240.00'];
        $vat_account_key = '250.160.00'; 

        // === 1. Get Records Sales Invoices ===
        $this->db->select('a.*, b.account_name');
        $this->db->from('sales_invoices a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.upload', "YES");
        $this->db->where('a.upload_date', date("Y-m-d"));
        $records = $this->db->get()->result_array();
        
        // === 2. Get dan Map Journal Setups ===
        $journal_setup_map = [];
        $allJournals = [];
        $groupedInvoices = [];
        
        // Get semua journal setup yang ada dan aktif
        $this->db->select('journal_setups.*, account_coa.account_name');
        $this->db->from('journal_setups');
        $this->db->join('account_coa', 'journal_setups.account_number = account_coa.account_number');
        $this->db->where('account_coa.status', 0);
        $journal_setups = $this->db->get()->result_array();
        
        // Mapping setup ke array asosiatif untuk akses cepat
        foreach ($journal_setups as $setup) {
            $journal_setup_map[$setup['journal_type_id']][$setup['account_number']] = $setup;
        }

        // Group data berdasarkan nomor invoice
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

        // === 3. Proses setiap grup invoice ===
        foreach ($groupedInvoices as $number => $group) {
            $main_record = $group['main_record'];
            $items = $group['items'];
            $rate = (float)($main_record['rate'] ?? 1);
            $journal_type_id = $main_record['journal_type_id'];

            $mergedData = [];
            $subTotal = 0;
            
            // Hitung total dari semua item dan gabungkan entri
            foreach ($items as $item) {
                $total = (float)($item['total'] ?? 0);
                $subTotal += $total;

                if (isset($mergedData[$item['account_number']])) {
                    $mergedData[$item['account_number']]['debit'] += ($item['account_type'] == "DEBIT" ? $total : 0);
                    $mergedData[$item['account_number']]['credit'] += ($item['account_type'] == "CREDIT" ? $total : 0);
                } else {
                    $mergedData[$item['account_number']] = [
                        "number"         => $number,
                        "account_number" => $item['account_number'],
                        "account_name"   => $item['account_name'],
                        "debit"          => ($item['account_type'] == "DEBIT" ? $total : 0),
                        "credit"         => ($item['account_type'] == "CREDIT" ? $total : 0),
                    ];
                }
            }

            // Perhitungan VAT (Pajak)
            $vatRate    = (float)($main_record['taxes'] ?? 0) / 100;
            $vatTotal   = $subTotal * $vatRate;
            $arrTotal   = $subTotal + $vatTotal;
            $localTotal = $arrTotal * $rate;
            
            // === JURNAL PIUTANG (AR) - Menggunakan 1 dari array Account Keys (Fix Bug hardcoded account Third Party beda dengan di Journal Setup) ===
            $journalSetupAR  = null;
            $ar_account_used = null;

            // Cari setup AR yang valid di antara kunci yang diizinkan
            foreach ($ar_account_keys as $ar_account) {
                if (isset($journal_setup_map[$journal_type_id][$ar_account])) {
                    $journalSetupAR = $journal_setup_map[$journal_type_id][$ar_account];
                    $ar_account_used = $ar_account;
                    break; // Ambil yang pertama dan hentikan loop
                }
            }
            
            if ($journalSetupAR) {
                // Asumsi Piutang selalu DEBIT (nilai $arrTotal = Grand Total)
                $debitAR = ($journalSetupAR['status'] == "DEBIT") ? $arrTotal : 0; 
                $creditAR = ($journalSetupAR['status'] == "CREDIT") ? $arrTotal : 0;
                
                $mergedData[$ar_account_used] = [ // Menggunakan nomor akun yang ditemukan
                    "number"         => $number,
                    "account_number" => $journalSetupAR['account_number'],
                    "account_name"   => $journalSetupAR['account_name'],
                    "debit"          => $debitAR,
                    "credit"         => $creditAR,
                ];
            }


            // === JURNAL VAT ===
            $journalSetupVAT = $journal_setup_map[$journal_type_id][$vat_account_key] ?? null;
            if ($journalSetupVAT) {
                $debitVAT = ($journalSetupVAT['status'] == "DEBIT") ? $vatTotal : 0;
                $creditVAT = ($journalSetupVAT['status'] == "CREDIT") ? $vatTotal : 0;
                
                $mergedData[$journalSetupVAT['account_number']] = [
                    "number"         => $number,
                    "account_number" => $journalSetupVAT['account_number'],
                    "account_name"   => $journalSetupVAT['account_name'],
                    "debit"          => $debitVAT,
                    "credit"         => $creditVAT,
                ];
            }

            // Update total di tabel sales_invoices
            $this->db->update('sales_invoices', [
                "total_sub" => $subTotal,
                "total_vat" => $vatTotal,
                "total_pph" => 0,
                "total_grand" => $arrTotal,
                "total_invoice" => $arrTotal,
                "total_local" => $localTotal
            ], ["number" => $number]);

            // Tambahkan hasil jurnal ke array utama
            $flag_counter = 1;
            foreach (array_values($mergedData) as $journalEntry) {
                $journalEntry['flag'] = $flag_counter++;
                $allJournals[] = $journalEntry;
            }
        }

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
            
            $sales_invoice_journals = $this->crud->read('sales_invoice_journals', [], ["number" => $post['number'], "account_number" => $post['account_number']]);

            if (@$sales_invoice_journals->id != "") {
                $send = $this->crud->update('sales_invoice_journals', ["number" => $post['number'], "account_number" => $post['account_number']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('sales_invoice_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }


    // ------- PRINT FUNCTIONS ---------
    public function print_commercial($invoice_no)
    {
        $invoice_no = base64_decode($invoice_no);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["number" => $invoice_no]);
        $sales_invoice = $this->crud->read('sales_invoices', [], ["number" => $invoice_no]);
        $delivery_note = $this->crud->read('delivery_notes', [], ["delivery_note_no" => $sales_invoice->delivery_note_no]);

        if (@$delivery_note->address == "2") {
            $address_no = "_2";
        } else {
            $address_no = "";
        }

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 20;
        $page = ceil(count($sales_invoices) / $rows);
        //Generate QRcode
        $this->createQrcode($sales_invoice->number, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $sales_invoice->number . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                        <h1>Press CTRL + P for Print</h1>
                        <p>Display pages for 10 rows</p>
                        <p>Paper Size A4, Layout Landscape</p>
                        <p>Margin Default, Scale 98</p>
                    </center></div><div class="print">';
        
        //Loop Page
        $no = 1;
        $grand_qty = 0;
        $grand_total = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                d.number as customer_number, 
                d.name as customer_name,
                d.type, 
                b.address, 
                b.address_billing, 
                b.telp_billing,
                b.telp,
                COALESCE(h.currency,h2.currency) as currency,
                COALESCE(d.currency, "") as customer_currency,
                g.origin,
                g.sailing,
                g.ship_by,
                g.incoterm,
                g.delivery_order_no,
                g.trans_type,
                COALESCE(f.bank_name, "") as bank_name,
                "PT. BANSHU PLASTIC INDONESIA" as account_name,
                COALESCE(f.bank_account, 0) as bank_account');
            $this->db->select('fg.hs_code');
            $this->db->from('sales_invoices a');
            $this->db->join('customers d', 'a.customer_id = d.id', 'left');
            $this->db->join('delivery_notes g', 'a.delivery_note_no = g.delivery_note_no and a.customer_order_no = g.customer_order_no', 'left');
            $this->db->join('account_banks f', 'a.payment_to = f.bank_name', 'left');
            $this->db->join('sales_orders h', 'a.sales_order_no = h.sales_order_no', 'left');
            $this->db->join('sales_order_rm h2', 'a.sales_order_no = h2.sales_order_no', 'left');
            $this->db->join('customer_address b', 'COALESCE(h.customer_address_id, h2.customer_address_id) = b.id', 'left');
            $this->db->join('item_fg fg', 'fg.id = a.item_fg_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $invoice_no);
            $this->db->group_by('a.id');
            // $this->db->order_by('a.item_no', 'asc');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            // $this->db->select('a.delivery_note_no');
            // $this->db->from('sales_invoices a');
            // $this->db->where('a.deleted', 0);
            // $this->db->where('a.number', $invoice_no);
            // $this->db->group_by('a.delivery_note_no');
            // $this->db->order_by('a.delivery_note_no', 'asc');
            // $deliveryNotes = $this->db->get()->result_array();

            if ($records[0]['type'] == "EXPORT") {
                $header = ' <th width="60">Price</th>
                            <th width="60">Total</th>';
            } else {
                $title = "DELIVERY NOTE";
                $header = "";
            }

            $html .= '  <table style="width:100%; ">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $sales_invoice->number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_sales_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_sales_invoice . '</td>
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
                                </th>
                            </tr>
                        </table>
                        <div style="border: 0px; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>INVOICE</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Customer Code</td>
                                            <td width="10">:</td>
                                            <td><b>' . $records[0]['customer_number'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Customer Name</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['customer_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Ship To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address' . $address_no] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Bill To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address_billing' . $address_no] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Attention</td>
                                            <td>:</td>
                                            <td><b>' . $address_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Telp</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['telp' . $address_no] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Sales Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$sales_invoice->number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Faktur No</td>
                                            <td>:</td>
                                            <td><b>' . @$sales_invoice->faktur_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['trans_type'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Ship By</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['ship_by'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($sales_invoice->trans_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Payment Due</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($sales_invoice->due_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">';

            // Kolom HS Code ditampilkan pada printout invoice jika faktur_code = 070 saja (Bu Nina)
            $showHsCode = (stripos($records[0]['faktur_code'], "07") !== false);

            $html .= '<tr>';
            $html .= '<th width="20">No</th>';
            $html .= '<th>Sales Order No</th>';
            $html .= '<th>Product No</th>';
            $html .= '<th>Product Name</th>';

            if ($showHsCode) {
                $html .= '<th width="60">HS Code</th>';
            }

            $html .= '<th width="60">UoM</th>';
            $html .= '<th width="60">Qty</th>';
            $html .= '<th width="60">Currency</th>';
            $html .= '<th width="60">Price</th>';
            $html .= '<th width="60">Total</th>';
            $html .= '</tr>';

            $sub_total = 0;
            $vat_total = 0;
            $dpp_total = 0;
            $tax_total = 0;
            $tax = "";

            // foreach ($deliveryNotes as $deliveryNote) {
            //     $delivery_notess = array_map(function($deliveryNote) {
            //         return $deliveryNote['delivery_note_no'];
            //     }, $deliveryNotes); 

            //     $delivery_notes_string = implode(',', $delivery_notess);
            // }

            foreach ($records as $record)
            {
                if ($record['customer_order_no'] == "") {
                    $sales_order_no = $record['sales_order_no'];
                } else {
                    $sales_order_no = $record['customer_order_no'];
                }

                /** -- existing --
                $total_invoice = ($record['total_invoice']);
                $discount = ($record['discount']);
                $sub_total = ($record['total_sub']);
                $down_payment = ($record['down_payment']);
                $dpp_total = (($record['total_sub'] - $record['down_payment']) * 11/12);
                $vat_total = ($dpp_total * ($record['taxes']/100));
                $disc_pr = ($record['disc_pr']);
                $disc_dp = ($record['disc_dp']);
                **/

                // fixing nominal-nominal seharusnya output print out = data grid (Bu Nina)
                $total_invoice  = ($record['total_invoice']);
                $discount       = ($record['discount']) ?? 0;
                $taxes          = ($record['taxes']);
                $down_payment   = ($record['down_payment']);

                // --- SUB TOTAL = total_invoice - discount
                $sub_total      = ($record['total_sub'] - $discount);

                // --- DPP = sub_total * 11/12
                $dpp_total      = (($record['total_sub']) * 11/12);

                // --- VAT = ((sub_total - down_payment) * 11/12) * taxes
                // $vat_total      = (($sub_total - $down_payment) * 11/12) * ($taxes/100);
                $vat_total      = ($record['total_vat']);

                $disc_pr = ($record['disc_pr']);
                $disc_dp = ($record['disc_dp']);

                $sales_invoices = $this->db->query("SELECT * FROM sales_invoice_journals WHERE account_number IN ('170.110.00', '170.130.00') AND number = ?", [$record['number']])->result();
                // var_dump($sales_invoices);
                
                if (!empty($sales_invoices)) {
                    foreach ($sales_invoices as $invoice) {
                        if ($invoice->account_number == "170.110.00" && $invoice->credit == "0" && $invoice->debit == "0" ) {
                            $tax = "21 (5%)";
                            $tax_total = 0;
                            break;
                        } elseif ($invoice->account_number == "170.110.00") {
                            $tax = "21 (5%)";
                            $tax_total = ($sub_total * (5/100));
                            break;
                        } elseif($invoice->account_number == "170.130.00") {
                            $tax = "23 (2%)";
                            $tax_total = ($sub_total * (2/100));
                            break;
                        } else{
                            $tax = "";
                            $tax_total = 0;
                        }
                    }
                } else {
                    $tax = "";
                    $tax_total = 0;
                }

                // Tax Total = Total PPH from table sales_invoices
                $tax_total = $record['total_pph'] ?? $tax_total;

                $grand_total = ((($sub_total - $down_payment) + $vat_total) - $tax_total);


                // if ($record['type'] == "EXPORT") {
                //     $content = '<td style="text-align:right">' . number_format($record['price'], 4, ",", ".") . '</td>
                //                 <td style="text-align:right">' . number_format(($record['price'] * $record['qty']), 2, ",", ".") . '</td>';
                // } else {
                //     $content = "";
                // }

                if($record['currency'] == 'IDR'){
                    $format_no = 2;
                }else{
                    $format_no = 4;
                }

                $html .= '<tr>';
                $html .= '<td style="text-align:center">' . $no . '</td>';
                $html .= '<td><span style="font-size:10px;">' . $sales_order_no . '</span></td>';
                $html .= '<td style="font-size:10px;">' . $record['item_no'] . '</td>';
                $html .= '<td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>';
                
                if ($showHsCode) {
                    $html .= '<td style="text-align:center;"><span style="font-size:10px;">' . $record['hs_code'] . '</span></td>';
                }

                $currency_display = $record['currency'] ?? $record['customer_currency'];

                $html .= '<td style="text-align:center;"><span style="font-size:10px;">' . $record['uom'] . '</span></td>';
                $html .= '<td style="text-align:right">' . number_format($record['qty'], 0, ",", ".") . '</td>';
                $html .= '<td style="text-align:center;"><span style="font-size:10px;">' . $currency_display . '</span></td>';
                $html .= '<td style="text-align:right">' . number_format($record['price'], $format_no, ",", ".") . '</td>';
                $html .= '<td style="text-align:right">' . number_format(($record['price'] * $record['qty']), $format_no, ",", ".") . '</td>';
                $html .= '</tr>';

                $no++;
            }

            if (($i + 1) == $page) {
                $column = 8;
                if ($showHsCode) {
                    $column = 9;
                }

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Total Invoice</b></td>
                            <td style="text-align:right"><b>' . number_format($total_invoice, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Discount ('. number_format($disc_pr,0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($discount, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Sub Total</b></td>
                            <td style="text-align:right"><b>' . number_format($sub_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                        <td colspan="'.$column.'" style="text-align:right"><b>Down Payment ('. number_format($disc_dp,0).' %)</b></td>
                        <td style="text-align:right"><b>' . number_format($down_payment, $format_no, ",", ".") . '</b></td>
                    </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>DPP</b></td>
                            <td style="text-align:right"><b>' . number_format($dpp_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>VAT ('. number_format($record['taxes'],0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($vat_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                
                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Income Tax '.$tax.'</b></td>
                            <td style="text-align:right"><b>' . number_format($tax_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Grand Total</b></td>
                            <td style="text-align:right"><b>' . number_format($grand_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                    <table id="customers" style="margin-top:10px; font-size:10px; border: 2px solid black;border-collapse: collapse;">
                        <tr>
                            <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">
                                Total Invoice Value in Words: <br><br>
                                ' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . ' <br><br><br>
                                Payment Information <br>
                                Please transfer the Grand Total Amount to the following bank account: <br>
                                <table style="width:100%; margin-top:10px; font-size:10px; border: none;">
                                    <tr>
                                        <td style="width:15%; text-align:left; border: none;"><b>Bank Name</td>
                                        <td style="width:2%; text-align:left; border: none;"><b>:</td>
                                        <td style="width:90%; text-align:left; border: none;"><b>' . $record['bank_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Account Name</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['account_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Bank Account</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['bank_account'] . '</td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table id="customers" style="margin-top:10px; font-size:12px;"><br><br>
                        <tr>
                            <th width="420" style="text-align:left; vertical-align:top;" rowspan="4">
                            Note. <br><br>
                        </th>
                        </tr>
                        <tr>
                            <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                        </tr>
                        <tr>
                            <th style="height:150px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;">Finance Dept</th>
                        </tr>
                    </table>
                </div>';
               
            }

            $html .= '</table>';

            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print_commercial_sum2($invoice_no)//origin
    {
        $invoice_no = base64_decode($invoice_no);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["number" => $invoice_no]);
        $sales_invoice = $this->crud->read('sales_invoices', [], ["number" => $invoice_no]);
        $delivery_note = $this->crud->read('delivery_notes', [], ["delivery_note_no" => $sales_invoice->delivery_note_no]);

        if (@$delivery_note->address == "2") {
            $address_no = "_2";
        } else {
            $address_no = "";
        }

        $this->db->select('item_fg_id, price');
        $this->db->from('sales_invoices');
        $this->db->where('number', $invoice_no);
        $this->db->group_by(['item_fg_id', 'price']);
        

        $query = $this->db->get();
        $total_rows = $query->num_rows();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 20;
        $page = ceil($total_rows / $rows);
        // $page = ceil(count($sales_invoices) / $rows);
        //Generate QRcode
        $this->createQrcode($sales_invoice->number, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $sales_invoice->number . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                        <h1>Press CTRL + P for Print</h1>
                        <p>Display pages for 10 rows</p>
                        <p>Paper Size A4, Layout Landscape</p>
                        <p>Margin Default, Scale 98</p>
                    </center></div><div class="print">';
        
        //Loop Page
        $no = 1;
        $grand_qty = 0;
        $grand_total = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                h.qty as qty_sum,
                h.price as prices,
                d.number as customer_number, 
                d.name as customer_name,
                d.type, 
                b.address, 
                b.address_billing, 
                b.telp_billing,
                b.telp,
                COALESCE(i.currency,i2.currency) as currency,
                g.origin,
                g.sailing,
                g.ship_by,
                g.incoterm,
                g.delivery_order_no,
                g.trans_type,
                COALESCE(f.bank_name, "") as bank_name,
                "PT. BANSHU PLASTIC INDONESIA" as account_name,
                COALESCE(f.bank_account, 0) as bank_account');
            $this->db->from('sales_invoices a');
            $this->db->join('customers d', 'a.customer_id = d.id', 'left');
            $this->db->join('delivery_notes g', 'a.delivery_note_no = g.delivery_note_no and a.customer_order_no = g.customer_order_no', 'left');
            $this->db->join('account_banks f', 'a.payment_to = f.bank_name', 'left');
            $this->db->join('sales_orders i', 'a.sales_order_no = i.sales_order_no', 'left');
            $this->db->join('sales_order_rm i2', 'a.sales_order_no = i2.sales_order_no', 'left');
            $this->db->join('customer_address b', 'COALESCE(i.customer_address_id, i2.customer_address_id) = b.id', 'left');
            $this->db->join("(SELECT id, item_no, item_fg_id, SUM(qty) as qty, price FROM sales_invoices WHERE number = '$invoice_no' GROUP BY item_fg_id, item_no, price) h", "(a.item_fg_id = h.item_fg_id OR (a.item_fg_id IS NULL AND a.item_no = h.item_no))","left");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $invoice_no);
            // $this->db->group_by('h.item_fg_id');
            $this->db->group_by('h.item_no');
            $this->db->group_by('h.price');
            $this->db->order_by('a.item_no', 'ASC');
            // $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            // var_dump($records);
            // die;

            if (!empty($records) && isset($records[0]['type'])) {
                if ($records[0]['type'] == "EXPORT") {
                    $header = ' <th width="60">Price</th>
                                <th width="60">Total</th>';
                } else {
                    $title = "DELIVERY NOTE";
                    $header = "";
                }
            } else {
                $title = "DELIVERY NOTE";
                $header = "";
            }

            $customer_number = (!empty($records) && isset($records[0]['customer_number'])) ? $records[0]['customer_number'] : '-';
            $customer_name = (!empty($records) && isset($records[0]['customer_name'])) ? $records[0]['customer_name'] : '-';

            $html .= '  <table style="width:100%; ">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $sales_invoice->number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_sales_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_sales_invoice . '</td>
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
                                </th>
                            </tr>
                        </table>
                        <div style="border: 0px; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>INVOICE</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Customer Code</td>
                                            <td width="10">:</td>
                                            <td><b>' . $customer_number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Customer Name</td>
                                            <td>:</td>
                                            <td><b>' . $customer_name . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Ship To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address' . $address_no] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Bill To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address_billing' . $address_no] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Attention</td>
                                            <td>:</td>
                                            <td><b>' . $address_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Telp</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['telp' . $address_no] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Sales Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$sales_invoice->number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Faktur No</td>
                                            <td>:</td>
                                            <td><b>' . @$sales_invoice->faktur_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['trans_type'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Ship By</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['ship_by'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($sales_invoice->trans_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Payment Due</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($sales_invoice->due_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">UoM</th>
                                        <th width="60">Qty</th>
                                        <th width="60">Currency</th>
                                        <th width="60">Price</th>
                                        <th width="60">Total</th>
                                    </tr>';
            $sub_total = 0;
            $vat_total = 0;
            $dpp_total = 0;
            $tax_total = 0;
            $tax = "";

            foreach ($records as $record) {
                if ($record['customer_order_no'] == "") {
                    $sales_order_no = $record['sales_order_no'];
                } else {
                    $sales_order_no = $record['customer_order_no'];
                }

                $total_invoice = ($record['total_invoice']);
                $discount = ($record['discount']);
                $sub_total = ($record['total_sub']);
                $down_payment = ($record['down_payment']);
                $dpp_total = (($record['total_sub'] - $record['down_payment']) * 11/12);
                $vat_total = ($dpp_total * ($record['taxes']/100));
                $disc_pr = ($record['disc_pr']);
                $disc_dp = ($record['disc_dp']);
                $sales_invoices = $this->db->query("SELECT * FROM sales_invoice_journals WHERE account_number IN ('170.110.00', '170.130.00') AND number = ?", [$record['number']])->result();
                // var_dump($sales_invoices);
                
                if (!empty($sales_invoices)) {
                    foreach ($sales_invoices as $invoice) {
                        if ($invoice->account_number == "170.110.00" && $invoice->credit == "0" && $invoice->debit == "0" ) {
                            $tax = "21 (5%)";
                            $tax_total = 0;
                            break;
                        } elseif ($invoice->account_number == "170.110.00") {
                            $tax = "21 (5%)";
                            $tax_total = ($sub_total * (5/100));
                            break;
                        } elseif($invoice->account_number == "170.130.00") {
                            $tax = "23 (2%)";
                            $tax_total = ($sub_total * (2/100));
                            break;
                        } else{
                            $tax = "";
                            $tax_total = 0;
                        }
                    }
                } else {
                    $tax = "";
                    $tax_total = 0;
                }
                
                if($record['currency'] == 'IDR'){
                    $format_no = 2;
                }else{
                    $format_no = 4;
                }
                
                $grand_total = ((($sub_total - $down_payment) + $vat_total) - $tax_total);

                if ($record['type'] == "EXPORT") {
                    $content = '<td style="text-align:right">' . number_format($record['price'], $format_no, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format(($record['price'] * $record['qty']), $format_no, ",", ".") . '</td>';
                } else {
                    $content = "";
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="font-size:10px;">' . $record['item_no'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty_sum'], 0, ",", ".") . '</td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['currency'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['prices'], $format_no, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format(($record['prices'] * $record['qty_sum']), $format_no, ",", ".") . '</td>
                            </tr>';
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Total Invoice</b></td>
                            <td style="text-align:right"><b>' . number_format($total_invoice, $format_no, ",", ".") . '</b></td>
                        </tr>';
                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Discount ('. number_format($disc_pr,0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($discount, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Sub Total</b></td>
                            <td style="text-align:right"><b>' . number_format($sub_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Down Payment ('. number_format($disc_dp,0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($down_payment, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>DPP</b></td>
                            <td style="text-align:right"><b>' . number_format($dpp_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>VAT ('. number_format($record['taxes'],0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($vat_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                
                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Income Tax '.$tax.'</b></td>
                            <td style="text-align:right"><b>' . number_format($tax_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                $html .= '<tr>
                            <td colspan="7" style="text-align:right"><b>Grand Total</b></td>
                            <td style="text-align:right"><b>' . number_format($grand_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                    <table id="customers" style="margin-top:10px; font-size:10px; border: 2px solid black;border-collapse: collapse;">
                        <tr>
                            <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">
                                Total Invoice Value in Words: <br><br>
                                ' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . ' <br><br><br>
                                Payment Information <br>
                                Please transfer the Grand Total Amount to the following bank account: <br>
                                <table style="width:100%; margin-top:10px; font-size:10px; border: none;">
                                    <tr>
                                        <td style="width:15%; text-align:left; border: none;"><b>Bank Name</td>
                                        <td style="width:2%; text-align:left; border: none;"><b>:</td>
                                        <td style="width:90%; text-align:left; border: none;"><b>' . $record['bank_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Account Name</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['account_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Bank Account</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['bank_account'] . '</td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table id="customers" style="margin-top:10px; font-size:12px;"><br><br>
                        <tr>
                            <th width="420" style="text-align:left; vertical-align:top;" rowspan="4">
                            Note. <br><br>
                        </th>
                        </tr>
                        <tr>
                            <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                        </tr>
                        <tr>
                            <th style="height:150px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;">Finance Dept</th>
                        </tr>
                    </table>
                </div>';
               
            }

            $html .= '</table>';

            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print_commercial_sum($invoice_no)
    {
        $invoice_no = base64_decode($invoice_no);

        $summary_qty = $this->db->select('a.item_fg_id, 
        fg.hs_code,
        a.item_no, 
        a.item_name, 
        a.uom, 
        a.price, 
        a.currency, 
        SUM(a.qty) as qty_sum, 
        a.total_invoice,
        a.discount,
        a.total_sub,
        a.down_payment,
        a.taxes,
        a.disc_pr,
        a.total_pph,
        a.total_vat,
        a.total_grand,
        a.disc_dp')
            ->from('sales_invoices a')
            ->join('item_fg fg', 'fg.id = a.item_fg_id', 'left')
            ->where('a.number', $invoice_no)
            ->group_by(['item_fg_id', 'item_no', 'item_name', 'uom', 'price', 'currency'])
            ->get()
            ->result_array();

        $record = $this->db->select('a.*, 
            COALESCE(f.bank_name, "") as bank_name, 
            "PT. BANSHU PLASTIC INDONESIA" as account_name, 
            COALESCE(f.bank_account, 0) as bank_account,
            d.number as customer_number, 
            d.name as customer_name,
            d.type, 
            b.address, 
            b.address_billing, 
            b.telp_billing,
            b.telp,
            COALESCE(i.currency,i2.currency) as currency,
            g.origin,
            g.sailing,
            g.ship_by,
            g.incoterm,
            g.delivery_order_no,
            g.trans_type')
            ->from('sales_invoices a')
            ->join('account_banks f', 'a.payment_to = f.bank_name', 'left')
            ->join('customers d', 'a.customer_id = d.id', 'left')
            ->join('sales_orders i', 'a.sales_order_no = i.sales_order_no', 'left')
            ->join('sales_order_rm i2', 'a.sales_order_no = i2.sales_order_no', 'left')
            ->join('customer_address b', 'COALESCE(i.customer_address_id, i2.customer_address_id) = b.id', 'left')
            ->join('delivery_notes g', 'a.delivery_note_no = g.delivery_note_no and a.customer_order_no = g.customer_order_no', 'left')
            ->where('a.deleted', 0)
            ->where('a.number', $invoice_no)
            ->limit(1)
            ->get()
            ->row_array();

        $rows_per_page = 20;
        $total_rows = count($summary_qty);
        $total_pages = ceil($total_rows / $rows_per_page);

        $this->createQrcode($invoice_no, "assets/image/qrcode/");

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $html = $this->build_invoice_html($summary_qty, $record, $config, $config_iso, $total_pages, $rows_per_page);
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    private function build_invoice_html($summary_qty, $record, $config, $config_iso, $total_pages, $rows_per_page) {
        $html = '<html><head><title>' . $record['number'] . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center><h1>Press CTRL + P for Print</h1><p>Display pages for 20 rows</p><p>Paper Size A4, Layout Landscape</p><p>Margin Default, Scale 98</p></center></div><div class="print">';

        $grand_total = 0;

        for ($i = 0; $i < $total_pages; $i++) {
            $records = array_slice($summary_qty, $i * $rows_per_page, $rows_per_page);

            $html .= '<table style="width:100%;">
                <tr>
                    <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                    <td width="250" style="padding:10px;">
                        <b style="font-size:14px;">' . $config->name . '</b><br>
                        <span style="font-size:10px;">' . $config->address . '</span><br>
                    </td>
                    <th width="100" style="text-align:right;">
                        <table style="width:100%; font-size:10px;">
                            <tr>
                                <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $record['number'] . '.png') . '" width="60"/></td>
                                <td width="60">Doc No</td>
                                <td width="5">:</td>
                                <td width="100">' . $config_iso->doc_sales_invoice . '</td>
                            </tr>
                            <tr>
                                <td>Form</td>
                                <td>:</td>
                                <td>' . $config_iso->form_sales_invoice . '</td>
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
                    </th>
                </tr>
            </table>';

            $html .= '<div style="padding:10px;"><center><h3>INVOICE</h3></center>
            <div style="float:left; width:60%;">
                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                    <tr><td width="150">Customer Code</td><td width="10">:</td><td><b>' . $record['customer_number'] . '</b></td></tr>
                    <tr><td>Customer Name</td><td>:</td><td><b>' . $record['customer_name'] . '</b></td></tr>
                    <tr><td style="vertical-align:top;">Ship To</td><td style="vertical-align:top;">:</td><td><b>' . $record['address'] . '</b></td></tr>
                    <tr><td style="vertical-align:top;">Bill To</td><td style="vertical-align:top;">:</td><td><b>' . $record['address_billing'] . '</b></td></tr>
                    <tr><td>Telp</td><td>:</td><td><b>' . $record['telp'] . '</b></td></tr>
                </table>
            </div>
            <div style="float:left; width:40%;">
                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                    <tr><td width="100">Sales Invoice No</td><td width="10">:</td><td><b>' . $record['number'] . '</b></td></tr>
                    <tr><td>Faktur No</td><td>:</td><td><b>' . $record['faktur_no'] . '</b></td></tr>
                    <tr><td>Trans Type</td><td>:</td><td><b>' . $record['trans_type'] . '</b></td></tr>
                    <tr><td>Ship By</td><td>:</td><td><b>' . $record['ship_by'] . '</b></td></tr>
                    <tr><td>Delivery Date</td><td>:</td><td><b>' . date("d F Y", strtotime($record['trans_date'])) . '</b></td></tr>
                    <tr><td>Payment Due</td><td>:</td><td><b>' . date("d F Y", strtotime($record['due_date'])) . '</b></td></tr>
                </table>
            </div>';

            // Kolom HS Code ditampilkan pada printout invoice jika faktur_code = 070 saja (Bu Nina)
            $showHsCode = (stripos($record['faktur_code'], "07") !== false);

            $html .= '<table id="customers">';
            $html .= '<tr>';
            $html .= '<th width="20">No</th>';
            $html .= '<th>Product No</th>';
            $html .= '<th>Product Name</th>';
            
            if ($showHsCode) {
                $html .= '<th width="60">HS Code</th>';
            }

            $html .= '<th width="60">UoM</th>';
            $html .= '<th width="60">Qty</th>';
            $html .= '<th width="60">Currency</th>';
            $html .= '<th width="60">Price</th>';
            $html .= '<th width="60">Total</th>';
            $html .= '</tr>';

            $no = 1 + ($i * $rows_per_page);
            $sub_total = 0;
            foreach ($records as $row) {
                /** -- existing --
                $qty = $row['qty_sum'];
                $total_invoice = $row['total_invoice'];
                $discount = $row['discount'];
                $sub_total = $row['total_sub'];
                $down_payment = $row['down_payment'];
                $dpp_total = (($record['total_sub'] - $record['down_payment']) * 11/12);
                $vat_total = ($dpp_total * ($record['taxes']/100));
                **/

                // fixing nominal-nominal seharusnya output print out = data grid (Bu Nina)
                $qty            = $row['qty_sum'];
                $total_invoice  = $row['total_invoice'];
                $discount       = $row['discount'] ?? 0;
                $taxes          = $row['taxes'];
                $down_payment   = $row['down_payment'];

                // --- SUB TOTAL = total_invoice - discount
                $sub_total      = ($row['total_sub'] - $discount);

                // --- DPP = sub_total * 11/12
                $dpp_total      = (($record['total_sub']) * 11/12);

                // --- VAT = ((sub_total - down_payment) * 11/12) * taxes
                // $vat_total      = (($sub_total - $down_payment) * 11/12) * ($taxes/100);
                $vat_total      = ($record['total_vat']); // sementara ambil dari db

                $disc_pr = ($record['disc_pr']);
                $disc_dp = ($record['disc_dp']);

                $sales_invoices = $this->db->query("SELECT * FROM sales_invoice_journals WHERE account_number IN ('170.110.00', '170.130.00') AND number = ?", [$record['number']])->result();
                // var_dump($sales_invoices);
                
                if (!empty($sales_invoices)) {
                    foreach ($sales_invoices as $invoice) {
                        if ($invoice->account_number == "170.110.00" && $invoice->credit == "0" && $invoice->debit == "0" ) {
                            $tax = "21 (5%)";
                            $tax_total = 0;
                            break;
                        } elseif ($invoice->account_number == "170.110.00") {
                            $tax = "21 (5%)";
                            $tax_total = ($sub_total * (5/100));
                            break;
                        } elseif($invoice->account_number == "170.130.00") {
                            $tax = "23 (2%)";
                            $tax_total = ($sub_total * (2/100));
                            break;
                        } else{
                            $tax = "";
                            $tax_total = 0;
                        }
                    }
                } else {
                    $tax = "";
                    $tax_total = 0;
                }

                // Tax Total = Total PPH from table sales_invoices
                $tax_total = $row['total_pph'] ?? $tax_total;

                
                if($record['currency'] == 'IDR'){
                    $format_no = 2;
                }else{
                    $format_no = 4;
                }
                
                // --- GRAND TOTAL = (Subtotal - DP) + Vat - Tax Total // $grand_total = $row['total_grand'];
                $grand_total = ((($sub_total - $down_payment) + $vat_total) - $tax_total);
                
                $format_no = $row['currency'] === 'IDR' ? 2 : 4;
                $line_total = $row['price'] * $qty;
                // $sub_total += $line_total;
                
                $html .= '<tr>';
                $html .= '<td style="text-align:center">' . $no++ . '</td>';
                $html .= '<td>' . $row['item_no'] . '</td>';
                $html .= '<td>' . $row['item_name'] . '</td>';
                
                if ($showHsCode) {
                    $html .= '<td style="text-align:center;"><span style="font-size:10px;">' . $row['hs_code'] . '</span></td>';
                }

                $html .= '<td style="text-align:center">' . $row['uom'] . '</td>';
                $html .= '<td style="text-align:right">' . number_format($qty, 0, ",", ".") . '</td>';
                $html .= '<td style="text-align:center">' . $row['currency'] . '</td>';
                $html .= '<td style="text-align:right">' . number_format($row['price'], $format_no, ",", ".") . '</td>';
                $html .= '<td style="text-align:right">' . number_format($line_total, $format_no, ",", ".") . '</td>';
                $html .= '</tr>';
            }

            if ($i + 1 == $total_pages) {
                $column = 7;
                if ($showHsCode) {
                    $column = 8;
                }

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Total Invoice</b></td>
                            <td style="text-align:right"><b>' . number_format($total_invoice, $format_no, ",", ".") . '</b></td>
                        </tr>';
                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Discount ('. number_format($disc_pr,0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($discount, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Sub Total</b></td>
                            <td style="text-align:right"><b>' . number_format($sub_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Down Payment ('. number_format($disc_dp,0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($down_payment, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>DPP</b></td>
                            <td style="text-align:right"><b>' . number_format($dpp_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>VAT ('. number_format($record['taxes'],0).' %)</b></td>
                            <td style="text-align:right"><b>' . number_format($vat_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                
                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Income Tax '.$tax.'</b></td>
                            <td style="text-align:right"><b>' . number_format($tax_total, $format_no, ",", ".") . '</b></td>
                        </tr>';
                $html .= '<tr>
                            <td colspan="'.$column.'" style="text-align:right"><b>Grand Total</b></td>
                            <td style="text-align:right"><b>' . number_format($grand_total, $format_no, ",", ".") . '</b></td>
                        </tr>';

                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                    <table id="customers" style="margin-top:10px; font-size:10px; border: 2px solid black;border-collapse: collapse;">
                        <tr>
                            <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">
                                Total Invoice Value in Words: <br><br>
                                ' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . ' <br><br><br>
                                Payment Information <br>
                                Please transfer the Grand Total Amount to the following bank account: <br>
                                <table style="width:100%; margin-top:10px; font-size:10px; border: none;">
                                    <tr>
                                        <td style="width:15%; text-align:left; border: none;"><b>Bank Name</td>
                                        <td style="width:2%; text-align:left; border: none;"><b>:</td>
                                        <td style="width:90%; text-align:left; border: none;"><b>' . $record['bank_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Account Name</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['account_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: none;"><b>Bank Account</td>
                                        <td style="text-align:left; border: none;"><b>:</td>
                                        <td style="text-align:left; border: none;"><b>' . $record['bank_account'] . '</td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table id="customers" style="margin-top:10px; font-size:12px;"><br><br>
                        <tr>
                            <th width="420" style="text-align:left; vertical-align:top;" rowspan="4">
                            Note. <br><br>
                        </th>
                        </tr>
                        <tr>
                            <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                        </tr>
                        <tr>
                            <th style="height:150px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;">Finance Dept</th>
                        </tr>
                    </table>
                </div>';
            } else {
                $html .= '</table>';
            }

            $html .= '</div>';

            if (($i + 1) != $total_pages) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
        }

        $html .= '</div></body></html>';
        return $html;
    }

    public function print_dn($invoice, $is_hyperlink = null) // is_hyperlink for Show GL and link each transaction (Report_general_ledgers)
    {
        $invoice_no = base64_decode($invoice);
        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->like('a.number', $invoice_no);
        $this->db->order_by('a.trans_date', 'DESC');
        //$this->db->group_by('a.number');
        $total_invoice = $this->db->get()->result_array();

        $sales_invoices = $this->crud->read('sales_invoices', [], ["number" => $invoice_no]);
        $approval = $this->crud->read('approvals', [], ["table_name" => "sales_invoices"]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
       
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);
       
        if (!empty($approval->user_approval_2)) {
            $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        } else {
            $user_2 = (object) ["name" => ""];
        }
        
        if (!empty($approval->user_approval_3)) {
            $user_3 = $this->crud->read('users', [], ["username" => $approval->user_approval_3]);
        } else {
            $user_3 = (object) ["name" => ""];
        }
        
        
        if($sales_invoices->approved == 0){
            $users_input = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($sales_invoices->approved == 1) {
            $users_input = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($sales_invoices->approved == 2) {
            $users_input = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($sales_invoices->approved == 3) {
            $users_input = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '';
        } else {
            $users_input = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '<img src="' . base_url('assets/image/qrcode/' . $user_3->name . '.png') . '" width="80"/>';
        }
        
        //Config Page
        $rows = 25;
        $page = ceil(count($total_invoice) / $rows);
        //Generate QRcode
        $this->createQrcode(@$invoice_no, "assets/image/qrcode/");
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
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
                            <p>Display pages for 10 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
                }

        $no = 1;
        $hal = 1;
        $grand_total = 0;
        $grand_total_local = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as customer_name, a.item_no as item_number, a.item_name');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->like('a.number', $invoice_no);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(25, ($i * 25));
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
                                            <td width="100">' . $config_iso->doc_sales_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_sales_invoice . '</td>
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
                                    <h3><u style="padding:5px;">SALES INVOICING</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Customer Name</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Customer Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_order_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Sales Invoice No</td>
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
                                    </table>
                                </div>
                                <div style="width:100%; text-align: right; font-size:12px;">Page ' . $hal . '/' . $page . '</div>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">SO No</th>
                                        <th rowspan="2">DN No</th>
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
            $sub_total = 0;
            $sub_total_local = 0;
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

                if($record['currency'] == 'IDR'){
                    $format_no = 2;
                }else{
                    $format_no = 4;
                }

                $amount = ($record['total'] * $price);
                $sub_total += $record['total'];
                $sub_total_local += $amount;
                $grand_total += $record['total'];
                $grand_total_local += $amount;
                $discount = ($record['discount']);
                $down_payment = ($record['down_payment']);
                $dpp_total = (($sub_total - $down_payment) * 11/12);
                
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['sales_order_no'] . '</td>
                                <td>' . $record['delivery_note_no'] . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['qty']), 2) . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['price'], $format_no) . '</td>
                                <td style="text-align:right;">' . @number_format($record['total'], $format_no) . '</td>
                                <td>IDR</td>
                                <td style="text-align:right;">' . @number_format($amount, $format_no) . '</td>
                            </tr>';
                $no++;
            }
            
            if (($i + 1) == $page) {
                $html .= '<tr>
                            <th colspan="9" style="text-align:right">SUB TOTAL</th>
                            <th style="text-align:right">' . number_format($sub_total, $format_no) . '</th>
                            <th></th>
                            <th style="text-align:right">' . number_format($sub_total_local, $format_no) . '</th>
                          </tr>
                          <tr>
                            <th colspan="9" style="text-align:right">GRAND TOTAL</th>
                            <th style="text-align:right">' . number_format($grand_total, $format_no) . '</th>
                            <th></th>
                            <th style="text-align:right">' . number_format($grand_total_local, $format_no) . '</th>
                          </tr>';
            }else{
                $html .= '<tr>
                            <th colspan="9" style="text-align:right">SUB TOTAL</th>
                            <th style="text-align:right">' . number_format($sub_total, 2) . '</th>
                            <th></th>
                            <th style="text-align:right">' . number_format($sub_total_local, 2) . '</th>
                          </tr>';
            }
            
            $html .= '</table>';

            if (($i + 1) != $page) {
                $html .= '</div></div><div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM sales_invoice_journals a 
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
                    <div style="width:20%; float:left;">
                        &nbsp;
                    </div>
                    <div style="width:30%; float:left;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td style="font-weight:bold;">Discount</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format($discount, $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Sub Total</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(($grand_total - $discount), $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Down Payment</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(($down_payment), $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">DPP</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(($dpp_total), $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">VAT</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_vat'], $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">PPH</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_pph'], $format_no) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Grand Total</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(((((@$grand_total - $discount) - $down_payment) + $records[0]['total_vat']) - $records[0]['total_pph']), $format_no) . '</td>
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
                        <th style="height:60px;">'. $users_input. '</th>
                        <th style="height:60px;">'. $users_1. '</th>
                        <th style="height:60px;">'. $users_2. '</th>
                        <th style="height:60px;">'. $users_3. '</th>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User</th>
                        <th style="height:20px; text-align:center;">'. $user_1->name .'<br><hr style="width:60%;margin-left:20%;">Purchasing</th>
                        <th style="height:20px; text-align:center;">'. $user_2->name .'<br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                        <th style="height:20px; text-align:center;">'. $user_3->name .'<br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>';
        
        if (empty($is_hyperlink) && $is_hyperlink !== 'GL') {
            $html .= "<script>window.print()</script></body>";
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
            header("Content-Disposition: attachment; filename=sales_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice'));
        $filter_delivery_note_no = base64_decode($this->input->get('filter_delivery_note_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_faktur_no = base64_decode($this->input->get('filter_faktur_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        // if no filter_type, then get data per this month same as datatables()
        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        } else {
            $this->db->where("a.trans_date between '$date_from' and '$date_to'");
        }
        $this->db->like('a.number', $filter_sales_invoice);
        $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.faktur_no', $filter_faktur_no);
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
                                <small>REPORT SALES INVOICING</small><br>
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
                    <th>Sales Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Total Invoice</th>
                    <th>Discount</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th colspan="2">Grand Total</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select('a.*');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_invoice'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['discount'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 4) . '</td>
                            <td colspan="2" style="text-align:right;">' . number_format($data['total_grand'], 4) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['number'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>DN No</th>
                            <th>SO No</th>
                            <th>Customer Order No</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            foreach ($details as $detail) {
                if($detail['currency'] == 'IDR'){
                    $format = 2;
                }else{
                    $format = 4;
                }
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['delivery_note_no'] . '</td>
                                <td>' . $detail['sales_order_no'] . '</td>
                                <td>' . $detail['customer_order_no'] . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2, ',', '.') . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], $format, ',', '.') . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 2, ',', '.') . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_summary($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice'));
        $filter_delivery_note_no = base64_decode($this->input->get('filter_delivery_note_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_faktur_no = base64_decode($this->input->get('filter_faktur_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        // if no filter_type, then get data per this month same as datatables()
        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        } else {
            $this->db->where("a.trans_date between '$date_from' and '$date_to'");
        }
        $this->db->like('a.number', $filter_sales_invoice);
        $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.faktur_no', $filter_faktur_no);
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
                                <small>REPORT SALES INVOICING</small><br>
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
                    <th>Sales Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th colspan="2">Grand Total</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select('a.*, SUM(a.qty) as qty');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.price');
            $this->db->order_by('a.item_no', 'ASC');
            // $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 4) . '</td>
                            <td colspan="2" style="text-align:right;">' . number_format($data['total_grand'], 4) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['number'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            $nod = 1;
            foreach ($details as $detail) {
                if($detail['currency'] == 'IDR'){
                    $decimal_no = 2;
                }else{
                    $decimal_no = 4;
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2, ',', '.') . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], $decimal_no, '.', ',') . '</td>
                                <td style="text-align:right">' . number_format(($detail['price'] * $detail['qty']), 2, ",", ".") . '</td>

                            </tr>';
                $nod++;
            }
            $html .= '<tr><td colspan="8"></td></tr>'; // separator

            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printDetail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_invoices_details_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice'));
        $filter_delivery_note_no = base64_decode($this->input->get('filter_delivery_note_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_faktur_no = base64_decode($this->input->get('filter_faktur_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        // if no filter_type, then get data per this month same as datatables()
        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
            $period_due =  "-";
        } elseif ($filter_type == "PAY") {
            $period_due =  $filter_due_date_from . ' to ' . $filter_due_date_to;
            $periode =  "-";
        } else {
            $periode =  $date_from . ' to ' . $date_to;
            $period_due =  "-";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name, c.name as journal_type_name, d.total_sub, e.account_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id');
        $this->db->join("(SELECT number, SUM(total) as total_sub FROM sales_invoices GROUP BY number) d", 'a.number = d.number');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        } else {
            $this->db->where("a.trans_date between '$date_from' and '$date_to'");
        }
        $this->db->like('a.number', $filter_sales_invoice);
        $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.faktur_no', $filter_faktur_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.customer_id', 'ASC');
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
                <h2>REPORT SALES INVOICE DETAIL</h2>
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
                    <th>Sales Invoice No</th>
                    <th>Customer Name</th>
                    <th>Trans Date</th>
                    <th>Receipt Due</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th>Grand Total</th>
                    <th>Remarks</th>
                    <th>Delivery Note</th>
                    <th>SO No</th>
                    <th>Customer Order No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
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
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . $data['total_sub'] . '</td>
                            <td style="text-align:right;">' . $data['total_vat'] . '</td>
                            <td style="text-align:right;">' . $data['total_pph'] . '</td>
                            <td style="text-align:right;">' . ($data['total_sub'] + $data['total_vat'] - $data['total_pph']) . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['delivery_note_no'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td>' . $data['customer_order_no'] . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['qty'] . '</td>
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

    public function printJournal($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_invoices_journals_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice'));
        $filter_delivery_note_no = base64_decode($this->input->get('filter_delivery_note_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_faktur_no = base64_decode($this->input->get('filter_faktur_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        // if no filter_type, then get data per this month same as datatables()
        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
            $period_due =  "-";
        } elseif ($filter_type == "PAY") {
            $period_due =  $filter_due_date_from . ' to ' . $filter_due_date_to;
            $periode =  "-";
        } else {
            $periode =  $date_from . ' to ' . $date_to;
            $period_due =  "-";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.trans_date, b.due_date, b.currency, c.name as customer_name, d.name as journal_type_name, e.account_name');
        $this->db->from('sales_invoice_journals a');
        $this->db->join('sales_invoices b', 'a.number = b.number');
        $this->db->join('customers c', 'b.customer_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("b.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("b.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        } else {
            $this->db->where("b.trans_date between '$date_from' and '$date_to'");
        }
        $this->db->like('b.number', $filter_sales_invoice);
        $this->db->like('b.delivery_note_no', $filter_delivery_note_no);
        $this->db->like('b.customer_id', $filter_customer);
        $this->db->like('b.faktur_no', $filter_faktur_no);
        $this->db->like('b.status', $filter_status);
        $this->db->group_by('a.number');
        $this->db->group_by('a.account_number');
        $this->db->order_by('b.customer_id', 'ASC');
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
                <h2>REPORT SALES INVOICE JOURNAL</h2>
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
                    <th>Sales Invoice No</th>
                    <th>Customer Name</th>
                    <th>Trans Date</th>
                    <th>Receipt Due</th>
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
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
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

    // function exportCsv($id) {

    //     $ids = explode(',', base64_decode($id));

    //     // Buffer output
    //     ob_start();
    
    //     // Set headers for CSV output
    //     header("Content-Type: text/csv; charset=utf-8");
    //     header("Content-Disposition: attachment; filename=csv_sales_invoicing" . date("Ymd") . ".csv");
    
    //     // Initialize output
    //     $output = fopen('php://output', 'w');
    
    //     // Write the CSV headers once
    //     fputcsv($output, ['FK', 'KD_JNS_TRANSAKSI', 'FG_PENGGANTI', 'NOMOR_FAKTUR', 'MASA_PAJAK', 'TAHUN_PAJAK', 'TANGGAL_FAKTUR', 'NPWP', 'NAMA', 'ALAMAT_LENGKAP', 'JUMLAH_DPP', 'JUMLAH_PPN', 'JUMLAH_PPNBM', 'ID_KETERANGAN_TAMBAHAN', 'FG_UANG_MUKA', 'UANG_MUKA_DPP', 'UANG_MUKA_PPN', 'UANG_MUKA_PPNBM', 'REFERENSI', 'KODE_DOKUMEN_PENDUKUNG']);
    //     fputcsv($output, ['LT', 'NPWP', 'NAMA', 'JALAN', 'BLOK', 'NOMOR', 'RT', 'RW', 'KECAMATAN', 'KELURAHAN', 'KABUPATEN', 'PROVINSI', 'KODE_POS', 'NOMOR_TELEPON']);
    //     fputcsv($output, ['OF', 'KODE_OBJEK', 'NAMA', 'HARGA_SATUAN', 'JUMLAH_BARANG', 'HARGA_TOTAL', 'DISKON', 'DPP', 'PPN', 'TARIF_PPNBM', 'PPNBM']);
    
    //     //Config
    //     $config = $this->db->select('*')
    //                        ->from('config')
    //                        ->get()
    //                        ->row();
        
    //     // Fetch FK and LT data (ensure no duplicates)
    //     $this->db->select('a.faktur_code, a.fp_pengganti, b.npwp as cust_npwp, a.faktur_no, a.trans_date, b.name as cust_name, c.address, a.number as invoice_number, a.total_vat, a.total_sub, c.telp');
    //     $this->db->from('sales_invoices a');
    //     $this->db->join('customers b', 'a.customer_id = b.id');
    //     $this->db->join('customer_address c', 'b.id = c.customer_id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where_in('a.id', $ids); // Use where_in to handle array of IDs
    //     $this->db->group_by('a.number'); // Group by invoice number to avoid duplicates
    //     $invoiceRecords = $this->db->get()->result_array();
    
    //     // Write FK and LT rows
    //     foreach ($invoiceRecords as $invoice) {
    //         // FK row
    //         fputcsv($output, [
    //             'FK', $invoice['faktur_code'], $invoice['fp_pengganti'], $invoice['faktur_no'], date('m', strtotime($invoice['trans_date'])), date('Y', strtotime($invoice['trans_date'])),
    //             date('d/m/Y', strtotime($invoice['trans_date'])), $config->npwp, $invoice['cust_name'], $invoice['address'], $invoice['total_sub'], $invoice['total_vat'], '0', '0', '0', '0', '0', '0', $invoice['invoice_number'], '0'
    //         ]);
    
    //         // LT row
    //         fputcsv($output, [
    //             'LT', $invoice['cust_npwp'], $invoice['cust_name'], $invoice['address'], '-', '-', '0', '0', '-', '-', '-', '-', '-', $invoice['telp']
    //         ]);
    
    //         // Fetch items (OF rows) for this invoice
    //         $this->db->select('e.number as item_number, e.name as item_name, a.price, a.qty, a.total, a.discount');
    //         $this->db->from('sales_invoices a');
    //         $this->db->join('item_fg e', 'a.item_fg_id = e.id');
    //         $this->db->where('a.number', $invoice['invoice_number']); // Fetch items for the specific invoice
    //         $itemRecords = $this->db->get()->result_array();
    
    //         // Write OF rows (one per item)
    //         foreach ($itemRecords as $item) {
    //             fputcsv($output, [
    //                 'OF', $item['item_number'], $item['item_name'], $item['price'], $item['qty'], $item['total'], $item['discount'], '0', '0', '0', '0'
    //             ]);
    //         }
    //     }
    
    //     // Close output
    //     fclose($output);
    //     ob_end_flush();
    // }

    function exportAccurate($id) {

        $ids = explode(',', base64_decode($id));

        // Buffer output
        ob_start();
    
        // Set headers for CSV output
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=csv_to_accurate_sales_invoicing" . date("Ymd") . ".csv");
    
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
        $this->db->select('a.faktur_code, a.faktur_no, a.trans_date, b.id as cust_id, b.name as cust_name, c.address, a.number as invoice_number, a.total_vat, a.total_sub, a.total_grand, a.taxes');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address c', 'b.id = c.customer_id');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.id', $ids); // Use where_in to handle array of IDs
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
                'HEADER', $invoice['invoice_number'], $invoice['faktur_no'], date('d/m/Y', strtotime($invoice['trans_date'])), $invoice['cust_id'], $invoice['address'], $ppn,'Tidak', $invoice['faktur_no'], 'Ya', 
                '', '', '', '', '', '', '', '', '', '', 
                '', '', '', '', '', '', '', '', '', '', 
                '', '', '', '', ''
            ]);
    
            // Fetch items (OF rows) for this invoice
            $this->db->select('e.number as item_number, e.name as item_name, a.price, a.qty, a.total, a.discount, e.uom');
            $this->db->from('sales_invoices a');
            $this->db->join('item_fg e', 'a.item_fg_id = e.id');
            $this->db->where('a.number', $invoice['invoice_number']); // Fetch items for the specific invoice
            $itemRecords = $this->db->get()->result_array();
    
            // Write ITEM rows (one per item)
            foreach ($itemRecords as $item) {
                fputcsv($output, [
                    'ITEM', $item['item_number'], $item['item_name'], intval($item['qty']), $item['uom'], intval($item['price']), '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', 
                    '', '', '', '', '', '', '', '', '', '', '', '', '',
                ]);
            }

            $vat = $invoice['taxes'];
            $total_vat = $invoice['total_sub'] * ($vat / 100);
            $total_grand = $invoice['total_sub'] + $total_vat;

             // EXPENSE row
             fputcsv($output, [
                'EXPENSE', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ]);
            
            //SUBTOTAL
            fputcsv($output, [
                '', '', '', '', '', '', '', 'SUB TOTAL', intval($invoice['total_sub']), '', '', '', '', '', '', '', '', '', ''
            ]);
            //VAT
            fputcsv($output, [
                '', '', '', '', '', '', '', 'VAT', $total_vat, '', '', '', '', '', '', '', '', '', ''
            ]);
            //GRAND TOTAL
            fputcsv($output, [
                '', '', '', '', '', '', '', 'GRAND TOTAL', $total_grand, '', '', '', '', '', '', '', '', '', ''
            ]);
        }
    
        // Close output
        fclose($output);
        ob_end_flush();
    }

    // eCoretax Format XML
    public function export_ecoretax($si_no)
    {
        $si_nos = explode(',', base64_decode($si_no));
        
        $config = $this->db->select('*')->from('config')->get()->row();
        
        // Query Get Faktur
        $faktur_query = $this->db->select('a.faktur_code, 
            a.fp_pengganti, 
            b.npwp as cust_npwp, 
            a.faktur_no, 
            a.trans_date, 
            a.keterangan_tambahan, 
            a.cap_fasilitas, 
            a.bc_no, 
            b.name as cust_name, 
            c.address, c.email,
            a.number as invoice_number, 
            a.total_vat, 
            a.total_sub, 
            c.telp, 
            b.type as customer_type')
            ->from('sales_invoices a')
            ->join('customers b', 'a.customer_id = b.id')
            ->join('customer_address c', 'b.id = c.customer_id')
            ->where('a.deleted', 0)
            ->where_in('a.number', $si_nos)
            ->group_by('a.number')
            ->order_by('a.number', 'ASC')
            ->get()->result_array();

        // Query Detail Faktur
        $detailfaktur_query = $this->db->select('e.number as item_number, e.name as item_name,
            e.id as item_no, 
            e.number as product_no,
            a.price, 
            a.number as si_no,
            a.qty, 
            a.total, 
            a.total_discount, 
            a.taxes, 
            f.description as uom,
            e.hs_code')
            ->from('sales_invoices a')
            ->join('item_fg e', 'a.item_fg_id = e.id')
            ->join('uom f', 'e.uom = f.name', 'left')
            ->where_in('a.number', $si_nos)
            ->order_by('a.number', 'ASC')
            ->get()->result_array();
        
        // Group Detail Faktur by Invoice Number
        $groupedDetails = [];
        foreach ($detailfaktur_query as $detail) {
            $groupedDetails[$detail['si_no']][] = $detail;
        }

        // Set NPWP and IDTKU
        $npwp = preg_replace('/\D/', '', $config->npwp);
        $first_two_digits = substr($npwp, 0, 2);
        if (strlen($npwp) < 16 && $first_two_digits !== '00') { // Validasi jika NPWP tidak dimulai dengan '00' atau '0'
            $first_digit = substr($npwp, 0, 1);
            if ($first_digit !== '0') {
                $npwp_seller = '00' . $npwp;
            } else {
                $npwp_seller = '0' . $npwp;
            }
        } else {
            $npwp_seller = $npwp;
        }
        $idtku_seller = str_pad($npwp_seller, 22, '0', STR_PAD_RIGHT);

        // Build XML Structure
        $xml = new SimpleXMLElement('<TaxInvoiceBulk xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="TaxInvoice.xsd"></TaxInvoiceBulk>');
        $xml->addChild('TIN', htmlspecialchars($npwp_seller));
        $listFakturNode = $xml->addChild('ListOfTaxInvoice');

        foreach ($faktur_query as $faktur) 
        {
            $fakturNode = $listFakturNode->addChild('TaxInvoice');
            
            // value 'Tidak Ada' di aplikasi pajak tidak valid
            $additional_info = $faktur['keterangan_tambahan'];
            if (empty($additional_info) || strtolower($additional_info) == "tidak ada") {
                $additional_info = '';
            }
            
            $facility_stamp = $faktur['cap_fasilitas'];
            if (empty($facility_stamp) || strtolower($facility_stamp) == "tidak ada") {
                $facility_stamp = '';
            }
            
            // Perbarui nama tag utama
            $fakturNode->addChild('TaxInvoiceDate', $faktur['trans_date']);
            $fakturNode->addChild('TaxInvoiceOpt', 'Normal');
            $fakturNode->addChild('TrxCode', $faktur['faktur_code']);
            $fakturNode->addChild('AddInfo', $additional_info);
            $fakturNode->addChild('CustomDoc', $faktur['invoice_number']);
            $fakturNode->addChild('CustomDocMonthYear', date('mY', strtotime($faktur['trans_date'])));
            $fakturNode->addChild('RefDesc', $faktur['invoice_number']);
            $fakturNode->addChild('FacilityStamp', $facility_stamp);

            // NPWP dan ID Penjual
            $fakturNode->addChild('SellerIDTKU', $idtku_seller);

            // NPWP Pembeli
            $npwp_buyer = preg_replace('/\D/', '', $faktur['cust_npwp']);
            $first_two_digits_buyer = substr($npwp_buyer, 0, 2);
            if (strlen($npwp_buyer) < 16 && $first_two_digits_buyer !== '00') { // Validasi jika NPWP tidak dimulai dengan '00' atau '0'
                $first_digit = substr($npwp_buyer, 0, 1);
                if ($first_digit !== '0') {
                    $tin_buyer = '00' . $npwp_buyer;
                } else {
                    $tin_buyer = '0' . $npwp_buyer;
                }
            } else {
                $tin_buyer = $npwp_buyer;
            }
            $idtku_buyer = str_pad($tin_buyer, 22, '0', STR_PAD_RIGHT);
            
            // Informasi Pembeli
            $fakturNode->addChild('BuyerTin', $tin_buyer);
            $fakturNode->addChild('BuyerDocument', 'TIN');
            $fakturNode->addChild('BuyerCountry', ($faktur['customer_type'] == "LOCAL") ? "IDN" : "");
            $fakturNode->addChild('BuyerDocumentNumber', '-');
            $fakturNode->addChild('BuyerName', htmlspecialchars($faktur['cust_name']));
            $fakturNode->addChild('BuyerAdress', htmlspecialchars($faktur['address']));
            $fakturNode->addChild('BuyerEmail', $faktur['email']);
            $fakturNode->addChild('BuyerIDTKU', $idtku_buyer);

            // Ambil detail yang relevan dari array yang sudah dikelompokkan
            if (isset($groupedDetails[$faktur['invoice_number']])) {
                $detailNode = $fakturNode->addChild('ListOfGoodService');

                // Jika ada Product Name yang sama dan HARGA yang sama, maka dijadikan satu (Bu Nina)
                $mergedItems = [];
                foreach ($groupedDetails[$faktur['invoice_number']] as $detail) {
                    // Tambahkan price ke dalam key agar item dengan harga berbeda tidak digabung
                    $key = $detail['item_no'] . '_' . $detail['price']; 
                    
                    if (isset($mergedItems[$key])) {
                        // Jika item dan harga sama, jumlahkan qty, total, dan discount
                        $mergedItems[$key]['qty'] += $detail['qty'];
                        $mergedItems[$key]['total'] += $detail['total'];
                        $mergedItems[$key]['total_discount'] += $detail['total_discount']; // Jangan lupa jumlahkan diskon juga
                    } else {
                        // Jika belum ada, buat entri baru
                        $mergedItems[$key] = $detail;
                    }
                }

                foreach ($mergedItems as $detail) {
                    $itemNode = $detailNode->addChild('GoodService');

                    // Setting HS Code
                    $hs_code = !empty($detail['hs_code']) ? substr($detail['hs_code'], 0, 4) . "00" : "000000";
                    $hs_code_item = !empty($detail['hs_code']) ? "#".$detail['hs_code'] : ""; // -- ganti / menjadi #
                    
                    // Setting UOM
                    $uom = "UM.0033";
                    if ($detail['uom'] == "Kilogram") $uom = "UM.0003";
                    if ($detail['uom'] == "Gram") $uom = "UM.0004";
                    if ($detail['uom'] == "Liter") $uom = "UM.0007";
                    if ($detail['uom'] == "Meter") $uom = "UM.0013";
                    if ($detail['uom'] == "Lusin") $uom = "UM.0017";
                    if ($detail['uom'] == "Unit") $uom = "UM.0018";
                    if ($detail['uom'] == "Set") $uom = "UM.0019";
                    if ($detail['uom'] == "Lembar") $uom = "UM.0020";
                    if ($detail['uom'] == "Piece") $uom = "UM.0021";

                    // Nama Barang minta tolong ditambahkan menjadi >>productno/productname/hscode (Bu Nina)
                    $item_no   = htmlspecialchars($detail['item_no']);
                    $product_no= htmlspecialchars($detail['product_no']);
                    $item_name = htmlspecialchars($detail['item_name']);

                    // Perbarui tag dengan nilai yang sudah digabungkan
                    $itemNode->addChild('Opt', 'A');
                    $itemNode->addChild('Code', $hs_code);
                    $itemNode->addChild('Name', $product_no . '/' . $item_name . $hs_code_item);
                    $itemNode->addChild('Unit', $uom);
                    $itemNode->addChild('Price', round($detail['price'], 2));
                    $itemNode->addChild('Qty', round($detail['qty'], 2)); // Gunakan qty yang sudah dijumlahkan
                    $itemNode->addChild('TotalDiscount', round($detail['total_discount'], 2));
                    $itemNode->addChild('TaxBase', round($detail['total'] - $detail['total_discount'], 2)); // Gunakan total yang sudah dijumlahkan
                    $itemNode->addChild('OtherTaxBase', round(11/12 * ($detail['total'] - $detail['total_discount']), 2)); // Gunakan total yang sudah dijumlahkan
                    $itemNode->addChild('VATRate', round($detail['taxes'], 2));
                    $itemNode->addChild('VAT', round((11/12 * ($detail['total'] - $detail['total_discount'])) * ($detail['taxes']/100), 2));
                    $itemNode->addChild('STLGRate', 0);
                    $itemNode->addChild('STLG', 0);
                }
            }
        }

        // Filename
        $timestamp = date('Ymd_His');
        $filename  = 'tax_invoicing_ecoretax_' . $timestamp . '.xml';
        
        // Output format XML
        header('Content-Description: File Transfer');
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: public, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        echo $xml->asXML();
        exit();
    }

    // eCoretax Format Excel / CSV
    public function export_ecoretax_backup($si_no){
        $si_nos = explode(',', base64_decode($si_no));
        require 'assets/vendors/phpspreadsheet/vendor/autoload.php';
        $spreadsheet = new Spreadsheet();
        ob_end_clean();

        // var_dump($si_nos);
        // die;

        // **Sheet 1 - Faktur**
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Faktur');

        $config = $this->db->select('*')->from('config')->get()->row();
        
        $this->db->select('a.faktur_code, 
        a.fp_pengganti, 
        b.npwp as cust_npwp, 
        a.faktur_no, 
        a.trans_date, 
        a.keterangan_tambahan, 
        a.cap_fasilitas, 
        a.bc_no, 
        b.name as cust_name, 
        c.address, c.email,
        a.number as invoice_number, 
        a.total_vat, 
        a.total_sub, 
        c.telp, 
        b.type as customer_type');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address c', 'b.id = c.customer_id');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.number', $si_nos);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number','ASC');
        $faktur = $this->db->get()->result_array();

        $this->db->select('e.number as item_number, 
        e.name as item_name, 
        a.price, 
        a.number as si_no,
        a.qty, 
        a.total, 
        a.total_discount, 
        a.taxes, 
        f.description as uom');
        $this->db->from('sales_invoices a');
        $this->db->join('item_fg e', 'a.item_fg_id = e.id');
        $this->db->join('uom f', 'e.uom = f.name','left');
        $this->db->where_in('a.number', $si_nos);
        $this->db->order_by('a.number','ASC');
        $detailfaktur = $this->db->get()->result_array();

        $sheet1->setCellValue('A1', 'NPWP Penjual');
        $sheet1->setCellValueExplicit('C1', $config->npwp, DataType::TYPE_STRING);
        $sheet1->setCellValue('A3', 'Baris');
        $sheet1->setCellValue('B3', 'Tanggal Faktur');
        $sheet1->setCellValue('C3', 'Jenis Faktur');
        $sheet1->setCellValue('D3', 'Kode Transaksi');
        $sheet1->setCellValue('E3', 'Keterangan Tambahan');
        $sheet1->setCellValue('F3', 'Dokumen Pendukung');
        $sheet1->setCellValue('G3', 'Referensi');
        $sheet1->setCellValue('H3', 'Cap Fasilitas');
        $sheet1->setCellValue('I3', 'ID TKU Penjual'); 
        $sheet1->setCellValue('J3', 'NPWP/NIK Pembeli');
        $sheet1->setCellValue('K3', 'Jenis ID Pembeli');
        $sheet1->setCellValue('L3', 'Negara Pembeli');
        $sheet1->setCellValue('M3', 'Nomor Dokumen Pembeli');
        $sheet1->setCellValue('N3', 'Nama Pembeli');
        $sheet1->setCellValue('O3', 'Alamat Pembeli');
        $sheet1->setCellValue('P3', 'Email Pembeli');
        $sheet1->setCellValue('Q3', 'ID TKU Pembeli');

        $sheet1->mergeCells('A1:B1');
        $sheet1->getStyle('A1:Q3')->getFont()->setBold(true);

        $row = 4; 
        $number = 1;
        foreach ($faktur as $data) {
            $negara_pembeli = ($data['customer_type'] == "LOCAL") ? "IDN" : "";
            $sheet1->setCellValue('A' . $row, $number);
            $date = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date->getTimestamp());
            $sheet1->setCellValue('B' . $row, $excelDate);
            $sheet1->getStyle('B' . $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
            $sheet1->setCellValue('C' . $row, "Normal");
            $sheet1->setCellValue('D' . $row, $data['faktur_code']);
            $sheet1->setCellValue('E' . $row, $data['keterangan_tambahan']);
            $sheet1->setCellValue('F' . $row, $data['bc_no']);
            $sheet1->setCellValue('G' . $row, $data['invoice_number']);
            $sheet1->setCellValue('H' . $row, $data['cap_fasilitas']);
            $sheet1->setCellValue('I' . $row, $config->npwp);
            $sheet1->setCellValueExplicit('J' . $row, $data['cust_npwp'], DataType::TYPE_STRING);
            $sheet1->setCellValue('K' . $row, "TIN");
            $sheet1->setCellValue('L' . $row, $negara_pembeli);
            $sheet1->setCellValue('M' . $row, "-");
            $sheet1->setCellValue('N' . $row, $data['cust_name']);
            $sheet1->setCellValue('O' . $row, $data['address']);
            $sheet1->setCellValue('P' . $row, $data['email']);
            $sheet1->setCellValueExplicit('Q' . $row, $data['cust_npwp'], DataType::TYPE_STRING);
            $row++;
            $number++;
        }

        foreach (range('A', 'Q') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet1->setCellValue('A' . $row, 'END');

        // **Sheet 2 - DetailFaktur**
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('DetailFaktur');
        $spreadsheet->setActiveSheetIndex(1);

        // Header DetailFaktur
        $sheet2->setCellValue('A1', 'Baris');
        $sheet2->setCellValue('B1', 'Barang/Jasa');
        $sheet2->setCellValue('C1', 'Kode Barang Jasa');
        $sheet2->setCellValue('D1', 'Nama Barang/Jasa');
        $sheet2->setCellValue('E1', 'Nama Satuan Ukur');
        $sheet2->setCellValue('F1', 'Harga Satuan');
        $sheet2->setCellValue('G1', 'Jumlah Barang Jasa');
        $sheet2->setCellValue('H1', 'Total Diskon');
        $sheet2->setCellValue('I1', 'DPP');
        $sheet2->setCellValue('J1', 'DPP Nilai Lain');
        $sheet2->setCellValue('K1', 'Tarif PPN');
        $sheet2->setCellValue('L1', 'PPN');
        $sheet2->setCellValue('M1', 'Tarif PPnBM');
        $sheet2->setCellValue('N1', 'PPnBM');

        $row2 = 2; 
        $number2 = 0;
        $previous_si_no = "";
        foreach ($detailfaktur as $data2) {

            if ($previous_si_no !== $data2['si_no']) {
                $number2++;
            }

            if($data2['uom'] == "Kilogram"){
                $uom = "UM.0003";
            }elseif($data2['uom'] == "Gram"){
                $uom = "UM.0004";
            }elseif($data2['uom'] == "Liter"){
                $uom = "UM.0007";
            }elseif($data2['uom'] == "Meter"){
                $uom = "UM.0013";
            }elseif($data2['uom'] == "Lusin"){
                $uom = "UM.0017";
            }elseif($data2['uom'] == "Unit"){
                $uom = "UM.0018";
            }elseif($data2['uom'] == "Set"){
                $uom = "UM.0019";
            }elseif($data2['uom'] == "Lembar"){
                $uom = "UM.0020";
            }elseif($data2['uom'] == "Piece"){
                $uom = "UM.0021";
            }else{
                $uom = "UM.0033";
            }

            $sheet2->setCellValue('A' . $row2, $number2);
            $sheet2->setCellValue('B' . $row2, "A");
            $sheet2->setCellValue('C' . $row2, "000000");
            $sheet2->setCellValue('D' . $row2, $data2['item_number']);
            $sheet2->setCellValue('E' . $row2, $uom);
            $sheet2->setCellValue('F' . $row2, $data2['price']);
            $sheet2->setCellValue('G' . $row2, $data2['qty']);
            $sheet2->setCellValue('H' . $row2, $data2['total_discount']);
            $sheet2->setCellValue('I' . $row2, round($data2['total'] - $data2['total_discount']));
            $sheet2->setCellValue('J' . $row2, round(11/12 * ($data2['total'] - $data2['total_discount'])));
            $sheet2->setCellValue('K' . $row2, $data2['taxes']);
            $sheet2->setCellValue('L' . $row2, round((11/12 * ($data2['total'] - $data2['total_discount'])) * ($data2['taxes']/100)));
            $sheet2->setCellValue('M' . $row2, 0);
            $sheet2->setCellValue('N' . $row2, (0*(11/12 * ($data2['total'] - $data2['total_discount']))) / 100);

            $sheet2->getStyle('H' . $row2)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->getStyle('I' . $row2)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->getStyle('J' . $row2)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->getStyle('L' . $row2)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->getStyle('N' . $row2)->getNumberFormat()->setFormatCode('0.00');

            
            $previous_si_no = $data2['si_no'];
            $row2++;
            // $number2++;
        }

        foreach (range('A', 'N') as $columnID2) {
            $sheet2->getColumnDimension($columnID2)->setAutoSize(true);
        }

        $sheet2->setCellValue('A' . $row2, 'END');

        // Kembali ke Sheet Faktur
        $spreadsheet->setActiveSheetIndex(0);

        // **Simpan dan Download**
        $writer = new Xlsx($spreadsheet);
        $filename = 'tax_invoicing_ecoretax.xlsx';

        // Header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Pragma: public');
        header('Expires: 0');

        // Output ke browser
        $writer->save('php://output');
        exit();
    }
}
