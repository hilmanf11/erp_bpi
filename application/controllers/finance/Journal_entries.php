<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_entries extends CI_Controller
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
            $this->load->view('finance/journal_entries');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-10 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readAssetNo($category_id, $month, $year)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $category = base64_decode($category_id);
        $date = $year . "-" . $month;

        $send = $this->crud->query("SELECT DISTINCT asset_no as number, asset_name as name FROM asset_journals WHERE asset_category_number = '$category' and periode = '$date' and (`asset_no` like '%$post%' or asset_name like '%$post%')");
        echo json_encode($send);
    }

    public function getData()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $category = $this->input->post('category');
            $number = $this->input->post('number');

            $period = date("Y-m-t", strtotime($year . "-" . $month));

            $this->db->select('*');
            $this->db->from('asset_fixeds');
            $this->db->where('trans_date <=', $period);
            // $this->db->where("DATE_ADD(trans_date, INTERVAL estimate_month MONTH) >=", $period);
            $this->db->where("expired_date >=", $period);
            $this->db->like('asset_category_number', $category);
            $this->db->like('number', $number);
            $this->db->order_by('number', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_category = base64_decode($this->input->get('filter_category'));
            $filter_asset_no = base64_decode($this->input->get('filter_asset_no'));

            $filter_period = $filter_year . "-" . $filter_month;

            //Select Query
            $this->db->select('b.*, d.number as gl_no, a.expired_date, 
            c.name as asset_category_name,
            a.purchase_invoice_number, a.trans_date as purchase_date, a.cost, 
            a.estimate_month, a.estimate_year');
            $this->db->from('asset_journals b');
            $this->db->join('asset_fixeds a', 'a.number = b.asset_no');
            $this->db->join('asset_categories c', 'a.asset_category_number = c.number');
            $this->db->join('journal_postings d', "b.asset_no = d.document_no and b.periode = DATE_FORMAT(d.journal_date, '%Y-%m')", 'left');
            $this->db->where('b.periode', $filter_period);
            if($filter_category != ""){
                $this->db->where('b.asset_category_number', $filter_category);
            }
            $this->db->like('b.asset_no', $filter_asset_no);
            $this->db->group_by('b.id');
            $this->db->order_by('b.periode', 'asc');
            $this->db->order_by('a.number', 'asc');

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

    public function calculate($category, $total)
    {
        $category_number = base64_decode($category);
        $asset_categories = $this->crud->reads("asset_categories", [], ["number" => $category_number]);
        $no = 1;
        foreach ($asset_categories as $asset_category) {
            if ($asset_category->account_type == "DEBIT") {
                $debit = $total;
                $credit = 0;
            } else {
                $debit = 0;
                $credit = $total;
            }

            $data[] = array(
                "asset_category_number" => $category_number,
                "account_number" => $asset_category->account_number,
                "account_name" => $asset_category->account_name,
                "debit" => $debit,
                "credit" => $credit,
                "flag" => $no,
            );

            $no++;
        }

        echo json_encode($data);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $asset_journals = $this->crud->read('asset_journals', [], ["asset_no" => $post['asset_no'], "periode" => $post['periode']]);

            if (@$asset_journals->id != "") {
                echo json_encode(array("title" => "Duplicate", "message" => "Asset No " . $post['asset_no'] . " in Period " . $post['periode'] . " Duplicate", "theme" => "error"));
            } else {
                $asset_categories = $this->crud->reads("asset_categories", [], ["number" => $post['asset_category_number']]);

                $send = json_encode(array("title" => "Not Found", "message" => "Asset Category " . $post['asset_category_number'] . " Not Found", "theme" => "error"));
                foreach ($asset_categories as $asset_category) {

                    if ($asset_category->account_type == "DEBIT") {
                        $debit = $post['depreciation'];
                        $credit = 0;
                    } else {
                        $debit = 0;
                        $credit = $post['depreciation'];
                    }

                    $data = array(
                        "asset_category_number" => $post['asset_category_number'],
                        "asset_no" => $post['asset_no'],
                        "asset_name" => $post['asset_name'],
                        "periode" => $post['periode'],
                        "trans_date" => $post['trans_date'],
                        "account_number" => $asset_category->account_number,
                        "account_name" => $asset_category->account_name,
                        "debit" => $debit,
                        "credit" => $credit,
                    );

                    $send = $this->crud->create('asset_journals', $data);
                }

                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function saveJournal()
    {
        $post = $this->input->post();
        $asset_journal_details = $this->crud->reads('asset_journal_details', [], ["periode" => $post['periode'], "asset_category_number" => $post['asset_category_number'], "account_number" => $post['account_number']]);

        if (count($asset_journal_details) > 0) {
            $send = $this->crud->update('asset_journal_details', ["periode" => $post['periode'], "asset_category_number" => $post['asset_category_number'], "account_number" => $post['account_number']], $post);
            echo $send;
        } else {
            $send = $this->crud->create('asset_journal_details', $post);
            echo $send;
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_journals', $data);
        echo $send;
    }

    public function uploadclearFailed()
    {
        @unlink('failed/journal_entries.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/journal_entries.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/journal_entries.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=journal_entries_$format.xls");
        }

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_category = base64_decode($this->input->get('filter_category'));
        $filter_asset_no = base64_decode($this->input->get('filter_asset_no'));

        $filter_period = $filter_year . "-" . $filter_month;

        //Select Query
        $this->db->select('b.*, d.number as gl_no, a.expired_date, 
            c.name as asset_category_name,
            a.purchase_invoice_number, a.trans_date as purchase_date, a.cost, 
            a.estimate_month, a.estimate_year');
        $this->db->from('asset_journals b');
        $this->db->join('asset_fixeds a', 'a.number = b.asset_no');
        $this->db->join('asset_categories c', 'a.asset_category_number = c.number');
        $this->db->join('journal_postings d', "b.asset_no = d.document_no and b.periode = DATE_FORMAT(d.journal_date, '%Y-%m')", 'left');
        $this->db->where('b.periode', $filter_period);
        if($filter_category != ""){
            $this->db->where('b.asset_category_number', $filter_category);
        }
        $this->db->like('b.asset_no', $filter_asset_no);
        $this->db->group_by('b.id');
        $this->db->order_by('b.periode', 'asc');
        $this->db->order_by('a.number', 'asc');
        //Get Data Array
        $records = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>JOURNAL ENTRIES</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
            <tr>
                <th>No</th>
                <th>Period</th>
                <th>Trans Date</th>
                <th>GL No</th>
                <th>Asset No</th>
                <th>Asset Name</th>
                <th>Asset Category</th>
                <th>Purchase Invoice</th>
                <th>Purchase Date</th>
                <th>Asset Cost</th>
                <th>Est. Economic<br>(Year)</th>
                <th>Est. Economic<br>(Month)</th>
                <th>Expired Date</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['periode'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['gl_no'] . '</td>
                        <td>' . $data['asset_no'] . '</td>
                        <td>' . $data['asset_name'] . '</td>
                        <td>' . $data['asset_category_name'] . '</td>
                        <td>' . $data['purchase_invoice_number'] . '</td>
                        <td>' . $data['purchase_date'] . '</td>
                        <td style="text-align:right;">' . number_format($data['cost']) . '</td>
                        <td style="text-align:right;">' . number_format($data['estimate_year']) . '</td>
                        <td style="text-align:right;">' . number_format($data['estimate_month']) . '</td>
                        <td>' . $data['expired_date'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td style="text-align:right;">' . number_format($data['debit']) . '</td>
                        <td style="text-align:right;">' . number_format($data['credit']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
