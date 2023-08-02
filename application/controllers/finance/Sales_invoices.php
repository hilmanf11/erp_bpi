<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
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
            $this->load->view('template/header', $data);
            $this->load->view('finance/sales_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $number = base64_decode($number);
        $this->db->select('a.*, c.id as item_id, c.number as item_number, c.name as item_name, d.name as uom, b.currency');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        // $this->db->join('customer_items e', 'b.id = e.customer_id and c.id = e.item_id');
        // $this->db->join('sales_orders f', 'a.so_number = f.number and b.id = f.customer_id and c.id = f.item_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->group_by('a.item_id');
        $this->db->order_by('c.number', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readExchangeRates(){
        $customer_id = $this->input->get('customer_id');

        $records = $this->crud->query("SELECT b.selling
            FROM customers a JOIN exchange_rates b ON a.currency = b.currency_from and b.currency_to = 'IDR'
            WHERE a.id = '$customer_id'
            GROUP BY a.currency 
            ORDER BY b.created_date desc");
        echo json_encode($records);
    }

    public function readDelivery()
    {
        $customer_id = $this->input->get('customer_id');

        $records = $this->crud->query("SELECT `number`
            FROM delivery_notes
            WHERE customer_id = '$customer_id' and `status` = '0'
            GROUP BY `number` 
            ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSalesInvoices()
    {
        $data = $this->crud->query("SELECT DISTINCT `number` FROM sales_invoices WHERE `status` = '0' ORDER BY `number` ASC");
        echo json_encode($data);
    }

    public function readDeliveryNote()
    {
        $data = $this->crud->query("SELECT DISTINCT `dn_number` FROM sales_invoices WHERE `status` = '0' ORDER BY `dn_number` ASC");
        echo json_encode($data);
    }

    public function readVoucher()
    {
        $data = $this->crud->query("SELECT DISTINCT `voucher` FROM sales_invoices WHERE `status` = '0' ORDER BY `voucher` ASC");
        echo json_encode($data);
    }

    public function number($trans_date)
    {
        $datenow    = "SI-" . date("Ymd", strtotime(base64_decode($trans_date)));
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
        $dn_number = base64_decode($this->input->get('dn_number'));

        $this->db->select('a.number as dn_number, a.customer_po, d.so_number, a.item_id, b.number as item_number, b.name as item_name, f.name as uom,
            e.currency, SUM(a.qty) as qty, (CASE WHEN g.price != null THEN g.price ELSE c.price END) as price, (SUM(a.qty) * (CASE WHEN g.price != null THEN g.price ELSE c.price END)) as total');
        $this->db->from('delivery_notes a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('customer_items c', 'a.customer_id = c.customer_id and a.item_id = c.item_id');
        $this->db->join('(SELECT so_number, number FROM delivery_orders GROUP BY number) d', 'a.do_number = d.number');
        $this->db->join('customers e', 'a.customer_id = e.id');
        $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->join('sales_orders g', 'd.so_number = g.number and a.customer_id = g.customer_id and a.item_id = g.item_id', 'left');
        $this->db->where('a.number', $dn_number);
        $this->db->group_by('a.number');
        $this->db->group_by('a.customer_po');
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $obj[] = array(
                "dn_number" => $record['dn_number'],
                "so_number" => $record['so_number'],
                "customer_po" => $record['customer_po'],
                "item_id" => $record['item_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "qty" => $record['qty'],
                "price" => $record['price'],
                "total" => $record['total']
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
        $filter_dn_number = base64_decode($this->input->get('dn_number'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as customer_name');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_type == "PID") {
                $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
            } elseif ($filter_type == "PAY") {
                $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
            }
            $this->db->like('a.number', $filter_sales_invoice);
            $this->db->like('a.dn_number', $filter_dn_number);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.status', $filter_status);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.number');
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.customer_po');
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
        }
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                
                $sales_invoices = $this->crud->read('sales_invoices', [], ["dn_number" => $post['dn_number'], "customer_po" => $post['customer_po'], "item_id" => $post['item_id']]);
                if (@$sales_invoices->id != "") {
                    $send = $this->crud->update('sales_invoices', ["dn_number" => $post['dn_number'], "customer_po" => $post['customer_po'], "item_id" => $post['item_id']], $post);
                    echo $send;
                } else {
                    $send = $this->crud->create('sales_invoices', $post);
                    $update = $this->crud->update('delivery_notes', ["number" => $post['dn_number'], "customer_po" => $post['customer_po'], "item_id" => $post['item_id']], ["status" => "1"]);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

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

    public function delete()
    {
        $data = $this->input->post();
        $packing_lists = $this->crud->reads('packing_lists', [], ["dn_number" => $data['dn_number']]);

        if(count($packing_lists) > 0){
            show_error("Please delete packing list first");
        }else{
            $send = $this->crud->delete('sales_invoices', $data);
            $update = $this->crud->update('delivery_notes', ["number" => $data['dn_number']], ["status" => "0"]);
            echo $send;
        }
    }

    public function print_dn($invoice_no)
    {
        $invoice_no = base64_decode($invoice_no);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["number" => $invoice_no]);
        $sales_invoice = $this->crud->read('sales_invoices', [], ["number" => $invoice_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
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
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                b.number as item_id, 
                b.name as item_name, 
                b.description, 
                c.name as uom, 
                f.price,
                d.number as customer_number, 
                d.name as customer_name,
                d.type, 
                d.address, 
                d.address_billing, 
                d.attention, 
                d.telp_billing,
                d.telp,
                d.currency,
                g.origin,
                g.sailing,
                g.ship,
                g.incoterm,
                g.do_number,
                g.trans_type');
            $this->db->from('sales_invoices a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('delivery_notes g', 'a.dn_number = g.number and a.customer_po = g.customer_po');
            $this->db->join('delivery_orders e', 'g.do_number = e.number');
            $this->db->join('customer_items f', 'd.id = f.customer_id and b.id = f.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $invoice_no);
            $this->db->group_by('a.customer_po');
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            if ($records[0]['type'] == "EXPORT") {
                $header = ' <th width="60">Price</th>
                            <th width="60">Total</th>';
            } else {
                $title = "DELIVERY NOTE";
                $header = "";
            }

            $html .= '  <table style="width:100%;">
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
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>COMMERCIAL INVOICE</h3>
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
                                            <td><b>' . $records[0]['address'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Bill To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address_billing'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Shipper From</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['origin'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Sailing On Or About to</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['sailing'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Attention</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['attention'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Telp</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['telp'] . '</b></td>
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
                                            <td>Delivery Note No</td>
                                            <td>:</td>
                                            <td><b>' . @$sales_invoice->dn_number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['trans_type'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Ship By</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['ship'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Incoterm</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['incoterm'] . '</b></td>
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
                                        <tr>
                                            <td>Create Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($sales_invoice->created_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Sales Order No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">UoM</th>
                                        <th width="60">Qty</th>
                                        <th width="60">Currency</th>
                                        <th width="60">Price</th>
                                        <th width="60">Total</th>
                                    </tr>';
            $grand_qty = 0;
            $grand_total = 0;
            foreach ($records as $record) {
                if($record['customer_po'] == ""){
                    $sales_order_no = $record['so_number'];
                }else{
                    $sales_order_no = $record['customer_po'];
                }
                
                $grand_qty += ($record['qty']);
                $grand_total += ($record['price'] * $record['qty']);

                // if ($record['type'] == "EXPORT") {
                //     $content = '<td style="text-align:right">' . number_format($record['price'], 4, ",", ".") . '</td>
                //                 <td style="text-align:right">' . number_format(($record['price'] * $record['qty']), 2, ",", ".") . '</td>';
                // } else {
                //     $content = "";
                // }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td><span style="font-size:10px;">' . $sales_order_no . '</span></td>
                                <td style="font-size:10px;">' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 0, ",", ".") . '</td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['currency'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['price'], 4, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format(($record['price'] * $record['qty']), 2, ",", ".") . '</td>
                            </tr>';
                $no++;
            }
            
            $html .= '<tr>
                        <td colspan="5" style="text-align:right"><b>Grand Total</b></td>
                        <td style="text-align:right"><b>'.number_format($grand_qty, 0, ",", ".").'</b></td>
                        <td style="text-align:right"></td>
                        <td style="text-align:right"></td>
                        <td style="text-align:right"><b>'.number_format($grand_total, 2, ",",".").'</b></td>
                    </tr>
                    </table>';

            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $html .= '</div></div>';

            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px; font-size:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">CUSTOMER STAMP & SIGNATURE</th>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
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
        $filter_dn_number = base64_decode($this->input->get('dn_number'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_status = base64_decode($this->input->get('filter_status'));

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
        }
        $this->db->like('a.number', $filter_sales_invoice);
        $this->db->like('a.dn_number', $filter_dn_number);
        $this->db->like('a.customer_id', $filter_customer);
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

            $this->db->select('a.*');
            $this->db->from('sales_invoices a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.customer_po');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
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
                            <th>DN No</th>
                            <th>SO No</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['dn_number'] . '</td>
                                <td>' . $detail['so_number'] . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], 4) . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 4)  . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
