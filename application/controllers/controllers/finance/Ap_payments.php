<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Ap_payments extends CI_Controller
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
        // //Validasi Form
        // $this->form_validation->set_rules('purchase_invoice', 'Purchase Invoice', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/ap_payments');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $payment_no = base64_decode($number);
        $this->db->select('a.*, b.account_name');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.payment_no', $payment_no);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readJournals($number, $journal_type_id = "", $bank_account = "")
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("ap_payment_journals", [], ["payment_no" => $number], "", "flag", "asc");

        if (count($reads) > 0) {
            echo json_encode($reads);
        }
    }

    function readExchangeRate()
    {
        $payment_date = $this->input->post('payment_date');
        $currency = $this->input->post('currency');
        
        $search_date = date("d", strtotime($payment_date));
        if($search_date == "31"){
          $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
        }
        
        $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

        if ($exchange) {
            $amount = $exchange->middle;
        } else {
            $amount = 0;
        }

        echo "Rp. " . number_format($amount, 2);
    }

    public function calculateJournal($journal_type_id = "", $bank_account = "")
    {
        $journal_type_id = base64_decode($journal_type_id);
        $bank_account = base64_decode($bank_account);

        $banks = $this->crud->query("SELECT a.*, b.account_name FROM account_banks a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.bank_account = '$bank_account'");

        $jsonDatas = json_decode(file_get_contents("json/ap_payments.json"), true);
        $total = 0;
        $grand_total = 0;
        $flag = 1;
        $mergedData = array();
        foreach ($jsonDatas as $jsonData) {
            $account_number = $jsonData["account_number"];
            $account_name = $jsonData["account_name"];
            $account_type = $jsonData["account_type"];
            $description = $jsonData["description"];
            $total = $jsonData["payment"];
            $currency = $jsonData['currency'];
            $payment_date = $jsonData['payment_date'];

            $search_date = date("d", strtotime($payment_date));
            if($search_date == "31"){
              $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            }

            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            if ($currency != "IDR") {
                if ($exchange) {
                    $amount = ($total * $exchange->middle);
                } else {
                    $amount = 0;
                }
            } else {
                $amount = $total;
            }

            if (isset($mergedData[$account_number])) {
                // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number]["debit"] += $total;
                    $mergedData[$account_number]["local_debit"] += $amount;
                    
                    $grand_total += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number]["credit"] += $total;
                    $mergedData[$account_number]["local_credit"] += $amount;

                    $grand_total -= $total;
                }
            } else {
                // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => $total,
                        "credit" => 0,
                        "local_debit" => round($amount, 2),
                        "local_credit" => 0,
                        "flag" => $flag,
                    );

                    $grand_total += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => 0,
                        "credit" => $total,
                        "local_debit" => 0,
                        "local_credit" => round($amount, 2),
                        "flag" => $flag,
                    );
                    $grand_total -= $total;
                }
            }

            $flag++;
        }

        $arr = array_values($mergedData);

        foreach ($banks as $bank) {
            if ($currency != "IDR") {
                if ($exchange) {
                    $amount = ($grand_total * $exchange->middle);
                } else {
                    $amount = 0;
                }
            } else {
                $amount = $grand_total;
            }

            $arr[] = array(
                "account_number" => $bank->account_number,
                "account_name" => $bank->account_name,
                "debit" => "0.00",
                "credit" => $grand_total,
                "local_debit" => 0,
                "local_credit" => round($amount, 2),
                "flag" => $flag,
            );
        }

        echo json_encode($arr);
    }

    // public function readInvoiceType()
    // {
    //     $supplier_id = $this->input->get('supplier_id');
    //     $payment_type = $this->input->get('payment_type');

    //     if ($payment_type == "PURCHASE") {
    //         $where_por = "por_no != '-'";
    //     } else {
    //         $where_por = "por_no = '-'";
    //     }

    //     $records = $this->crud->query("SELECT DISTINCT `number`, journal_type_id, trans_date, invoice_no, due_date FROM purchase_invoices WHERE supplier_id = '$supplier_id' and `status` = 0");
    //     echo json_encode($records);
    // }

    public function readInvoiceType()
    {
        $supplier_id = $this->input->get('supplier_id');
        $payment_type = $this->input->get('payment_type');

        if ($payment_type == "PURCHASE") {
            $where_por = "por_no != '-'";
        } else {
            $where_por = "por_no = '-'";
        }

        $records = $this->crud->query("SELECT DISTINCT `number`, journal_type_id, trans_date, invoice_no, due_date FROM purchase_invoices WHERE supplier_id = '$supplier_id' and `status` = 0");
        
        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readPayments($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT payment_no FROM ap_payments WHERE supplier_id = '$supplier_id' ORDER BY `payment_no` ASC");
        echo json_encode($data);
    }

    public function readInvoices($supplier_id)
    {
        $date_now = date("Y-m-t");
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT `purchase_invoice` FROM ap_payments WHERE supplier_id = '$supplier_id' and `status` = 0 ORDER BY `purchase_invoice` ASC");
        echo json_encode($data);
    }

    public function readDp()
    {
        $supplier_id = $this->input->post('supplier_id');
        $purchase_invoice = $this->input->post('purchase_invoice');

        $this->db->select('a.*, b.account_name');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('a.purchase_invoice', $purchase_invoice);
        // $this->db->where('a.journal_type_id', '20230823000001');
        //$this->db->where('a.account_number', '1151101');
        $this->db->where('a.status_dp', '0');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    // public function number($trans_date)
    // {
    //     $datenow    = "AP-" . date("Ym", strtotime(base64_decode($trans_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . "-" . $autoID;
    // }

    public function number($trans_date, $bank_code)
    {
        $decoded_date = base64_decode($trans_date);
        $year = date("y", strtotime($decoded_date));
        $month = date("m", strtotime($decoded_date));
        // $bank_code = base64_decode($bank_code);
        $datenow    = $bank_code."/".$month."-".$year."/"."K";
        $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, 0, 3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        echo $autoID."/".$datenow;
    }

    public function datatablesTemp()
    {
        $purchase_invoice = base64_decode($this->input->get('purchase_invoice'));
        $purchase_invoice_ex = explode(",", $purchase_invoice);

        // var_dump($purchase_invoice_ex);
        // die;

        $this->db->select("number, journal_type_id, invoice_no, currency, (SUM(CASE WHEN account_type = 'DEBIT' THEN total ELSE -total END) + total_vat - total_pph) as total");
        $this->db->from('purchase_invoices');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where_in('number', $purchase_invoice_ex);
        $this->db->group_by('number');
        $this->db->order_by('number', 'asc');
        $records = $this->db->get()->result_array();

        $total_payment = 0;
        foreach ($records as $record) {
            $total_payment += $record['total'];
            $journal_type_id = $record['journal_type_id'];
            $number = $record['number'];

            $journal = $this->crud->query("SELECT a.*, a.flag, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.journal_type_id = '$journal_type_id' AND a.ap_payment = 'YES'");

            $ap_payment = $this->crud->query("SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE purchase_invoice = '$number' GROUP BY purchase_invoice, account_number ORDER BY payment DESC");

            $obj[] = array(
                "purchase_invoice" => $record['number'],
                "supplier_invoice" => $record['invoice_no'],
                "currency" => $record['currency'],
                "amount" => $record['total'],
                "balance" => ($record['total'] - @$ap_payment[0]->payment),
                "payment" => ($record['total'] - @$ap_payment[0]->payment),
                "account_number" => @$journal[0]->account_number,
                "account_name" => @$journal[0]->account_name,
                "account_type" => "DEBIT",
            );
        }

        $arr['rows'] = @$obj;
        $arr['total_payment'] = round($total_payment, 2);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

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
            $this->db->select("a.*, d.number as gl_no, b.name as supplier_name, SUM(CASE WHEN a.account_type = 'DEBIT' THEN payment ELSE -payment END) as total_ap, 
            (CASE WHEN a.journal_type_id is null THEN c.journal_type_id ELSE a.journal_type_id END) as journal_type , GROUP_CONCAT(DISTINCT REPLACE(a.purchase_invoice, ' ', '') SEPARATOR ',') as purchase_invoices");
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('purchase_invoices c', 'a.purchase_invoice = c.number', 'left');
            $this->db->join('journal_postings d', 'a.payment_no = d.document_no', 'left');
            $this->db->like('a.payment_type', $filter_payment_type);
            if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
                $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
            }else{
                $this->db->where("a.payment_date between '$date_from' and '$date_to'");
            }
            $this->db->like('a.payment_no', $filter_payment_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.purchase_invoice', $filter_invoice_no);
            $this->db->like('a.bank_account', $filter_bank_no);
            $this->db->like('a.payment_by', $filter_payment_by);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_no', 'DESC');
            $this->db->group_by('a.payment_no');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        } else {
            $payment_no = base64_decode($this->input->get('payment_no'));

            $this->db->select('*');
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            //$this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');

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

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            if (@$post['id'] != "") {
                $send = $this->crud->update('ap_payments', ["id" => $post['id']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('ap_payments', $post);
                if ($send) {
                    if ($post['amount'] == $post['payment']) {
                        $this->crud->update('purchase_invoices', ["number" => $post['purchase_invoice']], ["status" => 1]);
                    }

                    if ($post['balance'] == $post['payment']) {
                        $this->crud->update('ap_payments', ["payment_no" => $post['purchase_invoice']], ["status_dp" => 1]);
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
        file_put_contents('json/ap_payments.json', $jsonData);
        file_put_contents('json/ap_payment_journals.json', $jsonData2);
    }

    public function createJournals()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $purchase_invoice_journals = $this->crud->read('ap_payment_journals', [], ["payment_no" => $post['payment_no'], "account_number" => $post['account_number']]);

            if (@$purchase_invoice_journals->id != "") {
                $send = $this->crud->update('ap_payment_journals', ["payment_no" => $post['payment_no'], "account_number" => $post['account_number']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('ap_payment_journals', $post);
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
            $send = $this->crud->update('ap_payments', ["payment_no" => $post['payment_no']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('ap_payments', array("id" => $data['id']));

        $this->crud->update('ap_payments', ["payment_no" => $data['purchase_invoice']], ["status_dp" => 0]);
        echo $send;
    }

    public function deleteJournal()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('ap_payment_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();

        $ap_payments = $this->crud->reads("ap_payments", [], ["payment_no" => $data['payment_no']]);
        foreach ($ap_payments as $ap_payment) {
            $this->crud->update("purchase_invoices", [
                "number" => $ap_payment->purchase_invoice,
            ], ["status" => 0]);

            $this->crud->update('ap_payments', ["payment_no" => $ap_payment->purchase_invoice], ["status_dp" => 0]);
        }

        $send = $this->crud->delete('ap_payments', $data);
        $send = $this->crud->delete('ap_payment_journals', ["payment_no" => $data['payment_no']]);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'supplier_code' => $data->val($i, 2),
                'payment_type' => $data->val($i, 3),
                'payment_date' => $data->val($i, 4),
                'payment_by' => $data->val($i, 5),
                'note' => $data->val($i, 6),
                'journal_number' => $data->val($i, 7),
                'bank_account' => $data->val($i, 8),
                'purchase_invoice' => $data->val($i, 9),
                'supplier_invoice' => $data->val($i, 10),
                'currency' => $data->val($i, 11),
                'amount' => $data->val($i, 12),
                'balance' => $data->val($i, 13),
                'payment' => $data->val($i, 14),
                'remark' => $data->val($i, 15),
                'account_number' => $data->val($i, 16),
                'account_type' => $data->val($i, 17),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/ap_payments.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/ap_payments.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/ap_payments.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number
            $supplier = $this->crud->read('suppliers', [], ["number" => $data['supplier_code']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);
            $journal_types = $this->crud->read('journal_types', [], ["number" => $data['journal_number']]);
            //$ap_payments = $this->crud->read('ap_payments', [], ["purchase_invoice" => $data['purchase_invoice']]);

            if (empty($supplier->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier No " . $data['supplier_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($journal_types->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Journal Type Code " . $data['journal_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($account_coa->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " Not Found", "theme" => "error"));
                // } elseif (!empty($ap_payments->id)) {
                //     echo json_encode(array("title" => "Duplicated", "message" => "Purchase Invoice " . $data['purchase_invoice'] . " Duplicate Data", "theme" => "error"));
            } else {
                $ap_payment_no = $this->crud->reads('ap_payments', [], ["supplier_id" => $supplier->id, "payment_date" => $data['payment_date'], "purchase_invoice" => $data['purchase_invoice']]);

                if (count($ap_payment_no) > 0) {
                    $payment_no = $ap_payment_no[0]->payment_no;
                } else {
                    $datenow    = "AP-" . date("Ymd", strtotime($data['payment_date']));
                    $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
                    $rowID      = $sqlGetID->row();
                    $kode       = $rowID->kode;
                    if ($kode == NULL) {
                        $autoID = sprintf("%04s", $kode + 1);
                    } else {
                        $urutan = (int) substr($kode, -4);
                        $urutan++;
                        $autoID = sprintf("%04s", $urutan);
                    }
                    $payment_no = $datenow . "-" . $autoID;
                }

                $data_final = array(
                    "payment_no" => $payment_no,
                    "supplier_id" => $supplier->id,
                    "journal_type_id" => $journal_types->id,
                    "payment_type" => $data['payment_type'],
                    "payment_date" => $data['payment_date'],
                    "payment_by" => $data['payment_by'],
                    "note" => $data['note'],
                    "bank_account" => $data['bank_account'],
                    "purchase_invoice" => $data['purchase_invoice'],
                    "supplier_invoice" => $data['supplier_invoice'],
                    "currency" => $data['currency'],
                    "amount" => $data['amount'],
                    "balance" => $data['balance'],
                    "payment" => $data['payment'],
                    "remarks" => $data['remark'],
                    "account_number" => $data['account_number'],
                    "account_type" => $data['account_type'],
                );

                //Simpan Data
                $send   = $this->crud->create('ap_payments', $data_final);

                $account_number = $data['account_number'];
                $ap_payment_journals = $this->crud->reads('ap_payment_journals', [], ["payment_no" => $payment_no, "account_number" => $account_number]);

                $sqlJournalMax = $this->db->query("SELECT max(flag) as kode FROM ap_payment_journals WHERE payment_no = '$payment_no'");
                $rowJournalMax = $sqlJournalMax->row();

                $sqlJournal = $this->db->query("SELECT account_number, SUM(debit) as debit, SUM(credit) as credit FROM ap_payment_journals WHERE payment_no = '$payment_no' AND account_number = '$account_number' GROUP BY account_number");
                $rowJournal = $sqlJournal->row();

                if (count($ap_payment_journals) == 0) {
                    if ($data['account_type'] == "DEBIT") {
                        $debit = $data['payment'];
                        $credit = 0;
                    } else {
                        $debit = 0;
                        $credit = $data['payment'];
                    }

                    $arr = array(
                        "payment_no" => $payment_no,
                        "account_number" => $account_coa->account_number,
                        "account_name" => $account_coa->account_name,
                        "debit" => $debit,
                        "credit" => $credit,
                        "flag" => ($rowJournalMax->kode + 1),
                    );

                    $this->crud->create('ap_payment_journals', $arr);
                } else {
                    if ($data['account_type'] == "DEBIT") {
                        $debit = ($rowJournal->debit + $data['payment']);
                        $credit = 0;
                    } else {
                        $debit = 0;
                        $credit = ($rowJournal->credit + $data['payment']);
                    }

                    $arr = array(
                        "debit" => $debit,
                        "credit" => $credit
                    );

                    $send = $this->crud->update('ap_payment_journals', ["payment_no" => $payment_no, "account_number" => $account_number], $arr);
                }

                echo $send;
            }
        }
    }

    public function print_voucher($payment)
    {
        $payment_no = base64_decode($payment);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_no', $payment_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $payment_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 40;
        $page = ceil(count($payment_total) / $rows);
        //Generate QRcode
        // $this->createQrcode(@$payment_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $payment_no . '</title>
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
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 40 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 95</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, c.bank_name');
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('account_banks c', 'a.bank_account = c.bank_account');
            $this->db->like('a.payment_no', $payment_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_date', 'DESC');
            //$this->db->group_by('a.payment_no');
            $this->db->limit(40, ($i * 40));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $payment_date = $records[0]['payment_date'];
            $currency = $records[0]['currency'];

            $search_date = date("d", strtotime($payment_date));
            if($search_date == "31"){
              $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            }

            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }
            // <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $payment_no . '.png') . '" width="60"/></td>
            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ap_payment . '</td>
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
                        <div style="border: none; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>PAYMENT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Pay To</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Payment By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_by'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Payment No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="80">Payment Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($records[0]['payment_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Bank Account</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_account'] . '</b></td>
                                        </tr>
                                         <tr>
                                            <td width="50">Bank Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:25%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to IDR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Purchase Invoice No</th>
                                        <th>Supplier Invoice No</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Payment</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total += $record['payment'];
                    $subtotal += $record['payment'];
                } else {
                    $grand_total -= $record['payment'];
                    $subtotal -= $record['payment'];
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['purchase_invoice'] . '</td>
                                <td>' . $record['supplier_invoice'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['balance'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['payment'], 2) . '</td>
                            </tr>';
                $no++;
            }

            // if (($i + 1) == $page) {
            //     $html_grand_total = '<tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($subtotal, 2) . '</th>
            //             </tr>';
            // }else{
            //     $html_grand_total = "";
            // }

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">SUB TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //             '.$html_grand_total.'
            //         </table>';

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //         </table>';

            if (($i + 1) == $page) { 
                $html .= '  <tr>
                                <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            </tr>';
            }
            
            $html .= '</table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        //<td>' . $this->convertcurrency->convertCurrencyToWords($subtotal, $records[0]['currency']) . '</td>
        $html .= '<div style="width:100%; float:left;">
                        <table id="customers" style="margin-top:10px;">
                            <tr>
                                <th style="text-align:center;">Amount in Words</th>
                                <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . '</td>
                               
                            </tr>
                        </table>
                        <p style="font-size:12px;"><i>Note: ' . @$records[0]['note'] . '</i>
                        <i>*This Payment Voucher was prepared by ' . $config->name . '</i></p>
                    </div>
                    <div style="width:100%; float:left; margin-bottom:20px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td rowspan="2" style="font-weight:bold;">Account No</td>
                                <td rowspan="2" style="font-weight:bold;">Account Name</td>
                                <td rowspan="2" style="font-weight:bold;">Description</td>
                                <td colspan="2" style="font-weight:bold;">Original Currency</td>
                                <td colspan="2" style="font-weight:bold;">Local Currency</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
            $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM ap_payment_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.payment_no = '$payment_no' ORDER BY a.flag ASC");

            $total_debit = 0;
            $total_credit = 0;
            $local_total_debit = 0;
            $local_total_credit = 0;
            foreach ($journals as $journal) {

                $total_debit += $journal->debit;
                $total_credit += $journal->credit;
                $local_total_debit += $journal->local_debit;
                $local_total_credit += $journal->local_credit;

                $html .= '  <tr>
                                <td>' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td>' . $journal->description . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_credit, 2) . '</td>
                            </tr>';
            }

            $html .= '      <tr>
                                <td colspan="3"><b>BALANCE TOTAL</b></td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>';

            $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Assistant Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Finance Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>
                </div>
            </div>';

        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('a.payment_no', $filter_payment_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.payment_by', $filter_payment_by);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $this->db->group_by('a.payment_no');
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
                                <small>REPORT AP PAYMENT</small><br>
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
                    <th>Payment Type</th>
                    <th colspan="2">Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th colspan="2">Note</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $payment_no = $data['payment_no'];

            $this->db->select('*');
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            $this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['payment_type'] . '</td>
                            <td colspan="2">' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td colspan="2">' . $data['note'] . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['payment_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Purchase Invoice</th>
                            <th>Supplier Invoice</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                            <th>Account No</th>
                            <th>Debt/Credit</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['purchase_invoice'] . '</td>
                                <td>' . $detail['supplier_invoice'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['amount'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['balance'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['payment'], 2)  . '</td>
                                <td>' . $detail['remarks'] . '</td>
                                <td>' . $detail['account_number'] . '</td>
                                <td>' . $detail['account_type'] . '</td>
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

    public function printDetail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_detail_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, c.name as journal_type_name, d.account_name, e.bank_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id', 'left');
        $this->db->join('account_coa d', 'a.account_number = d.account_number', 'left');
        $this->db->join('account_banks e', 'a.bank_account = e.bank_account', 'left');
        $this->db->like('a.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('a.payment_no', $filter_payment_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.payment_by', $filter_payment_by);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.payment_no', 'ASC');
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
                                <small>REPORT AP PAYMENT DETAIL</small><br>
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
                <h2>REPORT AP PAYMENT DETAIL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_payment_date_from . ' - ' . $filter_payment_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th>Note</th>
                    <th>Purchase Invoice</th>
                    <th>Supplier Invoice</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Payment</th>
                    <th>Remark</th>
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
                            <td>' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['purchase_invoice'] . '</td>
                            <td>' . $data['supplier_invoice'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['amount'] . '</td>
                            <td>' . $data['balance'] . '</td>
                            <td>' . $data['payment'] . '</td>
                            <td>' . $data['remarks'] . '</td>
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
            header("Content-Disposition: attachment; filename=ap_payment_journal_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.purchase_invoice, b.payment_date, b.payment_by, b.note, b.bank_account, c.name as supplier_name, d.name as journal_type_name, e.account_name, f.bank_name');
        $this->db->from('ap_payment_journals a');
        $this->db->join('ap_payments b', 'a.payment_no = b.payment_no');
        $this->db->join('suppliers c', 'b.supplier_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('account_banks f', 'b.bank_account = f.bank_account', 'left');
        $this->db->like('b.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("b.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('b.payment_no', $filter_payment_no);
        $this->db->like('b.supplier_id', $filter_supplier);
        $this->db->like('b.purchase_invoice', $filter_invoice_no);
        $this->db->like('b.bank_account', $filter_bank_no);
        $this->db->like('b.payment_by', $filter_payment_by);
        $this->db->group_by('a.payment_no');
        $this->db->group_by('a.account_number');
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('a.payment_no', 'ASC');
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
                <h2>REPORT AP PAYMENT JOURNAL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_payment_date_from . ' - ' . $filter_payment_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Journal Type</th>
                    <th rowspan="2">Payment No</th>
                    <th rowspan="2">Payment Date</th>
                    <th rowspan="2">Payment By</th>
                    <th rowspan="2">Purchase Invoice</th>
                    <th rowspan="2">Supplier Name</th>
                    <th rowspan="2">Bank Account</th>
                    <th rowspan="2">Note</th>
                    <th rowspan="2">Account No</th>
                    <th rowspan="2">Account Name</th>
                    <th rowspan="2">Description</th>
                    <th colspan="2">Orginal Currency</th>
                    <th colspan="2">Local Currency</th>
                </tr>
                <tr>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td>' . $data['purchase_invoice'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>' . $data['debit'] . '</td>
                            <td>' . $data['credit'] . '</td>
                            <td>' . $data['local_debit'] . '</td>
                            <td>' . $data['local_credit'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
