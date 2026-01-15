<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Customers extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Customer Code', 'required|min_length[1]|max_length[20]|is_unique[customers.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/customers');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT * FROM customers WHERE id like '%$post%' or `number` like '%$post%' or `name` like '%$post%'");
        echo json_encode($send);
    }

    public function readAddress($customer_id)
    {
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id' AND division_id = $division_id");
        echo json_encode($send);
    }

    public function readCoa()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_coa', ["account_name" => $post],["deleted" => 0], "", "account_number", "ASC");
        echo json_encode($send);
    }

    public function readDivision()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('divisions', ["name" => $post], ["deleted" => 0], "", "id", "ASC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
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
            $this->db->select('*');
            $this->db->from('customers a');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('id', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatables2($customer_id)
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
            $this->db->select('*');
            $this->db->from('customer_address');
            $this->db->where('customer_id', $customer_id);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('plant', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatables3($customer_address_id)//berubah
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
            $this->db->select('*');
            $this->db->from('customer_address_histories');
            $this->db->where('customer_address_id', $customer_address_id);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('created_date', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //AUTO ID
    public function autoid()
    {
        $sql = $this->db->query("SELECT max(id) as kode FROM customers");
        $row = $sql->row();
        $kode = substr($row->kode, 2);
        $autoid = "C" . sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('customers', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create2()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('customer_address', $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('customers', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update2()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('customer_address', ["id" => $id], $post);

            $history = [
                'customer_address_id' => $id,
                'plant'               => $post['plant'],
                'department'          => $post['department'],
                'address'             => $post['address'],
                'address_billing'     => $post['address_billing'],
                'contact_person'      => $post['contact_person'],
                'telp'                => $post['telp'],
                'telp_billing'        => $post['telp_billing'],
                'email'               => $post['email'],
                'website'             => $post['website'],
                'taxes_plant'         => $post['taxes_plant']
            ];

            $send2 = $this->crud->create('customer_address_histories', $history);

            if (!$send2) {
                echo '<pre>';
                print_r($this->db->error());
                exit;
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete() 
    {
        if (!$this->input->post()) {
            $this->output->set_status_header(400); // Bad Request
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Invalid request method.']);
            return;
        }

        // Ambil hanya Primary Key (ID)
        $id = $this->input->post('id'); 
        if (empty($id)) {
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Customer ID is required for deletion.']);
            return;
        }

        // Cek apakah data customer ada
        $dataBefore = $this->crud->read('customers', [], ['id' => $id]); 
        if (!$dataBefore) {
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Customer not found.']);
            return;
        }

        $dataBeforeAccount = $this->db->get_where('customer_account_numbers', ['customer_id' => $id])->result_array();

        // DB::transaction() penting karena ada dua operasi: DELETE dan LOG
        $this->db->trans_start();

        // HAPUS DATA CHILD (customer_account_numbers)
        // Ini harus dilakukan pertama untuk menghindari error Foreign Key
        $this->db->where('customer_id', $id)->delete('customer_account_numbers');

        // HAPUS DATA PARENT (customers)
        $this->db->where('id', $id)->delete('customers');

        // Logging
        $this->crud->logs("Delete", json_encode($dataBefore), 'customers');

        if (!empty($dataBeforeAccount)) {
            $this->crud->logs("Delete", json_encode($dataBeforeAccount), 'customer_account_numbers');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Failed to delete Customer']);
        } else {
           echo json_encode(['title' => 'Success', 'theme' => 'success', 'message' => 'Customer deleted successfully.']);
        }
    }

    public function delete_existing()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customers', $data);
        echo $send;
    }

    public function delete2()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customer_address', $data);
        echo $send;
    }


    // --- FUNCTIONS OF MULTIPLE ACCOUNT NUMBERS --- 
    // DATATABLE 
    public function datatableMultiAccounts() 
    {
        $customer_id = base64_decode($this->input->get('id'));
        
        $customer = $this->db->select('a.id, a.account_number, b.account_name')
            ->from('customers a')
            ->join('account_coa b', 'a.account_number = b.account_number', 'left')
            ->where('a.id', $customer_id)
            ->where('a.deleted', 0)
            ->get()->row();

        if (empty($customer)) {
            $result = [];
            echo json_encode($result);
            return;
        }

        // Logic CREATE baris pertama jika belum ada
        $check_existing = $this->db->get_where('customer_account_numbers', ['customer_id' => $customer_id, 'account_number' => $customer->account_number])->row();
        if (empty($check_existing) && !empty($customer->account_number)) { 
            $data_to_save = [
                'customer_id'    => $customer_id,
                // 'division'       => $this->check_division($customer->account_name),
                'division'       => 'INJ',
                'account_number' => $customer->account_number,
                'account_name'   => $customer->account_name ?? null,
                'account_type'   => null,
                'flag'           => 1,
            ];
            
            $this->crud->create('customer_account_numbers', $data_to_save);
        } 

        // SHOW DATA
        $records = $this->db->get_where('customer_account_numbers', ['customer_id' => $customer_id])->result_array();
        
        $result = [];
        foreach ($records as $row) {            
            $division = $row['division'] ?? 'INJ';
            // if (empty($division)) { 
            //     $division = $this->check_division($row['account_name']);
            // }
            
            $result[] = [
                'migration_id'   => $row['migration_id'],
                'customer_id'    => $row['customer_id'],
                'division'       => $division,
                'account_number' => $row['account_number'],
                'account_name'   => $row['account_name'],
                'account_type'   => $row['account_type'],
                'flag'           => $row['flag'],
            ];
        }
        
        echo json_encode($result); 
    }

    protected function check_division($account_name) {
        if (stripos($account_name, 'mds') === 0) { 
            return 'MTS';
        } elseif (stripos($account_name, 'inj') === 0) {
            return 'INJ';
        } else {
            return '';
        }
    }
    
    // CREATE OR UPDATE
    public function createMultiAccounts() 
    {
        if ($this->input->post()) {
            
            $customer_id   = $this->input->post('customer_id');
            $accounts_data = $this->input->post('accounts');
            
            if (empty($customer_id) || empty($accounts_data) || !is_array($accounts_data)) {
                $response = [
                    'theme' => 'error', 
                    'title' => 'Error', 
                    'message' => 'Data Customer ID is not complete.'
                ];
                echo json_encode($response);
                return;
            }

            $all_success = true;
            $this->db->trans_start();

            foreach ($accounts_data as $account) {
                $data_to_save = [
                    'customer_id'    => $customer_id,
                    'division'       => $account['division'],
                    'account_number' => $account['account_number'],
                    'account_name'   => $account['account_name'],
                    'account_type'   => $account['account_type'] ?? null,
                    'flag'           => $account['flag'] ?? 1,
                ];

                $account_number = $data_to_save['account_number'];
                $account_name   = $data_to_save['account_name'];

                $existing = $this->db->select('*')->from('customer_account_numbers')
                            ->where("customer_id", $customer_id)
                            ->where("account_number", $data_to_save['account_number'])
                            ->get()->row();

                $customer_coa_existing = $this->db->select('*')->from('customers')
                    ->where('id', $customer_id)
                    ->where('account_number', $account_number)
                    ->get()->row();

                // Update data COA di Customers
                if (empty($customer_coa_existing)) {
                    if ($account['flag'] == '1' || $data_to_save['flag'] == '1') {
                        $this->crud->update('customers', 
                            ["id" => $customer_id], 
                            [
                                "account_number" => $account_number, 
                                "account_name" => $account_name, // di table customers dengan nama
                            ]
                        );
                    }
                }
                
                if (!empty($customer_coa_existing) && !empty($existing)) {
                    // ALSO UPDATE COA IN TABLE CUSTOMERS
                    $this->crud->update('customers', 
                        ["id" => $customer_coa_existing->id], 
                        ["account_number" => $account_number, "account_name" => $account_name]
                    );

                    $result = $this->crud->update('customer_account_numbers', 
                        ["migration_id" => $existing->migration_id], 
                        $data_to_save
                    );
                    
                } elseif (!empty($existing) && empty($customer_coa_existing)) {
                    // UPDATE
                    $result = $this->crud->update('customer_account_numbers', 
                        ["migration_id" => $existing->migration_id], 
                        $data_to_save
                    );
                
                } else {
                    // CREATE
                    $result = $this->crud->create('customer_account_numbers', $data_to_save);
                }

                if (!$result) {
                    $all_success = false;
                    break; 
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE || $all_success === false) {
                $response = [
                    'theme' => 'error', 
                    'title' => 'Error', 
                    'message' => 'Failed to save Multiple Account..'
                ];
                echo json_encode($response);
                
            } else {
                echo $result;
            }

        } else {
            $response = [
                'theme' => 'error', 
                'title' => 'Error', 
                'message' => 'Request method is not valid!'
            ];
            echo json_encode($response);
        }
    }

    // DELETE 
    public function deleteSingleAccount() 
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $customer_id    = $this->input->post('customer_id');
            $account_number = $this->input->post('account_number');

            $check = $this->db->select('*')->from('customer_account_numbers')
                    ->where('customer_id', $customer_id)
                    ->where('account_number', $account_number)
                    ->get()->row();
            $customer_coa_existing = $this->db->select('*')->from('customers')
                ->where('id', $customer_id)
                ->where('account_number', $account_number)
                ->get()->row();

            if (!empty($customer_coa_existing) && !empty($check)) {
                $send = json_encode(["title" => "Failed", "message" => "Cannot delete existing Account COA!", "theme" => "error"]);
                
            } elseif (!empty($check) && empty($customer_coa_existing)) {
                $send = $this->crud->delete('customer_account_numbers', $data);
            
            } else {
                $send = json_encode(["title" => "Removed", "message" => "Data not in database but removed from table", "theme" => "success"]);
            }
            echo $send;
        }
    }


    // --- UPLOAD FUNCTIONS ---
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'name' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'type' => $data->val($i, 4),
                'currency' => $data->val($i, 5),
                'taxes' => $data->val($i, 6),
                'payment_term' => $data->val($i, 7),
                'bank_account' => $data->val($i, 8),
                'bank_name' => $data->val($i, 9),
                'faktur_code' => $data->val($i, 10),
                'npwp' => $data->val($i, 11),
                'account_number' => $data->val($i, 12),
                'coa_name' => $data->val($i, 13),
                'status' => $data->val($i, 14)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/customers.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/customers.txt";
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

            //Cek Process Number          //table       //field        //field excel
            $customers = $this->crud->read('customers', [], ["number" => $data['number']]);

            //AUTOID
            $sql = $this->db->query("SELECT max(id) as kode FROM customers");
            $row = $sql->row();
            $kode = substr($row->kode, 2);
            $autoid = "C" . sprintf("%03s", $kode + 1);

            if (!empty($customers->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Customer Code " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "name" => $data['name'],
                    "number" => $data['number'],
                    "type" => $data['type'],
                    "taxes" => $data['taxes'],
                    "currency" => $data['currency'],
                    "payment_term" => $data['payment_term'],
                    "bank_account" => $data['bank_account'],
                    "bank_name" => $data['bank_name'],
                    "faktur_code" => $data['faktur_code'],
                    "npwp" => $data['npwp'],
                    "account_number" => $data['account_number'],
                    "coa_name" => $data['coa_name'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('customers', $dataFinal);
                echo $send;
            }
        }
    }

    //UPLOAD DATA
    public function upload2()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload2']['name']);
        move_uploaded_file($_FILES['file_upload2']['tmp_name'], $target);
        chmod($_FILES['file_upload2']['name'], 0777);
        $file = $_FILES['file_upload2']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'customer_id' => $data->val($i, 2),
                'plant' => $data->val($i, 3),
                'department' => $data->val($i, 4),
                'address' => $data->val($i, 5),
                'address_billing' => $data->val($i, 6),
                'contact_person' => $data->val($i, 7),
                'telp' => $data->val($i, 8),
                'telp_billing' => $data->val($i, 9),
                'email' => $data->val($i, 10),
                'website' => $data->val($i, 11),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload2']['name']);
    }
    public function uploadclearFailed2()
    {
        @unlink('failed/customer_address.txt');
    }
    public function uploadcreateFailed2()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customer_address.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed2()
    {
        $file = "failed/customer_address.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate2()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);

            if (empty($customers->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Customer Id " . $data['customer_id'] . " is Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "customer_id" => $data['customer_id'],
                    "plant" => $data['plant'],
                    "department" => $data['department'],
                    "address" => $data['address'],
                    "address_billing" => $data['address_billing'],
                    "contact_person" => $data['contact_person'],
                    "telp" => $data['telp'],
                    "telp_billing" => $data['telp_billing'],
                    "email" => $data['email'],
                    "website" => $data['website'],
                );

                $send   = $this->crud->create('customer_address', $dataFinal);
                echo $send;
            }
        }
    }

    
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=customers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, a.id as customer_id, b.*');
        $this->db->from('customers a');
        $this->db->join('customer_address b', 'a.id = b.customer_id', 'left');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER CUSTOMER</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Customer Code</th>
                <th>Type</th>
                <th>Plant</th>
                <th>Department</th>
                <th>Address</th>
                <th>Billing Address</th>
                <th>Contact Person</th>
                <th>Telepon</th>
                <th>Billing Contact</th>
                <th>Email</th>
                <th>Website</th>
                <th>Currency</th>
                <th>Taxes</th>
                <th>Payment Term (Day)</th>
                <th>Bank Account</th>
                <th>Bank Name</th>
                <th>Faktur Code</th>
                <th>NPWP</th>
                <th>COA No</th>
                <th>COA Name</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            
            if ($data['status'] == 0) {
                $status = 'Active';
            } else {
                $status = 'Not Active';
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['customer_id'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td>' . $data['department'] . '</td>
                    <td>' . $data['address'] . '</td>
                    <td>' . $data['address_billing'] . '</td>
                    <td>' . $data['contact_person'] . '</td>
                    <td>' . $data['telp'] . '</td>
                    <td>' . $data['telp_billing'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['website'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['taxes'] . '</td>
                    <td>' . $data['payment_term'] . '</td>
                    <td>' . $data['bank_account'] . '</td>
                    <td>' . $data['bank_name'] . '</td>
                    <td>' . $data['faktur_code'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['npwp'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['account_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['account_name'] . '</td>
                    <td>' . $status . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
