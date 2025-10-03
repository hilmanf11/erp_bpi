<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_trial_balances extends CI_Controller
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
            $this->load->view('finance/report_trial_balances');
        } else {
            redirect('error_access');
        }
    }

    private function split_balance($balance) 
    {
        return [
            'debit' => $balance >= 0 ? $balance : 0,
            'credit' => $balance < 0 ? abs($balance) : 0,
        ];
    }

    public function getData()
    {
        // Ambil input tanggal dari request POST
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');

        // Validasi input tanggal
        if (empty($filter_from) || empty($filter_to)) {
            echo json_encode([
                'total' => 0,
                'rows' => [],
                'message' => 'Filter tanggal tidak boleh kosong.'
            ]);
            return;
        }

        $date_from = new DateTime($filter_from);
        $period = $date_from->format("Ym");
        $is_january = ($date_from->format('m') === '01');
        $data = [];

        // --- Ambil Data Master: Grup Akun & Akun COA ---
        $this->db->select('*');
        $this->db->from('account_group_details');
        $this->db->order_by('number', 'asc');
        $account_groups = $this->db->get()->result_array();

        $this->db->select('a.*');
        $this->db->from('account_coa a');
        $this->db->order_by('a.account_number', 'asc');
        $accounts_coa = $this->db->get()->result_array();
        
        if (empty($account_groups)) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => 'Tidak ada grup akun ditemukan.']);
            return;
        }
        
        $accounts_by_group = [];
        foreach ($accounts_coa as $account) {
            $accounts_by_group[$account['account_group_detail_id']][] = $account;
        }
        $all_account_numbers = array_column($accounts_coa, 'account_number');
        
        // --- Ambil Mutasi Jurnal untuk Periode Laporan ---
        $journal_mutations_map = [];
        if (!empty($all_account_numbers)) {
            $this->db->select('account_number, SUM(local_debit) as total_debit, SUM(local_credit) as total_credit');
            $this->db->from('journal_postings');
            $this->db->where_in('account_number', $all_account_numbers);
            $this->db->where("journal_date BETWEEN '$filter_from' AND '$filter_to'");
            $this->db->group_by('account_number');
            $journal_mutations = $this->db->get()->result_array();

            foreach ($journal_mutations as $journal_row) {
                $journal_mutations_map[$journal_row['account_number']] = [
                    'local_debit' => (float)$journal_row['total_debit'],
                    'local_credit' => (float)$journal_row['total_credit']
                ];
            }
        }
        
        // --- Tentukan Saldo Awal (Begin Balance) ---
        $begin_balance_map = [];
        if ($is_january) {
            foreach ($accounts_coa as $account) {
                $begin_balance_map[$account['account_number']] = [
                    'debit' => $account['local_debit'],
                    'credit' => $account['local_kredit']
                ];
            }
        } else {
            $period_before = (clone $date_from)->modify('-1 month')->format('Ym');
            $this->db->select('account_number, ending_debit, ending_credit');
            $this->db->from('trial_balances');
            $this->db->where('period', $period_before);
            $prev_month_balances = $this->db->get()->result_array();
            
            $prev_month_balance_map = array_column($prev_month_balances, null, 'account_number');

            foreach ($accounts_coa as $account) {
                $prev_data = $prev_month_balance_map[$account['account_number']] ?? null;
                if ($prev_data) {
                    $begin_balance_map[$account['account_number']] = [
                        'debit' => $prev_data['ending_debit'],
                        'credit' => $prev_data['ending_credit']
                    ];
                } else {
                    $begin_balance_map[$account['account_number']] = [
                        'debit' => $account['local_debit'],
                        'credit' => $account['local_kredit']
                    ];
                }
            }
        }

        // --- Proses Data dan Hitung Saldo Akhir ---
        foreach ($account_groups as $account_group) {
            $accounts = $accounts_by_group[$account_group['id']] ?? [];

            $local_debit_total_group = 0;
            $local_credit_total_group = 0;
            $begin_debit_total_group = 0;
            $begin_credit_total_group = 0;

            foreach ($accounts as $account) {
                $account_number = $account['account_number'];
                $journal_data_current = $journal_mutations_map[$account_number] ?? ['local_debit' => 0, 'local_credit' => 0];
                $begin_data = $begin_balance_map[$account_number] ?? ['debit' => 0, 'credit' => 0];

                $begin_debit = (float)$begin_data['debit'];
                $begin_credit = (float)$begin_data['credit'];

                // Perhitungan saldo akhir menggunakan nilai debit dan kredit terpisah
                $ending_balance = ($begin_debit + $journal_data_current['local_debit']) - ($begin_credit + $journal_data_current['local_credit']);
                $ending_split = $this->split_balance($ending_balance);
                
                $data[] = [
                    "period"         => $period,
                    "account_number" => $account_number,
                    "account_name"   => $account['account_name'],
                    "begin_debit"    => $begin_debit,
                    "begin_credit"   => $begin_credit,
                    "local_debit"    => $journal_data_current['local_debit'],
                    "local_credit"   => $journal_data_current['local_credit'],
                    "ending_debit"   => $ending_split['debit'],
                    "ending_credit"  => $ending_split['credit'],
                    "header"         => 1,
                ];

                // Akumulasi untuk total grup
                $local_debit_total_group += $journal_data_current['local_debit'];
                $local_credit_total_group += $journal_data_current['local_credit'];
                $begin_debit_total_group += $begin_debit;
                $begin_credit_total_group += $begin_credit;
            }

            // Perhitungan saldo akhir untuk total grup
            $ending_balance_group = ($begin_debit_total_group + $local_debit_total_group) - ($begin_credit_total_group + $local_credit_total_group);
            $ending_split_group = $this->split_balance($ending_balance_group);
            
            // Tambahkan baris total grup (header 0)
            $data[] = [
                "period"         => $period,
                "account_number" => $account_group['number'],
                "account_name"   => $account_group['name'],
                "begin_debit"    => $begin_debit_total_group,
                "begin_credit"   => $begin_credit_total_group,
                "local_debit"    => $local_debit_total_group,
                "local_credit"   => $local_credit_total_group,
                "ending_debit"   => $ending_split_group['debit'],
                "ending_credit"  => $ending_split_group['credit'],
                "header"         => 0,
            ];
        }

        // Urutkan data berdasarkan account_number
        usort($data, function($a, $b) {
            return strcmp($a['account_number'], $b['account_number']);
        });

        // Kirim hasil sebagai JSON
        $result['total'] = count($data);
        $result['rows'] = $data;
        echo json_encode($result);
    }

    public function getData_backup() // Bug nominal is not valid or balance
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');

        // Validasi input tanggal
        if (empty($filter_from) || empty($filter_to)) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => 'Filter tanggal tidak boleh kosong.']);
            return;
        }

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));
        $period_before = date("Ym", strtotime("-1 month", strtotime($filter_from)));

        $data = [];

        $this->db->select('*');
        $this->db->from('account_group_details');
        $this->db->order_by('number', 'asc');
        $account_groups = $this->db->get()->result_array();

        if (empty($account_groups)) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => 'Tidak ada grup akun ditemukan.']);
            return;
        }

        foreach ($account_groups as $account_group)
        {
            // get semua akun COA yang relevan untuk grup ini
            $this->db->select('a.*');
            $this->db->from('account_coa a');
            $this->db->where('a.account_group_detail_id', $account_group['id']);
            $this->db->order_by('a.account_number', 'asc');
            $accounts = $this->db->get()->result_array();

            // Jika tidak ada akun detail untuk grup ini, tambahkan baris total grup dengan nilai 0
            if (empty($accounts)) {
                $data[] = [
                    "period"         => $period,
                    "account_number" => $account_group['number'],
                    "account_name"   => $account_group['name'],
                    "begin_debit"    => 0,
                    "begin_credit"   => 0,
                    "local_debit"    => 0,
                    "local_credit"   => 0,
                    "ending_debit"   => 0,
                    "ending_credit"  => 0,
                    "header"         => 0,
                ];
                continue; // Lanjut ke grup account berikutnya
            }

            // Ekstrak semua account_number dari $accounts untuk digunakan dalam klausa IN
            $account_numbers = array_column($accounts, 'account_number');
            
            $journal_mutations_map = [];
            if (!empty($account_numbers)) {
                $this->db->select('account_number,
                                    COALESCE(SUM(local_debit), 0) as total_local_debit,
                                    COALESCE(SUM(local_credit), 0) as total_local_credit');
                $this->db->from('journal_postings');
                $this->db->where_in('account_number', $account_numbers);
                $this->db->where("journal_date BETWEEN '$filter_from' AND '$filter_to'");
                $this->db->group_by('account_number');
                $journal_mutations = $this->db->get()->result_array();

                foreach ($journal_mutations as $journal_row) {
                    $journal_mutations_map[$journal_row['account_number']] = [
                        'local_debit' => $journal_row['total_local_debit'],
                        'local_credit' => $journal_row['total_local_credit']
                    ];
                }
            }

            // Ambil semua trial_balances untuk akun-akun ini pada period_before dalam satu query
            $trial_balances_before_map = [];
            if (!empty($account_numbers)) {
                $this->db->select('account_number, begin_debit, begin_credit, ending_debit, ending_credit, header');
                $this->db->from('trial_balances');
                $this->db->where_in('account_number', $account_numbers);
                $this->db->where('period', $period_before);
                $trial_balances_before = $this->db->get()->result_array();

                foreach ($trial_balances_before as $tb_row) {
                    $trial_balances_before_map[$tb_row['account_number']] = $tb_row;
                }
            }

            // --- Perhitungan Akumulasi untuk TOTAL GROUP (Header 0) ---
            $local_debit_total_group = 0;
            $local_credit_total_group = 0;
            $begin_debit_total_group = 0;
            $begin_credit_total_group = 0;

            foreach ($accounts as $account) {
                $journal_data = $journal_mutations_map[$account['account_number']] ?? ['local_debit' => 0, 'local_credit' => 0];
                $trial_balance_bf_data = $trial_balances_before_map[$account['account_number']] ?? null;

                $current_begin_debit = 0;
                $current_begin_credit = 0;

                if ($trial_balance_bf_data !== null) {
                    // Jika ada data trial_balance sebelumnya
                    $current_begin_debit = $trial_balance_bf_data['ending_debit'];
                    $current_begin_credit = $trial_balance_bf_data['ending_credit'];
                } else {
                    // Jika tidak ada trial_balance sebelumnya, gunakan begin_balance dari account_coa (asumsi ini kolom awal di COA)
                    // HATI-HATI: Pastikan 'local_debit' dan 'local_kredit' ada di tabel 'account_coa' jika digunakan sebagai saldo awal!
                    $current_begin_debit = $account['local_debit'] ?? 0;
                    $current_begin_credit = $account['local_kredit'] ?? 0;
                }

                $local_debit_total_group += $journal_data['local_debit'];
                $local_credit_total_group += $journal_data['local_credit'];
                $begin_debit_total_group += $current_begin_debit;
                $begin_credit_total_group += $current_begin_credit;
            }

            // --- Perhitungan Ending Balance untuk Total Group (Header 0) ---
            $final_begin_debit = 0;
            $final_begin_credit = 0;

            $net_begin_balance_group = $begin_debit_total_group - $begin_credit_total_group;
            if ($net_begin_balance_group > 0) {
                $final_begin_debit = abs($net_begin_balance_group);
                $final_begin_credit = 0;
            } else {
                $final_begin_credit = abs($net_begin_balance_group);
                $final_begin_debit = 0;
            }

            $begin_balance_group = ($final_begin_debit + $local_debit_total_group) - ($final_begin_credit + $local_credit_total_group);

            $ending_debit_total_group = 0;
            $ending_credit_total_group = 0;

            if ($begin_balance_group > 0) {
                $ending_debit_total_group = $begin_balance_group;
                $ending_credit_total_group = 0;
            } else {
                $ending_debit_total_group = 0;
                $ending_credit_total_group = abs($begin_balance_group);
            }

            // Jika header 0, ending_debit/credit-nya adalah saldo awal untuk detail
            // Jika header 1 (detail), ending_debit/credit-nya adalah saldo awal untuk detail
            $data[] = array(
                "period" => $period,
                "account_number" => $account_group['number'],
                "account_name" => $account_group['name'],
                "begin_debit" => $final_begin_debit,
                "begin_credit" => $final_begin_credit,
                "local_debit" => $local_debit_total_group,
                "local_credit" => $local_credit_total_group,
                "ending_debit" => $ending_debit_total_group,
                "ending_credit" => $ending_credit_total_group,
                "header" => 0,
            );

            // Perhitungan untuk akun detail (Header 1)
            foreach ($accounts as $account) 
            {
                $journal_data = $journal_mutations_map[$account['account_number']] ?? ['local_debit' => 0, 'local_credit' => 0];
                $trial_balance_bf_data = $trial_balances_before_map[$account['account_number']] ?? null;

                $begin_balance_debit = 0;
                $begin_balance_credit = 0;

                if ($trial_balance_bf_data !== null) {
                    $begin_balance_credit = $trial_balance_bf_data['ending_credit'];
                    $begin_balance_debit = $trial_balance_bf_data['ending_debit'];
                } else {
                    // HATI-HATI: Pastikan 'local_debit' dan 'local_kredit' ada di tabel 'account_coa' jika digunakan sebagai saldo awal!
                    $begin_balance_debit = $account['local_debit'] ?? 0;
                    $begin_balance_credit = $account['local_kredit'] ?? 0;
                }

                $journal_debit = $journal_data['local_debit'];
                $journal_credit = $journal_data['local_credit'];
                $account_no = $account['account_number'];

                $begin_balance_detail = (($begin_balance_debit + $journal_debit) - ($begin_balance_credit + $journal_credit));

                $journal_end_debit = 0;
                $journal_end_credit = 0;

                if ($begin_balance_detail > 0) {
                    $journal_end_debit = abs($begin_balance_detail);
                    $journal_end_credit = 0;
                } else {
                    $journal_end_debit = 0;
                    $journal_end_credit = abs($begin_balance_detail);
                }

                // Memastikan tidak ada duplikasi jika account_group['number'] sama dengan account['account_number']
                if($account_group['number'] != $account['account_number']){
                    $data[] = array(
                        "period" => $period,
                        "account_number" => $account['account_number'],
                        "account_name" => $account['account_name'],
                        "begin_debit" => $begin_balance_debit,
                        "begin_credit" => $begin_balance_credit,
                        "local_debit" => $journal_debit,
                        "local_credit" => $journal_credit,
                        "ending_debit" => $journal_end_debit,
                        "ending_credit" => $journal_end_credit,
                        "header" => 1,
                    );
                }
            }
        }

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $trial_balances = $this->crud->reads("trial_balances", [], [
                "period" => $post['period'], 
                "account_number" => $post['account_number']
            ]);

            if(count($trial_balances) > 0){
                $send = $this->crud->update('trial_balances', [
                    "period" => $post['period'], 
                    "account_number" => $post['account_number']
                ], $post);

                echo $send;
            }else{
                $send = $this->crud->create('trial_balances', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=trial_balances_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to   = base64_decode($this->input->get("filter_to"));

        if (empty($filter_from) || !strtotime($filter_from)) {
            show_error('Invalid "filter_from" date parameter.');
            return;
        }
        if (empty($filter_to) || !strtotime($filter_to)) {
            show_error('Invalid "filter_to" date parameter.');
            return;
        }

        $period_start = date("Ym", strtotime($filter_from));
        $period_end   = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Ambil Begin Balances
        $this->db->select('account_number, begin_debit, begin_credit');
        $this->db->from('trial_balances');
        $this->db->where('period', $period_start);
        $this->db->group_by('account_number', 'begin_debit', 'begin_credit');
        $begin_balances = $this->db->get()->result_array();

        $begin_balance_mapping = [];
        foreach ($begin_balances as $row) {
            $begin_balance_mapping[$row['account_number']] = [
                'debit' => $row['begin_debit'],
                'credit' => $row['begin_credit'],
            ];
        }

        
        // Ambil End Balance
        $this->db->select('account_number, account_name, header, 
        ending_debit, ending_credit');
        $this->db->from('trial_balances');
        $this->db->where('period', $period_end);
        $this->db->group_by('account_number, account_name, header, ending_debit, ending_credit');
        $ending_balances = $this->db->get()->result_array();
        
        $ending_balance_mapping = [];
        foreach ($ending_balances as $row) {
            $ending_balance_mapping[$row['account_number']] = [
                'name' => $row['account_name'],
                'header' => $row['header'],
                'debit' => $row['ending_debit'],
                'credit' => $row['ending_credit'],
            ];
        }

        // Ambil mutasi (Transaction)
        $this->db->select('a.account_number, a.account_name, b.account_group_detail_id, a.header,
            SUM(a.local_debit) as total_local_debit, 
            SUM(a.local_credit) as total_local_credit');
        $this->db->from('trial_balances a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.period >=', $period_start);
        $this->db->where('a.period <=', $period_end);
        $this->db->group_by('a.account_number');
        $local_mutations = $this->db->get()->result_array();

        $local_mutation_mapping = [];
        foreach ($local_mutations as $row) {
            $local_mutation_mapping[$row['account_number']] = [
                'debit' => $row['total_local_debit'],
                'credit' => $row['total_local_credit'],
            ];

            $groupId = $this->db->query("SELECT id FROM account_group_details WHERE number = '" . $row['account_number'] . "' ")->row();
            if ($groupId !== null) {
                $groupIdValue = $groupId->id;
            } else {
                // data account ini di table account_coa tidak ada, tetapi ada di journal_postings
                $groupIdValue = '99999'; // no account group
            }
            
            $account_mapping[] = [
                'group_id'       => !empty($row['account_group_detail_id']) ? $row['account_group_detail_id'] : $groupIdValue,
                'account_number' => $row['account_number'],
                'account_name'   => $row['account_name'],
                'header'         => $row['header'],
            ];
        }
        
        if (empty($account_mapping)) {
            echo ('<h3> Belum ada laporan pada periode ini. Silakan Generate. </h3>');
            return;
        }

        usort($account_mapping, function($a, $b) {
            // urutkan group_id null (99999) menjadi di akhir
            $special_group_id = '99999';

            $is_a_special = ($a['group_id'] == $special_group_id);
            $is_b_special = ($b['group_id'] == $special_group_id);

            // Kasus 1: a adalah 99999, b bukan. a harus di akhir.
            if ($is_a_special && !$is_b_special) {
                return 1; // a datang setelah b
            }
            // Kasus 2: b adalah 99999, a bukan. b harus di akhir.
            if (!$is_a_special && $is_b_special) {
                return -1; // a datang sebelum b
            }

            // Kasus 3: Keduanya 99999, atau keduanya bukan 99999.
            $groupComparison = $a['group_id'] <=> $b['group_id']; // Sort Prioritas 1: group_id (ascending)
            if ($groupComparison !== 0) {
                return $groupComparison;
            }

            $headerComparison = $a['header'] <=> $b['header'];     // Sort Prioritas 2: header (0 first, then 1 - ascending numerical)
            if ($headerComparison !== 0) {
                return $headerComparison;
            }

            return $a['account_number'] <=> $b['account_number']; // Sort Prioritas 3: account_number (ascending numerical/alphabetical)
        });

        $trial_balances = [];
        foreach ($account_mapping as $account) 
        {
            $name   = $ending_balance_mapping[$account['account_number']]['name'] ?? 'N/A';
            $header = $ending_balance_mapping[$account['account_number']]['header'] ?? 1;
            
            $begin_debit  = $begin_balance_mapping[$account['account_number']]['debit'] ?? 0;
            $begin_credit = $begin_balance_mapping[$account['account_number']]['credit'] ?? 0;
            
            $local_debit  = $local_mutation_mapping[$account['account_number']]['debit'] ?? 0;
            $local_credit = $local_mutation_mapping[$account['account_number']]['credit'] ?? 0;
            
            $ending_debit  = $ending_balance_mapping[$account['account_number']]['debit'] ?? 0;
            $ending_credit = $ending_balance_mapping[$account['account_number']]['credit'] ?? 0;
            
            $trial_balances[] = [
                'account_number' => $account['account_number'],
                'account_name'   => $name,
                'header'         => $header,
                'begin_debit'    => $begin_debit,
                'begin_credit'   => $begin_credit,
                'local_debit'    => $local_debit,
                'local_credit'   => $local_credit,
                'ending_debit'   => $ending_debit,
                'ending_credit'  => $ending_credit,
            ];
        }
        
        $html = '<html>
            <head>
                <title>Trial Balance - <?php echo date("F Y", strtotime($filter_to)); ?></title>
                <style>
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
                        border: 1px solid #ddd;
                        padding: 4px 8px; 
                    }
                    #customers th {
                        background-color: #f0f0f0;
                        text-align: center;
                        color: black;
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    #customers tr:hover {
                        background-color: #f1f1f1;
                    }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-light-green { background-color: #CAFFB3; } /* Untuk baris kelompok akun */
                    .bg-grey { background-color: #EBEBEB; } /* Untuk grand total */

                    .link-transaction {
                        color: inherit;
                        text-decoration: none;
                    }
                    .link-transaction:hover {
                        color: inherit;
                        font-weight: bolder;
                        text-decoration: underline;
                    }

                    .clearfix::after {
                        content: "";
                        clear: both;
                        display: table;
                    }
                </style>
            </head>
            <body>';

            $html .= '<div class="header-section clearfix">
                    <div class="company-info">
                        <table>
                            <tr>
                                <td class="company-logo">
                                    <img src="' . htmlspecialchars($config->favicon ?? "") . '" width="30" alt="Logo">
                                </td>
                                <td class="company-details">
                                    <b>' . htmlspecialchars($config->name ?? 'Company Name Not Set') . '</b><br>
                                    <span>' . htmlspecialchars($config->description ?? 'Description Not Set') . '</span><br>
                                </td>
                            </tr>
                        </table>
                    </div>';

            $html .= '<div class="print-info">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' .  htmlspecialchars($this->session->username) . '
                    </div>
                </div> <br>';

            $html .= '<div class="report-title">
                    <h3>TRIAL BALANCE</h3>
                    <small>PERIOD : <b>' . htmlspecialchars(date("d M Y", strtotime($filter_from))) . '</b> To <b> ' . htmlspecialchars(date("d M Y", strtotime($filter_to))) . ' </b></small>
                </div>
                <br><br>                
                <table id="customers">
                    <thead>
                        <tr>
                            <th rowspan="3" width="20">No</th>
                            <th rowspan="3">Account No</th>
                            <th rowspan="3">Account Name</th>
                            <th colspan="6">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th colspan="2">Begin Balance</th>
                            <th colspan="2">Transaction</th>
                            <th colspan="2">End Balance</th>
                        </tr>
                        <tr>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>';
                        
                            $no = 1;
                            $grand_total_begin_debit = 0;
                            $grand_total_begin_credit = 0;
                            $grand_total_local_debit = 0;
                            $grand_total_local_credit = 0;
                            $grand_total_ending_debit = 0;
                            $grand_total_ending_credit = 0;

                            foreach ($trial_balances as $trial_balance) 
                            {
                                $row_class = '';
                                $font_style = '';

                                if ($trial_balance['header'] == 0) { 
                                    $row_class = 'background:#CAFFB3; font-weight:bold;';
                                } else { 
                                    // Akumulasi grand total hanya untuk baris detail (header=1)
                                    $grand_total_begin_debit += $trial_balance['begin_debit'];
                                    $grand_total_begin_credit += $trial_balance['begin_credit'];
                                    $grand_total_local_debit += $trial_balance['local_debit'];
                                    $grand_total_local_credit += $trial_balance['local_credit'];
                                    $grand_total_ending_debit += $trial_balance['ending_debit'];
                                    $grand_total_ending_credit += $trial_balance['ending_credit'];
                                }

                                // --- Link transaksi GL Posting Journal
                                $linked_begin_debit   = $this->createLink($trial_balance['begin_debit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                $linked_begin_credit  = $this->createLink($trial_balance['begin_credit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                $linked_debit         = $this->createLink($trial_balance['local_debit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                $linked_credit        = $this->createLink($trial_balance['local_credit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                $linked_ending_debit  = $this->createLink($trial_balance['ending_debit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                $linked_ending_credit = $this->createLink($trial_balance['ending_credit'], $filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['header']);
                                
                                if ($option == "excel") {
                                    $linked_begin_debit   = number_format($trial_balance['begin_debit'], 2, ",", ".");
                                    $linked_begin_credit  = number_format($trial_balance['begin_credit'], 2, ",", ".");
                                    $linked_debit         = number_format($trial_balance['local_debit'], 2, ",", ".");
                                    $linked_credit        = number_format($trial_balance['local_credit'], 2, ",", ".");
                                    $linked_ending_debit  = number_format($trial_balance['ending_debit'], 2, ",", ".");
                                    $linked_ending_credit = number_format($trial_balance['ending_credit'], 2, ",", ".");
                                }

                                $html .= '<tr style="' . $row_class . '"> 
                                    <td class="text-center">' . $no . '</td>
                                    <td>' . htmlspecialchars($trial_balance['account_number']) . '</td>
                                    <td>' . $trial_balance['account_name'] . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_begin_debit . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_begin_credit . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_debit . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_credit . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_ending_debit . '</td>
                                    <td style="text-decoration:none;" class="text-right">' . $linked_ending_credit . '</td>
                                </tr>';
                            
                                $no++;
                            }

        $html .= '</tbody>
                    <tfoot>
                        <tr style="background-color: #EBEBEB; font-weight: bold;">
                            <td colspan="3" class="text-center">GRAND TOTAL</td>
                            <td class="text-right">' . number_format($grand_total_begin_debit, 2, ',', '.') . '</td>
                            <td class="text-right">' . number_format($grand_total_begin_credit, 2, ',', '.') . '</td>
                            <td class="text-right">' . number_format($grand_total_local_debit, 2, ',', '.') . '</td>
                            <td class="text-right">' . number_format($grand_total_local_credit, 2, ',', '.') . '</td>
                            <td class="text-right">' . number_format($grand_total_ending_debit, 2, ',', '.') . '</td>
                            <td class="text-right">' . number_format($grand_total_ending_credit, 2, ',', '.') . '</td>
                        </tr>
                    </tfoot>';

        $html .= '</table></body></html>';
        echo $html;
    }

    // get link detail transaksi GL
    function createLink($value, $filter_from, $filter_to, $filter_account, $is_header) 
    {
        if ($is_header !== "0") {
            $from_encoded    = base64_encode($filter_from);
            $to_encoded      = base64_encode($filter_to);
            $account_encoded = base64_encode($filter_account);
            $base_url        = base_url('finance/report_general_ledgers/print');
            $url             = $base_url . '?filter_from=' . $from_encoded . '&filter_to=' . $to_encoded . '&filter_account=' . $account_encoded;
            
            // if ($value > 0) { // walau 0 tetap bisa lihat GL
                return '<a href="javascript:void(0)" onclick="window.open(\'' . $url . '\', \'_blank\', \'location=yes,height=650,width=1500,scrollbars=yes,status=yes\');" class="link-transaction">' . $this->formatIDR($value, 2) . '</a>';
            // }
        }
        return $this->formatIDR($value, 2);
    }

    function formatIDR($number, $decimal_places = 2) {
        $formatted_number = number_format($number, $decimal_places, ',', '.');
        return $formatted_number;
    }

    public function printOld($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=trial_balances_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));
        $period_to = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('account_number, account_name, header,
            SUM(begin_debit) as begin_debit, 
            SUM(begin_credit) as begin_credit, 
            SUM(local_debit) as local_debit, 
            SUM(local_credit) as local_credit,
            SUM(ending_debit) as ending_debit,
            SUM(ending_credit) as ending_credit');
        $this->db->from('trial_balances');
        $this->db->where('period >=', $period);
        $this->db->where('period <=', $period_to);
        $this->db->order_by('id', 'asc');
        $this->db->group_by('account_number', 'asc');
        $trial_balances = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->description . '</span><br>
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
            <center>
                <h3 style="margin:0;">TRIAL BALANCE</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="3" width="20">No</th>
                <th rowspan="3">Account No</th>
                <th rowspan="3">Account Name</th>
                <th colspan="6">LOCAL CURRENCY</th>
            </tr>
            <tr>
                <th colspan="2">Begin Balance</th>
                <th colspan="2">Transaction</th>
                <th colspan="2">End Balance</th>
            </tr>
            <tr>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';

        $no = 1;
        $grand_total_begin_debit = 0;
        $grand_total_begin_credit = 0;
        $grand_total_local_debit = 0;
        $grand_total_local_credit = 0;
        $grand_total_ending_debit = 0;
        $grand_total_ending_credit = 0;
        foreach ($trial_balances as $trial_balance) {
            if($trial_balance['header'] == 0){
                $style = 'style="background:#CAFFB3;"';
                $font = 'font-weight:bold;';
            }else{
                $style = '';
                $font = '';
            }

            $html .= '  <tr '.$style.'>
                            <td style="'.$font.'">' . $no . '</td>
                            <td style="'.$font.'">' . $trial_balance['account_number'] . '</td>
                            <td style="'.$font.'">' . $trial_balance['account_name'] . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['begin_debit'], 2, ',', '.') . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['begin_credit'], 2, ',', '.') . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['local_debit'], 2, ',', '.') . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['local_credit'], 2, ',', '.') . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['ending_debit'], 2, ',', '.') . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['ending_credit'], 2, ',', '.') . '</td>
                        </tr>';

            if($trial_balance['header'] == 0){
                $grand_total_begin_debit += $trial_balance['begin_debit'];
                $grand_total_begin_credit += $trial_balance['begin_credit'];
                $grand_total_local_debit += $trial_balance['local_debit'];
                $grand_total_local_credit += $trial_balance['local_credit'];
                $grand_total_ending_debit += $trial_balance['ending_debit'];
                $grand_total_ending_credit += $trial_balance['ending_credit'];
            }
            $no++;
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td colspan="3"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_debit, 2, ',', '.') . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_credit, 2, ',', '.') . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_debit, 2, ',', '.') . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_credit, 2, ',', '.') . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_debit, 2, ',', '.') . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_credit, 2, ',', '.') . '</b></td>
                    </tr>';


        
        $html .= '</table></body></html>';
        echo $html;
    }
}
