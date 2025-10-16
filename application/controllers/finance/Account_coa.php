<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Account_coa extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('account_number', 'code', 'required|min_length[1]|max_length[20]|is_unique[account_coa.account_number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/account_coa');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($account_group_detail_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM account_coa WHERE (account_name LIKE '%$post%' or account_number LIKE '%$post%') and account_group_detail_id LIKE '%$account_group_detail_id%'");
        echo json_encode($send);
    }

    public function read()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_coa', ["account_number" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        ob_start();
        header('Content-Type: application/json');

        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'), true); // Jadikan array asosiatif
            $page = (int)$this->input->post('page');
            $rows = (int)$this->input->post('rows');
            $offset = ($page > 0) ? ($page - 1) * $rows : 0;
            
            // Get data dengan GROUP BY untuk total count yang akurat
            $this->db->select('account_coa.id'); // Hanya ambil ID untuk total count
            $this->db->from('account_coa');
            $this->db->join('account_group_details', 'account_group_details.id = account_coa.account_group_detail_id', 'left');
            $this->db->join('journal_types', 'journal_types.account_number = account_coa.account_number', 'left');
            $this->db->where('account_coa.deleted', 0);
            
            // Library filter 
            if (!empty($filters)) {
                $this->applyFilters($filters);
            }

            $this->db->group_by('account_coa.id');
            $totalRowsQuery = $this->db->get();
            $totalRows = $totalRowsQuery ? $totalRowsQuery->num_rows() : 0;
            
            $this->db->select("
                account_coa.id,
                account_coa.account_number,
                account_coa.account_name,
                account_coa.original_currency,
                account_coa.original_debit,
                account_coa.original_kredit,
                account_coa.local_currency,
                account_coa.local_debit,
                account_coa.local_kredit,
                account_coa.created_by,
                account_coa.created_date,
                account_coa.updated_by,
                account_coa.updated_date,
                account_coa.status,
                account_coa.starting_date,
                account_coa.ap_ar_other,
                account_coa.report_ap,
                account_coa.report_ar,
                account_group_details.id as account_group_detail_id,
                account_group_details.name as account_group_detail_name,
                (CASE WHEN account_coa.status = 0 THEN 'OPEN' ELSE 'CLOSE' END) as closing_journal
            ");
            $this->db->from('account_coa');
            $this->db->join('account_group_details', 'account_group_details.id = account_coa.account_group_detail_id', 'left');
            $this->db->join('journal_types', 'journal_types.account_number = account_coa.account_number', 'left');
            $this->db->where('account_coa.deleted', 0);
            
            // Terapkan filter lagi ke query data
            if (!empty($filters)) {
                $this->applyFilters($filters);
            }

            $this->db->group_by('account_coa.id');
            $this->db->order_by('account_coa.account_number', 'asc');
            $this->db->limit($rows, $offset);

            $records = $this->db->get()->result_array();
            
            // Bersihkan buffer dan kirim respons JSON
            ob_clean();
            echo json_encode([
                'total' => $totalRows,
                'rows' => $records
            ]);
            ob_end_flush();
        }
    }

    private function applyFilters($filters) {
        foreach ($filters as $filter) {
            $field = strtolower($filter['field']);
            $value = $filter['value'];

            if ($field == 'account_group_detail_name') {
                $this->db->like('account_group_details.name', $value);
            } elseif ($field == 'module') {
                $this->db->like('journal_types.module', $value); 
            } elseif ($field == 'closing_journal') {
                $status = (strtoupper($value) == 'CLOSE') ? 1 : 0; // OPEN=0 & CLOSE=1
                $this->db->like('account_coa.status', $status);
            } elseif ($field == 'starting_date') {
                $this->db->like('DATE_FORMAT(account_coa.starting_date, "%Y-%m-%d")', $value);
            } else {
                $this->db->like('account_coa.' . $field, $value);
            }
        }
    }

    public function datatables_backup()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select('account_coa.*, account_group_details.name as account_group_detail_name');
            $this->db->select("GROUP_CONCAT(DISTINCT journal_types.module ORDER BY journal_types.module ASC SEPARATOR ', ') as module");
            $this->db->select("DATE_FORMAT(account_coa.created_date, '%Y-%m-%d') as starting_from, 
                (CASE WHEN account_coa.status = 0 THEN 'CLOSE'
                ELSE 'OPEN' END) as closing_journal");
            $this->db->from('account_coa');
            $this->db->join('account_group_details', 'account_group_details.id = account_coa.account_group_detail_id', 'left');
            $this->db->join('journal_types', 'journal_types.account_number = account_coa.account_number', 'left');
            $this->db->where('account_coa.deleted', 0);

            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == 'account_group_detail_name') {
                        $this->db->like('account_group_details.name', $filter->value);
                    } elseif ($filter->field == 'module') {
                        $this->db->like('journal_types.module', $filter->value); 
                    } elseif ($filter->field == 'closing_journal') {
                        $status = ($filter->value == 'CLOSE') ? 0 : 1;
                        $this->db->like('account_coa.status', $status);
                    } elseif ($filter->field == 'starting_from') {
                        $this->db->group_start(); // Group OR conditions
                        $this->db->like("DATE_FORMAT(account_coa.created_date, '%Y')", $filter->value);
                        $this->db->or_like("DATE_FORMAT(account_coa.created_date, '%m')", $filter->value);
                        $this->db->or_like("DATE_FORMAT(account_coa.created_date, '%d')", $filter->value);
                        $this->db->group_end();
                    } else {
                        $this->db->like('account_coa.'.$filter->field, $filter->value);
                    }
                }
            }
            // Group berdasarkan primary key dari tabel utama (account_coa.id) untuk menghindari duplikasi
            $this->db->group_by('account_coa.id'); 
            $this->db->order_by('account_coa.account_number', 'asc');

            //Total Data
            $temp_db = clone $this->db; // Clone the DB object to get the total rows count without limit/offset
            $totalRows = $temp_db->count_all_results('', false); // Pass false to not reset the query
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);


            // Menampilkan hasil dalam format JSON
            echo json_encode($result);
        }
    }

    public function datatablesOld()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('account_coa.*, account_group_details.name as account_group_detail_id');
            $this->db->select("journal_types.module, DATE_FORMAT(account_coa.created_date, '%Y-%m-%d') as starting_from, 
                (CASE WHEN account_coa.status = 0 THEN 'CLOSE'
                ELSE 'OPEN' END) as closing_journal");
            $this->db->from('account_coa');
            $this->db->join('account_group_details', 'account_group_details.id = account_coa.account_group_detail_id', 'left');
            $this->db->join('journal_types', 'journal_types.account_number = account_coa.account_number', 'left');
            $this->db->where('account_coa.deleted', 0);

            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == 'account_group_detail_id') {
                        $this->db->like('account_group_details.name', $filter->value);
                    } elseif ($filter->field == 'module') {
                        $this->db->like('journal_types.module', $filter->value);
                    } elseif ($filter->field == 'closing_journal') {
                        $status = ($filter->value == 'CLOSE') ? 0 : 1;
                        $this->db->like('account_coa.status', $status);
                    } elseif ($filter->field == 'starting_from') {
                        $this->db->like("DATE_FORMAT(account_coa.created_date, '%Y')", $filter->value);
                        $this->db->or_like("DATE_FORMAT(account_coa.created_date, '%m')", $filter->value);
                        $this->db->or_like("DATE_FORMAT(account_coa.created_date, '%d')", $filter->value);
                    } else {
                        $this->db->like('account_coa.'.$filter->field, $filter->value);
                    }
                }
            }

            $this->db->order_by('account_coa.account_number', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);

            // Menampilkan hasil dalam format JSON
            echo json_encode($result);
        }
    }

    //AUTO ID
    public function autoid()
    {
        $sql = $this->db->query("SELECT max(`id`) as kode FROM account_coa");
        $row = $sql->row();
        $kode = substr($row->kode, 3);
        $autoid = "AGD" . sprintf("%02s", $kode + 1);
        echo $autoid;
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            unset($post['account_group_detail_name']);

            $account_number_new = $post['account_number'];
            $account_name_new   = $post['account_name'];

            $number_exists = $this->db->get_where('account_coa', ['account_number' => $account_number_new, 'deleted' => 0])->row();
            $name_exists   = $this->db->get_where('account_coa', ['account_name' => $account_name_new, 'deleted' => 0])->row();            

            // Check for duplicates
            if ($number_exists && $name_exists) {
                // echo json_encode(["title"   => "Duplicated", "message" => "Account No '" . $account_number_new . "' and Account Name '" . $account_name_new . "' are already in use.", "theme"   => "error"]);
                echo 'Duplicated';

            } elseif ($number_exists) {
                // echo json_encode(["title"   => "Duplicated", "message" => "Account No '" . $account_number_new . "' is already in use.", "theme"   => "error"]);
                echo 'Duplicated';

            } elseif ($name_exists) {
                // echo json_encode(["title"   => "Duplicated", "message" => "Account Name '" . $account_name_new . "' is already in use.", "theme"   => "error"]);
                echo 'Duplicated';

            } else {
                // No duplications, proceed with creation
                $send = $this->crud->create('account_coa', $post);
                $create_result = json_decode($send);

                if ($create_result->theme == 'success') { 
                    // echo json_encode(["title"   => "Success", "message" => "Data created successfully.", "theme"   => "success"]);
                    echo 'Success';

                } else {
                    // Gagal create karena alasan lain (misal query error, atau data tidak valid)
                    // echo json_encode(["title"   => "Error", "message" => "Failed to create data. Please try again.", "theme"   => "error"]);
                    echo 'Error';

                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    //UPDATE DATA
    public function update()
    {
        header('Content-Type: application/json');
        if ($this->input->post()) 
        {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            unset($post['account_group_detail_name']);

            if (isset($post['id'])) {
                unset($post['id']);
            }

            // Validasi Exist
            $this->db->where('id =', $id);
            $this->db->where('deleted', 0);
            $dataExist = $this->db->get('account_coa')->row();

            if ($dataExist) {

                // validasi Account CoA jika sudah digunakan pada tabel lain jangan dihapus (Bu Nina)
                $pi_exists = $this->db->get_where('purchase_invoices', ['account_number' => $dataExist->account_number, 'deleted' => 0])->row();
                $si_exists = $this->db->get_where('sales_invoices', ['account_number' => $dataExist->account_number, 'deleted' => 0])->row();
                $ap_exists = $this->db->get_where('ap_payments', ['account_number' => $dataExist->account_number, 'deleted' => 0])->row();
                $ar_exists = $this->db->get_where('ar_receipts', ['account_number' => $dataExist->account_number, 'deleted' => 0])->row();
                $journal_exists = $this->db->get_where('journal_postings', ['account_number' => $dataExist->account_number, 'deleted' => 0])->row();

                if ( !empty($pi_exists) || !empty($si_exists) || !empty($ap_exists) || !empty($ar_exists) || !empty($journal_exists) ) {
                    // echo json_encode(["title" => "Error", "message" => "Data is already in use in another table", "theme" => "error"]); 
                    echo 'Existed';
                
                } else {
                    $update = $this->crud->update('account_coa', ["id" => $id], $post);
                    $update_result = json_decode($update);

                    if ($update_result->theme == 'success') {                    
                        // echo json_encode(["title"   => "Success", "message" => "Data created successfully.", "theme"   => "success"]); // error parse
                        echo 'Success';

                    } else {
                        $this->output->set_status_header(400); // Bad Request
                        // echo json_encode(['success' => false, 'message' => 'Failed to update data.', 'title' => 'Error', 'theme' => 'error']);
                        echo 'Error';
                    }
                }


            } else {        
                $this->output->set_status_header(400); // Bad Request
                // echo json_encode(['success' => false, 'message' => 'Data not found.', 'title' => 'Error', 'theme' => 'error']);
                echo 'Failed';
            }

        } else {
            $this->output->set_status_header(405); // Method Not Allowed
            // echo json_encode(['success' => false, 'message' => 'Method not allowed.', 'title' => 'Error', 'theme' => 'error']);
            echo 'Error';
        }
    }

    // Update status Report AP & Report AR
    public function report_update()
    {
        header('Content-Type: application/json');
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error", "message" => "Invalid request method", "theme" => "error"]);
            return;
        }

        $id     = $this->input->post('id');
        $field  = $this->input->post('field');
        $status = $this->input->post('status');

        // Buat array data yang akan di-update secara dinamis
        $dataToUpdate = [$field => $status];

        $send = $this->crud->update('account_coa', ["id" => $id], $dataToUpdate);
        if ($send) {
            echo $send;
        } else {
            echo json_encode(["title" => "Error", "message" => "Failed to update report status.", "theme" => "error"]);
        }
    }

    //DELETE DATA
    public function delete()
    {
        $post = $this->input->post();
        $id   = $post['id'];

        $account = $this->db->get_where('account_coa', ['id' => $id, 'deleted' => 0])->row();
        
        // validasi Account CoA jika sudah digunakan pada tabel lain jangan dihapus (Bu Nina)
        $pi_exists = $this->db->get_where('purchase_invoices', ['account_number' => $account->account_number, 'deleted' => 0])->row();
        $si_exists = $this->db->get_where('sales_invoices', ['account_number' => $account->account_number, 'deleted' => 0])->row();
        $ap_exists = $this->db->get_where('ap_payments', ['account_number' => $account->account_number, 'deleted' => 0])->row();
        $ar_exists = $this->db->get_where('ar_receipts', ['account_number' => $account->account_number, 'deleted' => 0])->row();
        $journal_exists = $this->db->get_where('journal_postings', ['account_number' => $account->account_number, 'deleted' => 0])->row();

        if ( !empty($pi_exists) || !empty($si_exists) || !empty($ap_exists) || !empty($ar_exists) || !empty($journal_exists) ) {
            echo json_encode(["title" => "Error", "message" => "Data is already in use in another table", "theme" => "error"]);
            
        } elseif ($account->original_debit > 0 || $account->original_kredit > 0 || $account->local_debit > 0 || $account->local_kredit > 0) {
            echo json_encode(["title" => "Error", "message" => "Credit / Debit is not empty", "theme" => "error"]);
            
        } else {
            $send = $this->crud->delete('account_coa', $post);
            echo $send;
        } 
    }

    //UPLOAD DATA
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
        $datas = array();
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'account_group_detail_id' => $data->val($i, 2),
                'account_number' => $data->val($i, 3),
                'account_name' => $data->val($i, 4),
                'original_currency' => $data->val($i, 5),
                'original_debit' => $data->val($i, 6),
                'original_kredit' => $data->val($i, 7),
                'local_currency' => $data->val($i, 8),
                'local_debit' => $data->val($i, 9),
                'local_kredit' => $data->val($i, 10),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $account_group = $this->crud->read('account_group_details',[], ["number" => $data['account_group_detail_id']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);

            if (!empty($account_coa->account_number)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Account No " . $account_coa->account_number . " Duplicate Data", "theme" => "error"));
            }else if (empty($account_group->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Account Group Details Code " . $data['account_group_detail_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "account_group_detail_id" => $account_group->id,
                    "account_number" => $data['account_number'],
                    "account_name" => $data['account_name'],
                    "original_currency" => $data['original_currency'],
                    "original_debit" => $data['original_debit'],
                    "original_kredit" => $data['original_kredit'],
                    "local_currency" => $data['local_currency'],
                    "local_debit" => $data['local_debit'],
                    "local_kredit" => $data['local_kredit'],
                );
                $send = $this->crud->create('account_coa', $dataFinal);
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/account_coa.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/account_coa.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/account_coa.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }


    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=account_coa_$format.xls");
        }

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Fetch data using JOIN for account COA
        $this->db->select('ac.*, ag.name as group_name, agd.name as category');
        $this->db->from('account_coa ac');
        $this->db->join('account_group_details agd', 'ac.account_group_detail_id = agd.id', 'left');
        $this->db->join('account_groups ag', 'agd.account_group_id = ag.id', 'left');
        $this->db->where('ac.deleted', 0);
        $this->db->order_by('ac.account_number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
    <center>
        <div style="float: left; font-size: 12px; text-align: left;">
            <table style="width: 100%;">
                <tr>
                    <td rowspan="2" width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                        <img src="' . $config->favicon . '" width="30">
                    </td>
                    <td colspan="2" style="font-size: 14px; text-align: left; margin:2px;">
                        <b>' . $config->name . '</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 12px; text-align: left; margin:2px;">
                        Address or additional information
                    </td>
                </tr>
            </table>
        </div>
        <div style="float: right; font-size: 12px; text-align: right;">
            Print Date ' . date("d M Y H:m:s") . ' <br>
            Print By ' . $this->session->username . '  
        </div>
        <br><br>
        <div style="float: center; font-size: 16px; text-align: center;">
            <h3>CHART OF ACCOUNT</h3>
        </div>
    </center>
    
    <table id="customers" border="1">
        <tr>
            <th rowspan="2" width="20">No</th>
            <th rowspan="2">Category</th>
            <th rowspan="2">Account Number</th>
            <th rowspan="2">Account Name</th>
            <th colspan="3" style="text-align: center;"> Original Currency</th>
            <th colspan="3" style="text-align: center;"> Local Currency</th>
        </tr>
        <tr>
            <th>Currency</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Currency</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>';
        
        $no = 1;
        $total_original_debit  = 0;
        $total_original_kredit = 0;
        $total_local_debit = 0;
        $total_local_kredit = 0;

        foreach ($records as $data) {
            $html .= '<tr>
                <td style="text-align:center;">' . $no . '</td>
                <td style="text-align:left;">' . $data['category'] . '</td>
                <td style="text-align:left;">' . $data['account_number'] . '</td>
                <td style="text-align:left;">' . $data['account_name'] . '</td>
                <td style="text-align:center;">' . $data['original_currency'] . '</td>
                <td style="text-align:right;">' . number_format($data['original_debit'], 2, ',', '.') . '</td>
                <td style="text-align:right;">' . number_format($data['original_kredit'], 2, ',', '.') . '</td>
                <td style="text-align:center;">' . $data['local_currency'] . '</td>
                <td style="text-align:right;">' . number_format($data['local_debit'], 2, ',', '.') . '</td>
                <td style="text-align:right;">' . number_format($data['local_kredit'], 2, ',', '.') . '</td>
            </tr>';
            
            $no++;

            $total_original_debit += $data['original_debit'];
            $total_original_kredit += $data['original_kredit'];
            $total_local_debit += $data['local_debit'];
            $total_local_kredit += $data['local_kredit'];
        }

        $html .= '<tr style="background-color:#FFFF00;">
                <td colspan="5" style="text-align:right; padding-right:30px;"><b>Grand Total</b></td>
                <td style="text-align:right">' . number_format($total_original_debit, 2, ',', '.') . '</td>
                <td style="text-align:right">' . number_format($total_original_kredit, 2, ',', '.') . '</td>
                <td style="text-align:center;">-</td>
                <td style="text-align:right">' . number_format($total_local_debit, 2, ',', '.') . '</td>
                <td style="text-align:right">' . number_format($total_local_kredit, 2, ',', '.') . '</td>
            </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function get_statuses()
    {
        // Logic to fetch statuses from the database or any other source
        $statuses = array(
            array('value' => 0, 'label' => 'Yes'),
            array('value' => 1, 'label' => 'No'),
        );

        // Output the statuses as JSON
        echo json_encode($statuses);
    }
}
