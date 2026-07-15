<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Feasibilitys extends CI_Controller
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
            $this->load->view('npd/feasibilitys');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function readFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.*, a.customer_id, a.number as project_number, b.volume, b.volume_unit, c.name as customer_name
        FROM create_projects a 
        JOIN create_project_details b ON a.id = b.create_project_id 
        JOIN customers c ON a.customer_id = c.id 
        ORDER BY b.item_fg_number ASC");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT 
            a.id, 
            a.number, 
            a.name
        FROM item_rm a 
        WHERE a.number LIKE '%$post%' or a.name LIKE '%$post%'");
        echo json_encode($send);
    }

    public function readsMachines()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT 
            a.toonage, 
            a.maker,
            CONCAT(a.toonage, ' - ', a.maker) AS machine_std
        FROM machines a 
        WHERE a.toonage LIKE '%$post%' or a.maker LIKE '%$post%'
        GROUP BY a.toonage, a.maker
        ORDER BY a.toonage ASC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_item_fg_number = @base64_decode($get['filter_item_fg_number']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*');
            $this->db->from('feasibilitys a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_number', $filter_item_fg_number);
            
            $this->db->order_by('b.id', 'ASC');
            // $this->db->order_by('d.plant', 'ASC');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            
            $feasibilitys = $this->crud->read('feasibilitys', [], [
                "project_number" => $post['project_number'], 
                "item_fg_id" => $post['item_fg_id']
            ]);
            
            if (empty($feasibilitys)) {
                $attachment = $this->crud->upload('attachment', ["pdf", "png", "jpg", "jpeg"], 'assets/documents/feasibilitys/', [], "feasibilitys", "attachment");
                
                $postFinal = array_merge($post, ["attachment" => $attachment]);
                $send = $this->crud->create('feasibilitys', $postFinal);
                
                echo json_encode([
                    "theme"   => "success",
                    "title"   => "Success",
                    "message" => "Data has been save!"
                ]);
                
            } else {
                echo json_encode([
                    "theme"   => "error",
                    "title"   => "Validation Error",
                    "message" => "Duplicate Data"
                ]);
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
            $attachment = $this->crud->upload('attachment', ["pdf", "png", "jpg", "jpeg"], 'assets/documents/feasibilitys/', ["id" => $id], "feasibilitys", "attachment");
            $postFinal = array_merge($post, ["attachment" => $attachment]);
            $send = $this->crud->update('feasibilitys', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('feasibilitys', $data);
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'customer_id' => $data->val($i, 2),
                'division_id' => $data->val($i, 3),
                'customer_address_id' => $data->val($i, 4),
                'item_fg_id' => $data->val($i, 5),
                'price' => $data->val($i, 6),
                'currency' => $data->val($i, 7),
                'valid_date' => $data->val($i, 8),
                'remark' => $data->val($i, 9)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/customer_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customer_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/customer_items.txt";
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
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         //Cek Process Number          //table       //field        //field excel
    //         $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id']]);

    //         if (!empty($customer_items->customer_id)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => " Customer " . $data['customer_id'] . " is Duplicate Data", "theme" => "error"));
    //         } elseif (!empty($customer_items->item_fg_id)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
    //         } else {
    //             $dataFinal = array(
    //                 //field
    //                 "customer_id" => $data['customer_id'],
    //                 "item_fg_id" => $data['item_fg_id'],
    //                 "price" => $data['price'],
    //                 "valid_date" => $data['valid_date'],
    //                 "remark" => $data['remark'],
    //             );
    //             $send   = $this->crud->create('customer_items', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    // PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=feasibility_summary_$format.xls");
        }

        $get = $this->input->get();
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_fg_number = @base64_decode($get['filter_item_fg_number']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('feasibilitys a');
        if ($filter_customer_id) { $this->db->like('a.customer_id', $filter_customer_id); }
        if ($filter_item_fg_id) { $this->db->like('a.item_fg_id', $filter_item_fg_id); }
        if ($filter_item_fg_number) { $this->db->like('a.item_fg_number', $filter_item_fg_number); }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Feasibility Data</title></head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            #feasibilitys {border-collapse: collapse;width: 100%;font-size: 11px;}
            #feasibilitys td, #feasibilitys th {border: 1px solid #ddd;padding: 4px;}
            #feasibilitys tr:nth-child(even){background-color: #f2f2f2;}
            #feasibilitys tr:hover {background-color: #ddd;}
            #feasibilitys th {padding-top: 4px;padding-bottom: 4px;text-align: center;color: black; background-color: #eaeaea;}
        </style>
        <body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
                            <img src="' . $config->favicon . '" width="40">
                        </td>
                        <td style="font-size: 14px; text-align: left; vertical-align: middle; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <div style="clear: both; font-size: 16px; text-align: center;">
                <h3>Summary Feasibility Study Data</h3>
            </div>
        </center>
        
        <table id="feasibilitys" border="1">
            <tr>
                <th width="40">No</th>
                
                <th>Product No</th>
                <th>Product Name</th>
                <th>Customer</th>
                <th>Part Size (mm)</th>
                <th>Est. Part Weight (g)</th>

                <th>Resin Material</th>
                <th>Cavity No</th>
                <th>Base Steel</th>
                <th>Core & Cavity Steel</th>
                <th>Cavity & Core Steel</th>
                <th>Mold Accessories</th>
                <th>Mould Frame Note</th>
                <th>Life Est (Shots)</th>
                <th>Finish Surface</th>
                <th>Building Standard</th>
                <th>Mould Length (mm)</th>
                <th>Mould Width (mm)</th>
                <th>Mould Height (mm)</th>
                <th>Mould Weight (kg)</th>

                <th>Injection System</th>
                <th>Ejection System</th>
                <th>No of Sliders</th>
                <th>Side Action Op</th>
                <th>No of Lifter</th>
                <th>Hot Runner Details</th>

                <th>Runner Weight (g)</th>
                <th>Cycle Time (s)</th>
                <th>Machine Size (T)</th>
                <th>Lead Time (Weeks)</th>
                <th>Target Prod (pcs/h)</th>
                <th>Mold Setting Time</th>
                <th>Qty / Year</th>
                <th>Qty / Month</th>
                <th>Qty W/P / Month</th>
                <th>Avg Load (%)</th>
                <th>Space (m2)</th>
                <th>Packaging</th>

                <th>Hot Runner FOB Price</th>
                <th>Mold Flow Cost</th>
                <th>Other Cost</th>
                <th>Target Capability</th>
                <th>Mfg Tech Alt</th>
                <th>Customer Req</th>
                <th>Prev Experience</th>
                
                <th>Man Power (Std)</th>
                <th>Man Power (Act)</th>
                <th>Machine (Std)</th>
                <th>Machine (Act)</th>
                <th>Method (Std)</th>
                <th>Method (Act)</th>
                <th>Material (Std)</th>
                <th>Material (Act)</th>
                <th>Other 4M</th>
                
                <th>Rejection PPM/COPQ</th>
                <th>Error Proofing</th>
            </tr>';
            
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td style="text-align:center;">'.$no.'</td>
                    
                    <td style="mso-number-format:\@;">'.$data['item_fg_number'].'</td>
                    <td style="mso-number-format:\@;">'.$data['item_fg_name'].'</td>
                    <td style="mso-number-format:\@;">'.$data['customer_name'].'</td>
                    <td>'.$data['part_size_mm'].'</td>
                    <td>'.$data['est_part_weight_per_pcs'].'</td>

                    <td style="mso-number-format:\@;">'.$data['item_rm_number'].'</td>
                    <td style="text-align:right;">'.$data['mould_cav_no'].'</td>
                    <td>'.$data['mould_base_steel'].'</td>
                    <td>'.$data['core_cav_steel'].'</td>
                    <td>'.$data['mould_cav_core_steel'].'</td>
                    <td>'.$data['mold_base_accessories'].'</td>
                    <td>'.$data['mould_frame_note'].'</td>
                    <td style="text-align:right;">'.$data['mould_life_estimation'].'</td>
                    <td>'.$data['mould_finish_surface'].'</td>
                    <td>'.$data['mould_building_standard'].'</td>
                    <td style="text-align:right;">'.$data['estimation_mould_length'].'</td>
                    <td style="text-align:right;">'.$data['estimation_mould_width'].'</td>
                    <td style="text-align:right;">'.$data['estimation_mould_height'].'</td>
                    <td style="text-align:right;">'.$data['estimation_mould_weight'].'</td>

                    <td>'.$data['injection_system'].'</td>
                    <td>'.$data['ejection_system'].'</td>
                    <td>'.$data['no_of_sliders'].'</td>
                    <td>'.$data['side_action_operated_by'].'</td>
                    <td>'.$data['no_of_lifter'].'</td>
                    <td>'.$data['hot_runner_details'].'</td>

                    <td style="text-align:right;">'.$data['est_runner_weight_per_pcs'].'</td>
                    <td style="text-align:right;">'.$data['est_cycle_time'].'</td>
                    <td style="text-align:right;">'.$data['est_machine_size'].'</td>
                    <td style="text-align:right;">'.$data['lead_time_1st_off_sample'].'</td>
                    <td style="text-align:right;">'.$data['target_productivity'].'</td>
                    <td>'.$data['mold_setting_time'].'</td>
                    <td style="text-align:right;">'.$data['quantity_year'].'</td>
                    <td style="text-align:right;">'.$data['quantity_month'].'</td>
                    <td style="text-align:right;">'.$data['quantity_wp_month'].'</td>
                    <td style="text-align:right;">'.$data['avg_load_cap_machine'].'</td>
                    <td style="text-align:right;">'.$data['space_needed_for_wp_fg'].'</td>
                    <td style="text-align:right;">'.$data['packaging'].'</td>

                    <td>'.$data['estimation_hot_runner_fob_price'].'</td>
                    <td>'.$data['mold_flow_analysis_cost'].'</td>
                    <td>'.$data['other_cost'].'</td>
                    <td>'.$data['target_capability'].'</td>
                    <td>'.$data['mfg_tech_alternative'].'</td>
                    <td>'.$data['customer_req'].'</td>
                    <td>'.$data['experience_from_previous_dev'].'</td>
                    
                    <td>'.$data['man_power_std'].'</td>
                    <td>'.$data['man_power_act'].'</td>
                    <td>'.$data['machine_std'].'</td>
                    <td>'.$data['machine_act'].'</td>
                    <td>'.$data['method_std'].'</td>
                    <td>'.$data['method_act'].'</td>
                    <td>'.$data['material_std'].'</td>
                    <td>'.$data['material_act'].'</td>
                    <td>'.$data['other'].'</td>
                    
                    <td>'.$data['rejection_ppm_copq'].'</td>
                    <td>'.$data['error_proofing'].'</td>
                </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_feasibility($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=feasibility_study_$format.xls");
        }

        $get = $this->input->get();
        $id = @base64_decode($get['id']);

        // Config Data
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Fetch Data Feasibility
        $this->db->select('a.*');
        $this->db->from('feasibilitys a');

        if ($id){ 
            $this->db->like('a.id', $id);
        }
       
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        // Template HTML & CSS Base
        $html = '<html>
        <head><title>Print Feasibility Study</title></head>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
            .page-break { page-break-after: always; padding-bottom: 20px; }
            .table-print { border-collapse: collapse; width: 100%; font-size: 12px; margin-bottom: 20px; }
            .table-print td, .table-print th { border: 1px solid #000; padding: 4px 8px; }
            .table-header { border-collapse: collapse; width: 100%; border: 1px solid #000; border-bottom: none; }
            .table-header td { padding: 8px; }
            .text-center { text-align: center; }
            .text-left { text-align: left; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
        </style>
        <body>';

        foreach ($records as $data) {
            
            // --- 1. LOGIKA PENANGANAN GAMBAR ---
            $img_html = "";
            if (!empty($data['attachment'])) {
                $ext = strtolower(pathinfo($data['attachment'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $img_html = '<img src="'.$data['attachment'].'" style="max-width:200px; max-height:150px; display:block; margin:auto;">';
                } else {
                    $img_html = '<i>File Terlampir: ' . basename($data['attachment']) . '</i>';
                }
            }

            $html .= '<div class="page-break">';


            $html .= '<table class="table-print">';
            
            // Baris 1: Header Logo & Judul (Mengikuti pembagian 35% dan 65% milik tabel)
            $html .= '<tr>
                        <td colspan="2" width="35%" style="text-align: left; padding: 10px;">
                            <img src="' . $config->favicon . '" width="50" style="vertical-align: middle; margin-right: 10px;">
                            <b style="vertical-align: middle; font-size: 15px;">PT. BANSHU PLASTIC INDONESIA</b>
                        </td>
                        <td colspan="2" width="65%" class="text-center font-bold" style="font-size: 16px;">
                            Feasibility Study New Project
                        </td>
                      </tr>';
            
            // Helper function untuk baris standar
            $renderRow = function($label, $value) {
                return '<tr><td colspan="2" width="35%">'.$label.'</td><td colspan="2" width="65%" class="text-center">'.$value.'</td></tr>';
            };

            // Lanjut ke isi tabel seperti biasa...
            $html .= $renderRow('Customer', $data['customer_name']);
            $html .= $renderRow('Part Name:', $data['item_fg_name']);
            
            $html .= '<tr><td colspan="2">Part Picture</td><td colspan="2" class="text-center">'.$img_html.'</td></tr>';
            
            $html .= $renderRow('Part Number:', $data['item_fg_number']);
            $html .= $renderRow('Moulding Resin Material:', $data['item_rm_number']);
            $html .= $renderRow('Mould Cavity Number:', $data['mould_cav_no']);
            $html .= $renderRow('Mould-base Steel :', $data['mould_base_steel']);
            $html .= $renderRow('Core & Cavity plate Steel :', $data['core_cav_steel']);
            $html .= $renderRow('Mould Cavity & Core Steel:', $data['mould_cav_core_steel']);
            $html .= $renderRow('Mold base Accessories', $data['mold_base_accessories']);
            $html .= $renderRow('No. of sliders:', $data['no_of_sliders']);
            $html .= $renderRow('Side actions operated by', $data['side_action_operated_by']);
            $html .= $renderRow('No. of lifter:', $data['no_of_lifter']);
            $html .= $renderRow('Injection System:', $data['injection_system']);
            $html .= $renderRow('Ejection System:', $data['ejection_system']);
            $html .= $renderRow('Mould Frame Note:', $data['mould_frame_note']);
            $html .= $renderRow('Mould life estimation', $data['mould_life_estimation'] . ' years');
            $html .= $renderRow('Mould Finish Surface:', $data['mould_finish_surface']);
            $html .= $renderRow('Part size/mm', $data['part_size_mm']);
            $html .= $renderRow('Est. part weight/g per pcs', $data['est_part_weight_per_pcs']);
            $html .= $renderRow('Est. runner weight/g per pcs', $data['est_runner_weight_per_pcs']);
            $html .= $renderRow('Est. cycle time ( Sec )', $data['est_cycle_time']);
            $html .= $renderRow('Est. Machine size ( Ton )', $data['est_machine_size'] . 'T');
            
            $mould_size = $data['estimation_mould_length'] . ' x ' . $data['estimation_mould_width'] . ' x ' . $data['estimation_mould_height'];
            $html .= '<tr><td colspan="2">Estimation Mould Size: (mm)</td><td colspan="2" class="text-center">'.$mould_size.'</td></tr>';
            
            $html .= $renderRow('Estimation Mould Weigh: (kg)', $data['estimation_mould_weight']);
            $html .= $renderRow('Lead time to 1st off samples: (Weeks)', $data['lead_time_1st_off_sample'] . ' weeks');
            $html .= $renderRow('Mould Building Standard:', $data['mould_building_standard']);
            $html .= $renderRow('Estimation Hot Runner FOB Price:', $data['estimation_hot_runner_fob_price']);
            $html .= $renderRow('Hot Runner Details', $data['hot_runner_details']);
            $html .= $renderRow('Mold Flow Analysis cost:', $data['mold_flow_analysis_cost']);
            $html .= $renderRow('Other cost:', $data['other_cost']);
            $html .= $renderRow('Target Productivity (Pcs/Hour) :', $data['target_productivity']);
            $html .= $renderRow('Mold setting time :', $data['mold_setting_time']);
            $html .= $renderRow('Target Capability :', $data['target_capability']);
            $html .= $renderRow('Mfg Tech Alternative :', $data['mfg_tech_alternative']);
            $html .= $renderRow('Customer Requirement :', $data['customer_req']);
            $html .= $renderRow('Experience From Previous Developments :', $data['experience_from_previous_dev']);
            
            // --- 4. AREA KHUSUS 4M ANALYSIS (ROWSPAN) ---
            // Baris pertama (MANPOWER) 
            $html .= '<tr>
                        <td rowspan="5" width="35%">4M Analysis of the previous project experiences</td>
                        <td width="20%" class="text-center">MANPOWER</td>
                        <td width="22.5%">STD : '.$data['man_power_std'].'</td>
                        <td width="22.5%">ACT : '.$data['man_power_act'].'</td>
                      </tr>';
            $html .= '<tr>
                        <td class="text-center">MACHINE</td>
                        <td>STD : '.$data['machine_std'].'</td>
                        <td>ACT : '.$data['machine_act'].'</td>
                      </tr>';
            $html .= '<tr>
                        <td class="text-center">METHOD</td>
                        <td>STD : '.$data['method_std'].'</td>
                        <td>ACT : '.$data['method_act'].'</td>
                      </tr>';
            $html .= '<tr>
                        <td class="text-center">MATERIAL</td>
                        <td>STD : '.$data['material_std'].'</td>
                        <td>ACT : '.$data['material_act'].'</td>
                      </tr>';
            $html .= '<tr>
                        <td class="text-center">OTHERS</td>
                        <td colspan="2">'.$data['other'].'</td>
                      </tr>';
            // ---------------------------------------------
                      
            $html .= $renderRow('Rejection PPM or COPQ', $data['rejection_ppm_copq']);
            $html .= $renderRow('Packaging :', $data['packaging']);
            $html .= $renderRow('Error proofing :', $data['error_proofing']);
            
            $html .= $renderRow('Quantity / Year (Pcs) :', number_format((float)$data['quantity_year'], 0, ',', '.'));
            $html .= $renderRow('Quantity / Month (Pcs) :', number_format((float)$data['quantity_month'], 0, ',', '.'));
            $html .= $renderRow('Quantity W/P / Month (Pcs) :', number_format((float)$data['quantity_wp_month'], 0, ',', '.'));
            
            $html .= $renderRow('Avg Load Capacity per machine (%) :', $data['avg_load_cap_machine']);
            $html .= $renderRow('Space needed for W/P & FG (m2) :', $data['space_needed_for_wp_fg']);

            $html .= '</table>';
            $html .= '</div>'; 
        }

        if (empty($records)) {
            $html .= '<h3 style="text-align:center; font-family:arial;">Data tidak ditemukan.</h3>';
        }

        $html .= '</body></html>';
        
        echo $html;
    }
}
