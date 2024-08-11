<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Checksheets extends CI_Controller
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
        $this->form_validation->set_rules('wo_no', 'Wo No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/checksheets');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('checksheets', ["name" => $post]);
        echo json_encode($send);
    }

    public function readWoNo()
    {
        $send = $this->crud->query("SELECT DISTINCT a.wo_no, a.period, a.qty , a.item_fg_id, a.item_fg_name as product_name , b.number as product_no
        FROM production_schedules a
        JOIN item_fg b on a.item_fg_id = b.id 
        WHERE a.status = 0 and wo_no != '' 
        ORDER BY a.wo_no DESC");
        echo json_encode($send);
    }

    public function readWoNos()
    {
        $send = $this->crud->query("SELECT DISTINCT wo_no
        FROM checksheets
        WHERE `deleted` = 0
        ORDER BY wo_no DESC");
        echo json_encode($send);
    }

    public function readChecksheet()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM checksheets
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    public function readItems($item_fg_id)
    {
        $item_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT mpq, qty_box
            FROM item_fg a
            WHERE `status` = 0 and id = '$item_id' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function checkWo_no($wo_no)
    {
        $wono = base64_decode($wo_no);
        $send = $this->crud->query("SELECT COALESCE(SUM(receipt),0) as qty
            FROM checksheets
            WHERE wo_no = '$wono' 
            ORDER BY id DESC");
        echo json_encode($send);
    }
    public function readEmployesOP(){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/operator");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    public function readEmployesQC(){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/qc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    // public function readWorkorder($filter = "")
    // {
    //     if ($filter == "") {
    //         $join = "LEFT JOIN checksheets b ON a.wo_no = b.wo_no";
    //         $having = "having (a.qty - SUM(coalesce(b.receipt, 0))) > 0";
    //     } else {
    //         $join = "JOIN checksheets b ON a.wo_no = b.wo_no";
    //         $having = "";
    //     }

    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $send = $this->crud->query("SELECT a.*, d.number as product_no, d.name as product_name, coalesce(SUM(b.receipt), 0) as accumulate, (a.qty - SUM(coalesce(b.receipt, 0))) as balance FROM production_schedules a 
    //     $join
    //     JOIN item_fg d ON a.item_fg_id = d.id 
    //     WHERE a.status = '1' and a.workorder like '%$post%'
    //     GROUP BY a.workorder
    //     $having
    //     order by a.workorder desc");
    //     echo json_encode($send);
    // }

    public function checksheet_id($trans_date)
    {
        $datenow = date("Y-m", strtotime($trans_date));
        $datenow2 = date("Ymd", strtotime($trans_date));

        $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM checksheets WHERE trans_date like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "CS" . $datenow2 . "-" . $autoID;
        return $workOrderNo;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_wo_no = $this->input->get('filter_wo_no');
            $filter_checksheet = $this->input->get('filter_checksheet');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $sort = $this->input->post('sort');
            $order = $this->input->post('order');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.number as product_no, c.name as product_name, c.id as product_id, c.uom');
            $this->db->from('checksheets a');
            $this->db->join('production_schedules b', 'a.wo_no = b.wo_no');
            $this->db->join('item_fg c', 'b.item_fg_id = c.id');
            // $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.wo_no', $filter_wo_no);
            $this->db->like('a.number', $filter_checksheet);
            $this->db->order_by($sort, $order);
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
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if ($post['receipt'] > 0) {
                $checksheet_id = $this->checksheet_id($post['trans_date']);
                $checksheet = $this->crud->reads("checksheets", [], ["wo_no" => $post['wo_no'], "trans_date" => $post['trans_date'], "shift" => $post['shift'], "accumulate" => $post['accumulate']]);
                if (count($checksheet) == 0) {
                    $send = $this->crud->create('checksheets', array_merge($post, array("number" => $checksheet_id)));
                    if($post['accumulate'] == $post ['qty']){
                        $update = $this->crud->update('production_schedules', ["wo_no" => $post['wo_no'], "item_fg_id" => $post['item_fg_id']], ["status" => 1]);
                    }
                    echo $send;
                } else {
                    show_error("Duplicate Data");
                }
            } else {
                show_error("Receipt Qty cannot <= 0");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('checksheets', ["id" => $data['id']]);
        echo $send;
    }

    public function print_label($id)
    {
        $id = base64_decode($id);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, c.number as product_no, c.name as product_name, c.uom');
        $this->db->from('checksheets a');
        $this->db->join('production_schedules b', 'a.wo_no = b.wo_no');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id');
        // $this->db->join('uom e', 'c.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->order_by('a.trans_date', 'DESC');
        $checksheet = $this->db->get()->row();

        //Generate QRcode
        $this->createQrcode($checksheet->number, "assets/image/qrcode/");

        $html = '<html>
        <head>
            <title>' . $checksheet->number . '</title>
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
                    <p>Paper Size A5, Layout Potrait</p>
                    <p>Margin Default, Scale 98</p>
                </center>
            </div>
            <div class="print">
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    <table style="width:100%; font-size:10px;">
                        <tr>
                            <td width="80">Doc No</td>
                            <td width="5">:</td>
                            <td width="100">' . $config_iso->doc_checksheet . '</td>
                        </tr>
                        <tr>
                            <td>Form</td>
                            <td>:</td>
                            <td>' . $config_iso->form_checksheet . '</td>
                        </tr>
                        <tr>
                            <td>Print Date</td>
                            <td>:</td>
                            <td>' . date("d M Y H:m:s") . '</td>
                        </tr>
                        <tr>
                            <td>Print By</td>
                            <td>:</td>
                            <td>' . $this->session->username . '</td>
                        </tr>
                    </table>
                </div>

                <br><br><br><br>
                <center>
                    <h3 style="margin:0;"><u>FINAL CHECK SHEET</u></h3>
                    <b style="font-size:12px;">Doc. No ' . $checksheet->number . '</b>
                </center>
                <br>
                <div style="float:left; width:80%;"> 
                    <table style="width:100%; font-size:12px;">
                        <tr>
                            <td width="100" style="padding:5px;">Date</td>
                            <td width="20">:</td>
                            <td><b>' . date("d F Y", strtotime($checksheet->trans_date)) . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">Product No</td>
                            <td>:</td>
                            <td><b>' . $checksheet->product_no . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">Product Name</td>
                            <td>:</td>
                            <td><b>' . $checksheet->product_name . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">WO. No</td>
                            <td>:</td>
                            <td><b>' . $checksheet->wo_no . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">WO. Qty</td>
                            <td>:</td>
                            <td><b>' . $checksheet->qty . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">Receipt Qty</td>
                            <td>:</td>
                            <td><b>' . $checksheet->receipt . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;">Accumulate</td>
                            <td>:</td>
                            <td><b>' . $checksheet->accumulate . '</b></td>
                        </tr>
                        <tr>
                            <td style="padding:5px; vertical-align:top;">Remarks</td>
                            <td style="vertical-align:top;">:</td>
                            <td style="vertical-align:top;"><b>' . $checksheet->remarks . '</b></td>
                        </tr>
                    </table>
                </div>
                <div style="float:left; width:20%; text-align:center;">
                    <img src="' . base_url('assets/image/qrcode/' . $checksheet->number . '.png') . '" width="100"/>
                </div>
                <table id="customers" style="margin-top:20px;">
                    <tr>
                        <th width="200" style="text-align:center;">Production</th>
                        <th width="200" style="text-align:center;">QC</th>
                        <th width="200" style="text-align:center;">DC</th>
                    </tr>
                    <tr>
                        <th style="height:80px;"></th>
                        <th style="height:80px;"></th>
                        <th style="height:80px;"></th>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;"></th>
                        <th style="height:20px; text-align:center;"></th>
                        <th style="height:20px; text-align:center;"></th>
                    </tr>
                </table>
            </div>
            <script>window.print()</script>
        </body>
    </html>';
        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=checksheets_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_wo_no = $this->input->get('filter_wo_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, c.number as product_no, c.name as product_name, c.uom');
        $this->db->from('checksheets a');
        $this->db->join('production_schedules b', 'a.wo_no = b.wo_no');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id');
        // $this->db->join('uom e', 'c.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.wo_no', $filter_wo_no);
        $this->db->order_by('a.number', 'ASC');
        $this->db->order_by('a.wo_no', 'ASC');
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
                                <small>FINAL CHECKSHEET</small>
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
                    <th width="20">No</th>
                    <th>Checksheet ID</th>
                    <th>Wo_No</th>
                    <th>Trans Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Receipt</th>
                    <th>Accumulate</th>
                    <th>Balance</th>
                    <th>Prod Date</th>
                    <th>Packing Date</th>
                    <th>QC 1</th>
                    <th>QC 2</th>
                    <th>Operator 1</th>
                    <th>Operator 2</th>
                    <th>Shift</th>
                    <th>Packing</th>
                    <th>Packing Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['wo_no'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['receipt']) . '</td>
                            <td>' . number_format($data['accumulate']) . '</td>
                            <td>' . number_format($data['balance']) . '</td>
                            <td>' . $data['prod_date'] . '</td>
                            <td>' . $data['packing_date'] . '</td>
                            <td>' . $data['qc_1'] . '</td>
                            <td>' . $data['qc_2'] . '</td>
                            <td>' . $data['op_1'] . '</td>
                            <td>' . $data['op_2'] . '</td>
                            <td>' . $data['shift'] . '</td>
                            <td>' . $data['packing'] . '</td>
                            <td>' . $data['packing_qty'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
