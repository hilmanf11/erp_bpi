<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Journal_setups extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/journal_setups');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($journal_type_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $decoded = base64_decode(str_replace(' ', '+', $journal_type_id));
        if (!empty($decoded)) {
            $journal_type_id = $decoded;
        }
        $journal_type_id = str_replace('header_', '', $journal_type_id);
        $send = $this->crud->query("SELECT a.*, b.account_name FROM journal_setups a 
            LEFT JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.account_number LIKE '%$post%' and a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filters = json_decode($this->input->post('filterRules') ?? '[]');
        $id = $this->input->post('id') ?? "0";
        if ($id === "0" || empty($id)) {
            $this->db->select('a.journal_type_id, b.name as journal_name');
            $this->db->from('journal_setups a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if ($filter->field == "journal_name") {
                        $this->db->like("b.name", $filter->value);
                    } else {
                        $this->db->like($filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.journal_type_id');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => "header_" . $record['journal_type_id'],
                    "journal_type_id" => $record['journal_type_id'],
                    "journal_name" => $record['journal_name'],
                    "state" => "closed",
                    "datatable" => 1
                );
            }
        } else {
            $clean_id = str_replace('header_', '', $id);
            $this->db->select('a.*, b.name as journal_name, c.account_name');
            $this->db->from('journal_setups a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
            $this->db->join('account_coa c', 'a.account_number = c.account_number', 'left');
            $this->db->where('a.journal_type_id', $clean_id);
            $this->db->group_by('a.account_number');
            $this->db->order_by('a.flag', 'asc');
            $arr = $this->db->get()->result_array();
        }

        //Mapping Data
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }

    public function datatable_updates()
    {
        $raw = $this->input->get('journal_type_id');
        $journal_type_id = base64_decode(str_replace(' ', '+', $raw));
        if (empty($journal_type_id)) {
            $journal_type_id = $raw;
        }
        $journal_type_id = str_replace('header_', '', $journal_type_id);
        $records = $this->crud->query("SELECT a.*, b.account_name FROM journal_setups a LEFT JOIN account_coa b ON a.account_number = b.account_number WHERE a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");
        echo json_encode($records);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $journal_setups = $this->crud->read('journal_setups', [], ["journal_type_id" => $post['journal_type_id'], "account_number" => $post['account_number'], "status" => $post['status']]);
            if (@$journal_setups->id != "") {
                $send = $this->crud->update('journal_setups', ["journal_type_id" => $post['journal_type_id'], "account_number" => $post['account_number'], "status" => $post['status']], $post);
            } else {
                $send = $this->crud->create('journal_setups', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('journal_setups', $data);
        echo $send;
    }

    //UPLOAD DATA (template/tmp_journal_setups.xls: A=Journal Code, B=Account No, C=Debit/Credit, data mulai baris 4)
    public function upload()
    {
        // Notice/deprecated PHP 8 dari PhpSpreadsheet tidak boleh tercetak ke response JSON
        ini_set('display_errors', 0);
        require_once APPPATH . 'third_party/PhpSpreadsheet/autoload.php';

        $file = secure_excel_upload('file_upload', ['xls', 'xlsx']);
        if (!$file['status']) {
            echo json_encode(["title" => "Error", "message" => $file['message'], "theme" => "error"]);
            return;
        }

        try {
            $sheet = IOFactory::load($file['path'])->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $datas = [];
            for ($i = 4; $i <= $highestRow; $i++) {
                $journal_type   = trim((string) $sheet->getCell("A$i")->getValue());
                $account_number = trim((string) $sheet->getCell("B$i")->getValue());
                if ($journal_type === "" && $account_number === "") continue;

                $datas[] = [
                    'row'            => $i,
                    'journal_type'   => $journal_type,
                    'account_number' => $account_number,
                    'status'         => strtoupper(trim((string) $sheet->getCell("C$i")->getValue())),
                ];
            }
            echo json_encode(["total" => count($datas), "data" => $datas]);
        } catch (Throwable $e) {
            echo json_encode(["title" => "Error", "message" => "Failed to read file: " . $e->getMessage(), "theme" => "error"]);
        } finally {
            @unlink($file['path']);
        }
    }

    //UPLOAD CREATE DATA (key: journal code + account + debit/credit; yang sudah ada di-skip, AP/AR/Index tidak ditimpa)
    public function uploadcreate()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }
        $data = $this->input->post('data');
        $row  = "Row " . (int) $data['row'] . " : ";

        // Kolom kosong tidak boleh ikut lookup (bisa match master dengan number/akun kosong)
        $journal_type = ($data['journal_type'] ?? "") === "" ? null
            : $this->crud->read('journal_types', [], ["number" => $data['journal_type']]);
        $account   = ($data['account_number'] ?? "") === "" ? null
            : $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);

        if (empty($journal_type->id)) {
            $error = ["Not Found", "Journal Code " . $data['journal_type'] . " Not Found"];
        } elseif (empty($account->account_number)) {
            $error = ["Not Found", "Account No " . $data['account_number'] . " Not Found"];
        } elseif (!in_array($data['status'], ["DEBIT", "CREDIT"], true)) {
            $error = ["Invalid", "Debit/Credit must be Debit or Credit"];
        }
        if (!empty($error)) {
            echo json_encode(["title" => $error[0], "message" => $row . $error[1], "theme" => "error"]);
            return;
        }

        $key = [
            "journal_type_id" => $journal_type->id,
            "account_number"  => $account->account_number,
            "status"          => $data['status'],
        ];
        $label = $row . $journal_type->number . " / " . $account->account_number . " " . $data['status'] . " - ";
        $exist = $this->crud->read('journal_setups', [], $key);
        if (!empty($exist->id)) {
            echo json_encode(["title" => "Already Exists", "message" => $label . "Skipped", "theme" => "success"]);
            return;
        }

        // Index = urutan terakhir di journal tsb + 1, supaya urutan baris mengikuti file
        $max  = $this->db->select_max('flag')->where('journal_type_id', $journal_type->id)->get('journal_setups')->row();
        $flag = min((int) @$max->flag + 1, 127);

        $send   = $this->crud->create('journal_setups', array_merge($key, ["ap_payment" => "NO", "ar_receipt" => "NO", "flag" => $flag]));
        $result = json_decode((string) $send, true);
        if (!is_array($result)) {
            $result = ["title" => "Error", "message" => "Failed to save data", "theme" => "error"];
        }
        $result['message'] = $label . $result['message'];
        echo json_encode($result);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/journal_setups.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/journal_setups.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/journal_setups.txt";
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
            header("Content-Disposition: attachment; filename=journal_setups_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as journal_name, c.account_name');
        $this->db->from('journal_setups a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->join('account_coa c', 'a.account_number = c.account_number');
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
                            <b>' . $config->name . '</b><br>
                            <small>MASTER JOURNAL SETUP</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Journame Name</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Debit/Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['journal_name'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
