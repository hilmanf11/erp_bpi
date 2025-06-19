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
    // //AUTO ID
    // public function autoid()
    // {
    //     $sql = $this->db->query("SELECT max(`number`) as kode FROM account_coa");
    //     $row = $sql->row();
    //     $kode = substr($row->kode, 3);
    //     $autoid = "AGD" . sprintf("%02s", $kode + 1);
    //     echo $autoid;
    // }
    //CREATE DATA
    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             $send   = $this->crud->create('account_coa', $post);
    //             echo $send;
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function checkExisting($field, $value) 
    {
        $check = $this->crud->read('account_coa', [], [$field => $value]);
        return $check;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();

            $check_number = $this->checkExisting("account_number", $post['account_number']);
            $check_name   = $this->checkExisting("account_name", $post['account_name']);
            $number_exists = !empty($check_number->account_number);
            $name_exists   = !empty($check_name->account_name);

            if ($number_exists && $name_exists) {
                echo json_encode(array(
                    "title"   => "Duplicated",
                    "message" => "Account No " . $check_number->account_number . " and Account Name " . $check_name->account_name . " are already in use.",
                    "theme"   => "error"
                ));
            } elseif ($number_exists) {
                echo json_encode(array(
                    "title"   => "Duplicated",
                    "message" => "Account No " . $check_number->account_number . " is already in use.",
                    "theme"   => "error"
                ));
            } elseif ($name_exists) {
                echo json_encode(array(
                    "title"   => "Duplicated",
                    "message" => "Account Name " . $check_name->account_name . " is already in use.",
                    "theme"   => "error"
                ));
            } else {
                $send   = $this->crud->create('account_coa', $post);
                echo $send;
            }

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

            $check_number = $this->checkExisting("account_number", $post['account_number']);
            $check_name   = $this->checkExisting("account_name", $post['account_name']);
            $number_exists = !empty($check_number->account_number);
            $name_exists   = !empty($check_name->account_name);

            if ($number_exists && $name_exists) {
                echo json_encode(array(
                    "title"   => "Duplicated",
                    "message" => "Account No " . $check_number->account_number . " and Account Name " . $check_name->account_name . " are already in use.",
                    "theme"   => "error"
                ));
            } else {
                $send = $this->crud->update('account_coa', ["id" => $id], $post);
                echo $send;
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('account_coa', $data);
        echo $send;
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
            array('value' => 1, 'label' => 'Yes'),
            array('value' => 0, 'label' => 'No'),
        );

        // Output the statuses as JSON
        echo json_encode($statuses);
    }
}
