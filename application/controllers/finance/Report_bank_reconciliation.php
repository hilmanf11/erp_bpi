<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_bank_reconciliation extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_bank_reconciliation');
        } else {
            redirect('error_access');
        }
    }

    // GET PAYLOAD FOR UPLOAD DATA
    public function upload()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        require_once 'assets/vendors/excel_reader2.php';
        header('Content-Type: application/json');
        
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        
        $dataBank = [
            'bank_account' => $data->val(3, 3),
            'start_date'   => $data->val(4, 3),
            'end_date'     => $data->val(5, 3),
            'currency'     => $data->val(6, 3),
        ];

        $filter_account_number = $this->input->post("filter_account_number") ?? null;
        $filter_bank_account = $this->input->post("filter_bank_account") ?? null;
        $filter_from = $this->input->post("filter_from") ?? null;
        $filter_to = $this->input->post("filter_to") ?? null;
        $filter_to = $this->input->post("filter_to") ?? null;

        // CHECK Date Period same as excel
        if (strtotime($filter_from) !== strtotime($dataBank['start_date']) && strtotime($filter_to) !== strtotime($dataBank['end_date']) ) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Period in Excel Is Not Match with the selected Date", "theme" => "error"]);
            return;
        }

        // CHECK Bank Account Number same as excel
        $account_banks = $this->crud->read('account_banks', [], ["account_number" => $filter_account_number]);
        if (!$account_banks || $account_banks->bank_account !== $dataBank['bank_account'] || $filter_bank_account !== $dataBank['bank_account']) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Bank Account ". $dataBank['bank_account'] ." inside the file is Not Match with the selected data", "theme" => "error"]);
            return;
        }

        $datas = [];
        for ($i = 10; $i <= $total_row; $i++) {
            $datas[] = [
                'posting_date' => date("Y-m-d H:i:s", strtotime($data->val($i, 2))),
                'remark'       => htmlspecialchars($data->val($i, 3)),
                'debit'        => str_replace(',', '', $data->val($i, 4)),
                'credit'       => str_replace(',', '', $data->val($i, 5))
            ];
        }

        $payload = [
            "bank"  => $dataBank,
            "data"  => $datas,
            "total" => count($datas),
        ];
        echo json_encode($payload);
        
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/bank_reconciliation.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/bank_reconciliation.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/bank_reconciliation.txt";
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
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $bank = $this->input->post('bank');
            $data = $this->input->post('data');

            if (empty($bank['bank_account'])) {
                echo json_encode(array("title" => "Error", "message" => "Bank Account is required!", "theme" => "error"));
            } else {

                $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $bank['bank_account']]);
                $bank_reconciliation = $this->crud->read('bank_reconciliation', [], ["bank_account" => $bank['bank_account'], "start_date" => $bank['start_date'], "end_date" => $bank['end_date'], "currency" => $bank['currency'], 
                    "posting_date" => $data['posting_date'], "remark" => $data['remark']]);
    
                if (empty($account_banks->bank_account)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Bank Account Of " . $bank['bank_account'] ." Is Not Found", "theme" => "error"));
                } 
                elseif (!empty($bank_reconciliation)) {
                    echo json_encode(array("title" => "Error", "message" => "Data in this Period is already uploaded before!", "theme" => "error"));
                } 
                else {
    
                    $dataFinal = [
                        // Data Bank
                        "account_number"  => $account_banks->account_number,
                        "bank_account"    => $bank['bank_account'],
                        "start_date"      => $bank['start_date'],
                        "end_date"        => $bank['end_date'],
                        "currency"        => $bank['currency'],
                        // Data Transactions
                        "source"          => "Bank",
                        "posting_date"    => $data["posting_date"],
                        "remark"          => $data["remark"],
                        "credit"          => $data["credit"],
                        "debit"           => $data["debit"],
                    ];

                    $send = $this->crud->create('bank_reconciliation', $dataFinal);

                    // validasi posting_date mutasi berbeda dengan periode yang dipilih
                    if ( date("Y-m", strtotime($dataFinal['start_date'])) !== date("Y-m", strtotime($dataFinal['posting_date'])) ) {
                        echo json_encode(array("title" => "Caution!", "message" => "Data added, but the Posting Date doesn't match the Period Date!", "theme" => "warning"));
                    } else {
                        echo $send;
                    }
                }
            }

        }
    }

    public function getJournal($filter_from, $filter_to, $filter_account_number)
    {
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        
        $start_date = date("Y-m-d", strtotime($filter_from));
        $end_date   = date("Y-m-d", strtotime($filter_to));

        $summary = [];
        
        $this->db->select('*');
        $this->db->from('account_coa');
        $this->db->where("account_number", $filter_account_number);
        $account_coa = $this->db->get()->row();
        if ($account_coa) {
            $begin_account_no = $account_coa->account_number;
            $begin_balance_local = ($account_coa->local_debit + $account_coa->local_kredit);
            $begin_balance_ori = ($account_coa->original_debit + $account_coa->original_kredit);
        } else {
            // Tangani kasus jika akun tidak ditemukan
            $begin_account_no = null;
            $begin_balance_local = 0;
            $begin_balance_ori = 0;
        }
        
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $start_date);
        $this->db->where('trans_date <=', $end_date);
        $this->db->where("account_number", $filter_account_number);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $this->db->order_by('trans_date', 'asc');
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('id', 'asc'); // Tambahkan ini
        $journals = $this->db->get()->result_array();

        $this->db->select('account_number, account_name,
            COALESCE(SUM(original_debit)) as original_debit,
            COALESCE(SUM(original_credit)) as original_credit,
            COALESCE(SUM(local_debit)) as local_debit,
            COALESCE(SUM(local_credit)) as local_credit');
        $this->db->from('journal_postings');
        $this->db->where("trans_date between '$filter_before' and '$filter_before_to'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $this->db->order_by('trans_date', 'asc');
        $this->db->order_by('id', 'asc'); // Tambahkan ini
        $journal_bf = $this->db->get()->row();

        $journal_ori_debit = @$journal_bf->original_debit;
        $journal_ori_credit = @$journal_bf->original_credit;
        $journal_local_debit = @$journal_bf->local_debit;
        $journal_local_credit = @$journal_bf->local_credit;

        $journal_end_ori_debit = 0;
        $journal_end_ori_credit = 0;
        $journal_end_local_debit = 0;
        $journal_end_local_credit = 0;
        
        if ( (($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit) > 0 ) {
            $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
            $journal_end_ori_credit = 0;
            $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
            $journal_end_local_credit = 0;
        } else {
            $journal_end_ori_debit = 0;
            $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
            $journal_end_local_debit = 0;
            $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
        }
        
        $ori_balance = ($journal_end_ori_debit + $journal_end_ori_credit);
        $ori_debit = 0;
        $ori_credit = 0;
        $local_balance = ($journal_end_local_debit + $journal_end_local_credit);
        $local_debit = 0;
        $local_credit = 0;

        $summary['currency'] = $account_coa->original_currency;
        $summary['open_ori_balance'] = $ori_balance;
        $summary['open_local_balance'] = $local_balance;
        
        $journalTransactions = [];
        $last_transaction = null;
        $status_recheck = false;
        $no = 1;

        foreach ($journals as $journal) 
        {
            // Status Recheck transaksi identik
            if ($last_transaction && 
                $last_transaction['trans_date'] == $journal['trans_date'] &&
                $last_transaction['original_debit'] == $journal['original_debit'] &&
                $last_transaction['original_credit'] == $journal['original_credit']) 
            {
                $status_recheck = true;
            } else {
                $status_recheck = false;
            }

            $journalTransactions[] = [
                "no"                => $no,
                "id"                => $journal['id'],
                "trans_date"        => $journal['trans_date'],
                "number"            => $journal['number'],
                "account_number"    => $journal['account_number'],
                "account_name"      => $journal['account_name'],
                "description"       => $journal['description'],
                "currency"          => $journal['currency'],
                "ori_balance"       => $ori_balance,
                "original_debit"    => $journal['original_debit'],
                "original_credit"   => $journal['original_credit'],
                "local_balance"     => $local_balance,
                "local_debit"       => $journal['local_debit'],
                "local_credit"      => $journal['local_credit'],
                "status_recheck"    => $status_recheck,
            ];
            
            $ori_balance += ($journal['original_debit'] - $journal['original_credit']);
            $local_balance += ($journal['local_debit'] - $journal['local_credit']);  
            

            $ori_debit += $journal['original_debit'];
            $ori_credit += $journal['original_credit'];
            $local_debit += $journal['local_debit'];
            $local_credit += $journal['local_credit'];
            $no++;

            // Simpan transaksi saat ini untuk perbandingan selanjutnya
            $last_transaction = $journal;
        }

        $summary['ending_ori_balance'] = abs($ori_balance);
        $summary['ending_local_balance'] = abs($local_balance);
        $summary['grand_ori_debit'] = abs($ori_debit);
        $summary['grand_ori_credit'] = abs($ori_credit);
        $summary['grand_local_debit'] = abs($local_debit);
        $summary['grand_local_credit'] = abs($local_credit);

        $result = [
            'journal_summary' => $summary,
            'journal_transactions' => $journalTransactions,
        ];
        return $result;
    }

    public function getBankMutation($filter_from, $filter_to, $filter_account_number)
    {
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        
        $start_date = date("Y-m-d", strtotime($filter_from));
        $end_date   = date("Y-m-d", strtotime($filter_to));

        $summary = [];
        
        $this->db->select('coa.*, b.bank_account, b.currency');
        $this->db->from('account_coa coa');
        $this->db->join('account_banks b', 'b.account_number = coa.account_number', 'left');
        $this->db->where("coa.account_number", $filter_account_number);
        $account_coa = $this->db->get()->row();
        if ($account_coa) {
            $begin_account_no = $account_coa->account_number;
            $begin_balance_local = ($account_coa->local_debit + $account_coa->local_kredit);
            $begin_balance_ori = ($account_coa->original_debit + $account_coa->original_kredit);
        } else {
            // Tangani kasus jika akun tidak ditemukan
            $begin_account_no = null;
            $begin_balance_local = 0;
            $begin_balance_ori = 0;
        }

        $this->db->select('*');
        $this->db->from('bank_reconciliation');
        $this->db->where("posting_date between '$start_date' and '$end_date'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->order_by('posting_date', 'asc');
        $this->db->order_by('account_number', 'asc');
        $mutations = $this->db->get()->result_array();

        $this->db->select('account_number, 
            COALESCE(SUM(debit)) as debit,
            COALESCE(SUM(credit)) as credit');
        $this->db->from('bank_reconciliation');
        $this->db->where("posting_date between '$filter_before' and '$filter_before_to'");
        $this->db->where("account_number", $filter_account_number);
        $bank_sum = $this->db->get()->row();

        $bank_ori_debit = @$bank_sum->debit;
        $bank_ori_credit = @$bank_sum->credit;

        $bank_end_ori_debit = 0;
        $bank_end_ori_credit = 0;
        
        if ( (($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit) > 0 ) {
            $bank_end_ori_debit = abs(($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit);
            $bank_end_ori_credit = 0;
        } else {
            $bank_end_ori_debit = 0;
            $bank_end_ori_credit = abs(($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit);
        }
        
        $ori_balance = ($bank_end_ori_debit + $bank_end_ori_credit);
        $ori_debit = 0;
        $ori_credit = 0;

        $summary['currency'] = $account_coa->original_currency;
        $summary['open_ori_balance'] = $ori_balance;
        
        $bankMutations = [];
        $last_transaction = null;
        $status_recheck = false;
        $no = 1;
        
        foreach ($mutations as $bank) 
        {
            // Status Recheck transaksi identik
            if ($last_transaction && 
                $last_transaction['posting_date'] == $bank['posting_date'] &&
                $last_transaction['debit'] == $bank['debit'] &&
                $last_transaction['credit'] == $bank['credit']) 
            {
                $status_recheck = true;
            } else {
                $status_recheck = false;
            }

            $bankMutations[] = [
                "no"             => $no,
                "id"             => $bank['id'],
                "posting_date"   => $bank['posting_date'],
                "account_number" => $bank['account_number'],
                "remark"         => $bank['remark'],
                "currency"       => $bank['currency'],
                "balance"        => $ori_balance,
                "debit"          => $bank['debit'],
                "credit"         => $bank['credit'],
                "status_recheck" => $status_recheck,
            ];
            
            $ori_balance += ($bank['debit'] - $bank['credit']);
            
            $ori_debit += $bank['debit'];
            $ori_credit += $bank['credit'];
            $no++;

            // Simpan transaksi saat ini untuk perbandingan selanjutnya
            $last_transaction = $bank;
        }

        $summary['ending_ori_balance'] = abs($ori_balance);
        $summary['grand_ori_debit'] = abs($ori_debit);
        $summary['grand_ori_credit'] = abs($ori_credit);

        $result = [
            'bank_summary'   => $summary,
            'bank_mutations' => $bankMutations,
        ];
        return $result;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_reconciliation_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to   = base64_decode($this->input->get("filter_to"));
        $filter_account_number   = base64_decode($this->input->get("filter_account_number"));        

        if (empty($filter_from) || !strtotime($filter_from)) {
            show_error('Invalid "filter_from" date parameter.');
            return;
        }
        if (empty($filter_to) || !strtotime($filter_to)) {
            show_error('Invalid "filter_to" date parameter.');
            return;
        }
        if (empty($filter_account_number)) {
            show_error('Bank Account is required.');
            return;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Bank Account
        $this->db->select('*');
        $this->db->from('account_banks');
        $this->db->where('account_number', $filter_account_number);
        $dataBank = $this->db->get()->row();

        // GET DATA JOURNAL 
        $dataJournal = $this->getJournal($filter_from, $filter_to, $filter_account_number);
        
        // GET DATA BANK MUTATION 
        $dataMutation = $this->getBankMutation($filter_from, $filter_to, $filter_account_number);

        // --- Core Reconciliation Logic ---
        $matched_transactions = [];
        $unmatched_bank = [];
        $unmatched_journal = [];

        // Temporary arrays to track matched IDs
        $matched_journal_ids = [];
        $matched_bank_ids = [];

        // Loop through bank entries and try to match them with journal entries
        foreach ($dataMutation['bank_mutations'] as $b_key => $bank_entry) {
            $is_matched = false;
            foreach ($dataJournal['journal_transactions'] as $j_key => $journal_entry) {
                // Check if this journal entry has already been matched
                if (in_array($journal_entry['id'], $matched_journal_ids)) {
                    continue; // Skip if already matched
                }
                
                // Matching criteria
                $j_date = date("Y-m-d", strtotime($journal_entry['trans_date']));
                $b_date = date("Y-m-d", strtotime($bank_entry['posting_date']));
                
                $journal_amount_debit = (float)$journal_entry['original_debit'];
                $journal_amount_credit = (float)$journal_entry['original_credit'];
                $bank_amount_debit = (float)$bank_entry['debit'];
                $bank_amount_credit = (float)$bank_entry['credit'];

                // Check for debit match
                $debit_match = abs($journal_amount_debit - $bank_amount_debit) < 0.01 && $journal_amount_credit == 0 && $bank_amount_credit == 0;
                // Check for credit match
                $credit_match = abs($journal_amount_credit - $bank_amount_credit) < 0.01 && $journal_amount_debit == 0 && $bank_amount_debit == 0;
                
                if ($j_date == $b_date && ($debit_match || $credit_match)) {
                    $bank_entry['result'] = "Matched";
                    $journal_entry['result'] = "Matched";

                    $matched_transactions[] = [
                        'journal_data' => $journal_entry,
                        'bank_data' => $bank_entry
                    ];

                    $matched_journal_ids[] = $journal_entry['id'];
                    $matched_bank_ids[] = $bank_entry['id'];

                    $is_matched = true;
                    break;
                }
            }

            if (!$is_matched) {
                $bank_entry['result'] = "Not Matched";
                $unmatched_bank[] = $bank_entry;
            }
        }
        
        // Find all unmatched journal entries
        foreach ($dataJournal['journal_transactions'] as $journal_entry) {
            if (!in_array($journal_entry['id'], $matched_journal_ids)) {
                $journal_entry['result'] = "Not Matched";
                $unmatched_journal[] = $journal_entry;
            }
        }

        $result = [
            'success' => true,
            'message' => 'Reconciliation data fetched successfully.',
            'matched_transactions'        => $matched_transactions,
            'unmatched_bank_transactions' => $unmatched_bank,
            'unmatched_journal_postings'  => $unmatched_journal
        ];
        
        // PRINT 
        $html = "";
        $html .= '<html>
                <head>
                <title>Bank Reconciliation - <?php echo date("F Y", strtotime($filter_to)); ?></title>';
        $html .= $this->customTable();
        $html .= '</head><body>';

        $html .= '<div class="header-section clearfix">
                <div id="print-header-container">
                        <header class="header-section">
                            <table width="100%">
                                <tr>
                                    <td width="30">
                                        <img src="' . htmlspecialchars($config->favicon ?? "") . '" width="30" alt="Logo">
                                    </td>
                                    <td width="60%">
                                        <div class="logo-text">
                                            <p>
                                                <b>' . htmlspecialchars($config->name ?? 'Company Name Not Set') . '</b> <br>
                                                <small>' . htmlspecialchars($config->description ?? 'Description Not Set') . '</small>
                                            </p>
                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td width="40%" align="right">
                                        <div class="print-info">
                                            Print Date ' . date("d M Y H:i:s") . ' <br>
                                            Print By ' .  htmlspecialchars($this->session->username) . '
                                        </div>
                                    </td>
                                <tr>
                            </table>                                
                        </header>

                    </div>';

        $html .= '<div class="report-title">
                    <h3>BANK RECONCILIATION</h3>
                    </div>
                <br>

                <div class="two-panel-section">
                    <table width="100%" border="0">
                        <tr>
                            <td width="50%">
                                <table> 
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Cut off Date</td>
                                        <td><b>' . htmlspecialchars(date("d M Y", strtotime($filter_from))) . '</b> to <b> ' . htmlspecialchars(date("d M Y", strtotime($filter_to))) . ' </b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Account</td>
                                        <td><b>' . htmlspecialchars($filter_account_number) . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Name</td>
                                        <td>' . htmlspecialchars($dataBank->bank_name) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Currency</td>
                                        <td>' . htmlspecialchars($dataBank->currency) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Opening Balance</td>
                                        <td>' . $this->formatIDR($dataBank->balance) . '</td>
                                    </tr>

                                </table>

                            </td>
                            <td>&nbsp;</td>
                            <td width="50%">
                                <table class="table-custom-summary">
                                    <thead>
                                        <tr>
                                            <th>Summary</th>
                                            <th>Bank</th>
                                            <th>ERP</th>
                                            <th>DIFF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Opening Balance</td>
                                            <td>' . $this->formatIDR($dataMutation['bank_summary']['open_ori_balance']) . '</td> 
                                            <td>' . $this->formatIDR($dataJournal['journal_summary']['open_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['open_ori_balance'], $dataJournal['journal_summary']['open_ori_balance']) . ' 
                                        </tr>
                                        <tr>
                                            <td>Debit</td>
                                            <td>' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_debit']) . '</td>
                                            <td>' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_debit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_debit'], $dataJournal['journal_summary']['grand_ori_debit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Credit</td>
                                            <td>' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_credit']) . '</td> 
                                            <td>' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_credit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_credit'], $dataJournal['journal_summary']['grand_ori_credit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Ending Balance</td>
                                            <td>' . $this->formatIDR($dataMutation['bank_summary']['ending_ori_balance']) . '</td>
                                            <td>' . $this->formatIDR($dataJournal['journal_summary']['ending_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['ending_ori_balance'], $dataJournal['journal_summary']['ending_ori_balance']) . ' 
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <br>';

        $html .= '<table id="customers">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Source</th>
                        <th>Posting Date</th>
                        <th>Remark</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                        <th>Results</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

                $no = 1;

                // MATCHED BANK X JOURNAL
                if (!empty($result['matched_transactions'])) 
                {
                    foreach ($result['matched_transactions'] as $matched) {
                            
                        // balance per row = opening balance + credit - debit
                        // $balance_bank = $matched['bank_data']['balance'] + $matched['bank_data']['credit'] - $matched['bank_data']['debit'];
                        
                        $html .= '<tr>
                                <td rowspan="2">' . $no . '</td>
                                <td> Bank </td>
                                <td>' . $matched['bank_data']['posting_date'] . '</td>
                                <td>' . $matched['bank_data']['remark'] . '</td>
                                <td>' . $this->formatIDR($matched['bank_data']['debit']) . '</td>
                                <td>' . $this->formatIDR($matched['bank_data']['credit']) . '</td>
                                <td>' . $this->formatIDR($matched['bank_data']['balance']) . '</td>
                                <td rowspan="2">' . $matched['bank_data']['result'] . '</td>
                                <td rowspan="2">' . $this->statusRecheck($matched['journal_data']['status_recheck']) . '</td>
                            </tr>';                        
                        $html .= '<tr>
                                <td> ERP </td>
                                <td>' . $matched['journal_data']['trans_date'] . '</td>
                                <td>' . $matched['journal_data']['description'] . '</td>
                                <td>' . $this->formatIDR($matched['journal_data']['original_debit']) . '</td>
                                <td>' . $this->formatIDR($matched['journal_data']['original_credit']) . '</td>
                                <td>' . $this->formatIDR($matched['journal_data']['ori_balance']) . '</td>
                            </tr>';                        
                        $no++;
                    }
                    
                }
                
                // NOT MATCHED BANK 
                if (!empty($result['unmatched_bank_transactions'])) 
                {
                    foreach ($result['unmatched_bank_transactions'] as $rowBank) {
                        // Bank
                        $html .= '<tr>
                                <td rowspan="2">' . $no . '</td>
                                <td> Bank </td>
                                <td>' . $rowBank['posting_date'] . '</td>
                                <td>' . $rowBank['remark'] . '</td>
                                <td>' . $this->formatIDR($rowBank['debit']) . '</td>
                                <td>' . $this->formatIDR($rowBank['credit']) . '</td>
                                <td>' . $this->formatIDR($rowBank['balance']) . '</td>
                                <td rowspan="2" style="color:red;">' . $rowBank['result'] . '</td>
                                <td rowspan="2">' . $this->statusRecheck($rowBank['status_recheck']) . '</td>
                            </tr>';  
                        // NULL ERP
                        $html .= '<tr>
                                <td> ERP </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>';   
                        $no++;
                    }
                }

                // NOT MATCHED JOURNAL
                if (!empty($result['unmatched_journal_postings'])) 
                {
                    foreach ($result['unmatched_journal_postings'] as $rowJournal) {
                        // NULL Bank
                        $html .= '<tr>
                                <td rowspan="2">' . $no . '</td>
                                <td> Bank </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td rowspan="2" style="color:red;"> ' . $rowJournal['result'] . ' </td>
                                <td rowspan="2"> ' . $this->statusRecheck($rowJournal['status_recheck']) . ' </td>
                            </tr>'; 
                        // ERP
                        $html .='<tr>
                            <td> ERP </td>
                            <td>' . $rowJournal['trans_date'] . '</td>
                            <td>' . $rowJournal['description'] . '</td>
                            <td>' . $this->formatIDR($rowJournal['original_debit']) . '</td>
                            <td>' . $this->formatIDR($rowJournal['original_credit']) . '</td>
                            <td>' . $this->formatIDR($rowJournal['ori_balance']) . '</td>
                        </tr>';
                        $no++;
                    }
                }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        echo $html;
    }

    function formatIDR($number) {
        $formatted_number = number_format($number, 2, ',', '.');
        return $formatted_number;
    }

    function balanceDiff($bank, $erp) {
        $calc = abs($erp - $bank);
        if ($calc > 0) {
            return '<td style="color:red; font-weight:bold;"> ' . number_format($calc, 2, ",", ".") . '</td>';
        } else {
            return '<td style="color:green; font-weight:bold;"> ' . number_format($calc, 2, ",", ".") . '</td>';
        }
    }

    function statusRecheck($status) {
        $note = "";
        if ($status == true || $status == 1) {
            $note = "Recheck";
        }
        return $note;
    }

    function customTable() 
    {
        $css = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
                    }
                    .header-section {
                        overflow: hidden;
                        margin-bottom: 20px;
                    }
                    .company-info {
                        float: left;
                        width: 60%;
                        font-size: 12px;
                        text-align: left;
                    }
                    .print-info {
                        float: right;
                        width: 38%;
                        font-size: 12px;
                        text-align: right;
                    }
                    .company-logo {
                        vertical-align: top;
                        padding-right: 10px;
                    }
                    .company-details b {
                        font-size: 14px;
                    }
                    .company-details span {
                        font-size: 10px;
                    }
                    .report-title {
                        text-align: center;
                        margin-top: 20px;
                        margin-bottom: 20px;
                    }
                    .report-title h3 {
                        margin: 0;
                        font-size: 18px;
                    }
                    .report-title small {
                        font-size: 12px;
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 13px; 
                        margin-top: 15px;
                    }
                    #customers th,
                    #customers td {
                        border: 1px solid black;
                        padding: 0.6rem; 
                    }
                    #customers th {
                        background-color: #4E73BE;
                        text-align: center;
                        color: white; 
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #DEEBF7;;
                    }
                    #customers tr:hover {
                        background-color: #f1f1f1;
                    }

                    /* Aturan CSS khusus untuk print */
                    @media print {
                        body {
                            zoom: 90%;
                        }

                        /* Memaksa warna latar belakang untuk muncul saat dicetak */
                        #customers th {
                            background-color: #4E73BE !important;
                            -webkit-print-color-adjust: exact;
                        }
                        #customers tr:nth-child(even) {
                            background-color: #DEEBF7; !important;
                            -webkit-print-color-adjust: exact;
                        }
                        #customers tr:hover {
                            background-color: #f1f1f1 !important;
                            -webkit-print-color-adjust: exact;
                        }
                        
                        /* Styling untuk baris ERP */
                        .bg-erp-row { /* Menambahkan class baru untuk baris ERP */
                            background-color: #DEEBF7 !important;
                            -webkit-print-color-adjust: exact;
                        }
                    }


                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-light-green { background-color: #CAFFB3; } /* Untuk baris kelompok akun */
                    .bg-grey { background-color: #EBEBEB; } /* Untuk grand total */

                    .table-custom-summary {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11pt;
                        color: #495057;
                    }
                    
                    /* Specific styling to match the image */
                    .table-custom-summary thead {
                        border-top: 2px solid black;
                        border-bottom: 2px solid black;
                    }

                    .table-custom-summary thead th {
                        background-color: transparent; /* No background color in the header */
                        color: black;
                        text-align: left;
                        padding: 0.3rem;
                        font-weight: bold;
                    }
                    
                    .table-custom-summary tbody tr {
                        border-bottom: 1px solid #dee2e6; /* Border at the bottom of each row */
                    }
                    
                    .table-custom-summary tbody tr:last-child {
                        border-bottom: none; /* No border for the last row */
                    }
                    
                    .table-custom-summary tbody tr td {
                        padding: 0.3rem;
                        vertical-align: middle;
                        border: none; /* Remove all cell borders */
                    }

                    .table-custom-summary .text-end {
                        text-align: right;
                    }

                    .table-custom-summary .text-danger {
                        color: #dc3545;
                        font-weight: bold;
                    }
                    
                    .clearfix::after {
                        content: "";
                        clear: both;
                        display: table;
                    }
                </style>';
        return $css;
    }

}
