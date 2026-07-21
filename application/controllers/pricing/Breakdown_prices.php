<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Breakdown_prices extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[breakdown_prices.item_fg_id]');
    }
    // public function index()
    // {
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
    //         $data['button'] = $this->getbutton($this->id_menu());
    //         $this->load->view('template/header', $data);
    //         $this->load->view('pricing/breakdown_prices');
    //     } else {
    //         redirect('error_access');
    //     }
    // }

    //HALAMAN UTAMA
    //INDEX untuk kebutuhan NPD
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        }
        
        $url_menu_id = $this->input->get('menu_id');
        $active_menu = (!empty($url_menu_id)) ? $url_menu_id : $this->id_menu();

        if ($this->checkuserAccess($active_menu) > 0) {
            // Ambil button agar tidak ada error undefined variable
            $data['button'] = $this->getbutton($active_menu);

            $this->load->view('template/header', $data);
            $this->load->view('pricing/breakdown_prices', $data);
        } else {
            redirect('error_access');
        }
    }
   
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readItems()
    {
        $post = $this->input->post('q') ? $this->input->post('q') : "";

        $this->db->select('*');
        $this->db->from('cost_patterns');

        if (!empty($post)) {
            $this->db->group_start();
            $this->db->like('item_fg_number', $post);
            $this->db->or_like('item_fg_name', $post);
            $this->db->or_like('item_fg_id', $post);
            $this->db->or_like('customer_name', $post);
            $this->db->group_end();
        }

        // Tampilkan semua item secara unik
        $this->db->group_by('item_fg_id');
        $this->db->group_by('p_month');
        $this->db->group_by('p_year');
        $this->db->group_by('revision');
        $this->db->group_by('customer_id');
        $this->db->order_by('item_fg_number', 'ASC');
        
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }

    public function readItemOptions($type)
    {
        $item_id = $this->input->get('item_id');
        
        if (empty($item_id)) {
            echo json_encode([]);
            return;
        }

        $this->db->select($type);
        $this->db->from('cost_patterns');
        $this->db->where('item_fg_id', $item_id);
        $this->db->group_by($type);
        $this->db->order_by($type, 'ASC');
        $query = $this->db->get()->result_array();

        $result = array();
        foreach ($query as $row) {
            $value = $row[$type];
            
            if ($type == 'p_month') {
                $monthName = date("F", mktime(0, 0, 0, (int)$value, 10));
                
                $paddedValue = sprintf("%02d", (int)$value); 
                
                $result[] = array("id" => $paddedValue, "name" => $monthName);
            } else {
                $result[] = array("id" => $value, "name" => $value);
            }
        }

        echo json_encode($result);
    }

    public function get_sub_total_1()
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $p_month    = $this->input->post('p_month');
        $p_year     = $this->input->post('p_year');
        $revision   = $this->input->post('revision');
        $customer_id= $this->input->post('customer_id');

        // Tarik nilai cost langsung dari cost_patterns
        $this->db->select("item_rm_id_vg, virgin_cost, item_rm_id_mb, mb_cost, item_rm_id_cp, child_part_cost");
        $this->db->from("cost_patterns");
        $this->db->where([
            "item_fg_id" => $item_fg_id,
            "customer_id"=> $customer_id,
            "p_month"    => $p_month,
            "p_year"     => $p_year,
            "revision"   => $revision,
            "deleted"    => 0
        ]);

        $details = $this->db->get()->result();

        $sub_total_1 = 0;

        if (!empty($details)) {
            foreach ($details as $d) {
                // Jumlahkan jika id materialnya terisi
                if (!empty($d->item_rm_id_vg)) {
                    $sub_total_1 += (float)$d->virgin_cost;
                }
                if (!empty($d->item_rm_id_mb)) {
                    $sub_total_1 += (float)$d->mb_cost;
                }
                if (!empty($d->item_rm_id_cp)) {
                    $sub_total_1 += (float)$d->child_part_cost;
                }
            }
        }

        echo json_encode([
            'status'      => 'success',
            'sub_total_1' => $sub_total_1
        ]);
    }

    public function get_quotation_number()
    {
        $date = $this->input->post('date');
        $time = strtotime($date);
        $shortYear = date('y', $time);
        $monthRomawi = $this->get_romawi(date('n', $time));

        $this->db->select("MAX(CAST(SUBSTRING_INDEX(quotation_number, '/', 1) AS UNSIGNED)) as max_no"); 
        $this->db->like('quotation_number', "/BPI-MKT/QUOT/");
        $this->db->like('quotation_number', "/$shortYear", 'before'); 
        
        $query = $this->db->get('breakdown_prices');
        $row = $query->row();
        $next_val = ($row && $row->max_no) ? intval($row->max_no) + 1 : 1;
        $no_urut = sprintf('%03d', $next_val);
        $result = "$no_urut/BPI-MKT/QUOT/$monthRomawi/$shortYear";
        
        echo json_encode(['number' => $result]);
    }

    // Helper untuk angka Romawi
    private function get_romawi($month)
    {
        $map = [1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI', 7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII'];
        return $map[$month];
    }

    public function datatables()
    {
        $get = $this->input->get();

        $filter_period_month = $get['filter_period_month'];
        $filter_period_year = $get['filter_period_year'];
        $filter_from = $get['filter_from'];
        $filter_to = $get['filter_to'];
        $filter_item_fg_id = $get['filter_item_fg_id'];
        $filter_revision = $get['filter_revision'];
        $filter_customer_id = $get['filter_customer_id'];

        // Ambil pagination dan sorting
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        // Pagination 1-10
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        // Select Query Utama
        $this->db->select('a.*');
        $this->db->from('breakdown_prices a');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.quotation_date >=', $filter_from);
            $this->db->where('a.quotation_date <=', $filter_to);
        }
        if ($filter_period_month != "") {
            $this->db->where('a.p_month', $filter_period_month);
        }

        if ($filter_period_year != "") {
            $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_customer_id != "") {
            $this->db->where('a.customer_id', $filter_customer_id);
        }

        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.revision');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.revision_quotation_number');
        
        $this->db->order_by($sort, $order);
        $totalRows = $this->db->count_all_results('', false);

        // Batasi hasil query sesuai pagination
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        // Kembalikan hasil sebagai JSON
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //CREATE DATA
    // public function create()
    // {
    //     if ($this->input->post()) {

    //         $post = $this->input->post();

    //         // cek data duplikat
    //         $exists = $this->db->get_where('breakdown_prices', [
    //             'item_fg_id' => $post['item_fg_id'],
    //             'revision'   => $post['revision'],
    //             'p_month'    => $post['p_month'],
    //             'p_year'     => $post['p_year'],
    //         ])->num_rows();

    //         if ($exists > 0) {
    //             echo json_encode([
    //                 'status'  => false,
    //                 'message' => 'Items and revisions for the period already exist'
    //             ]);
    //             return;
    //         }

    //         // simpan data
    //         $send = $this->crud->create('breakdown_prices', $post);

    //         echo json_encode([
    //             'status'  => true,
    //             'theme'   => 'success',
    //             'message' => 'Data Save Succesfully',
    //             'data'    => $send
    //         ]);

    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {

            $post = $this->input->post();

            // 1. Cek data duplikat
            $exists = $this->db->get_where('breakdown_prices', [
                'item_fg_id' => $post['item_fg_id'],
                'revision'   => $post['revision'],
                'p_month'    => $post['p_month'],
                'p_year'     => $post['p_year'],
            ])->num_rows();

            if ($exists > 0) {
                echo json_encode([
                    'status'  => false,
                    'theme'   => 'error',
                    'message' => 'Items and revisions for the period already exist'
                ]);
                return;
            }

            // =========================================================
            // 2. MULAI PERHITUNGAN (KALKULASI)
            // =========================================================
            
            // A. Sub Total 1 (Sudah didapat dari form hidden input hasil AJAX)
            $sub_total_1 = isset($post['sub_total_1']) ? (float)$post['sub_total_1'] : 0;

            // B. Sub Total 2 (Process Cost)
            $cavity_standard = (float)$post['cavity_standard'];
            $cav_std = ($cavity_standard > 0) ? $cavity_standard : 1;
            
            $cycle_time         = (float)$post['cycle_time'];
            $plain_rate_sec     = (float)$post['plain_rate_sec'];
            $cycle_time_process = (float)$post['cycle_time_process'];
            $labour_cost        = (float)$post['labour_cost'];

            $inj_cost         = ($cycle_time / $cav_std) * $plain_rate_sec;
            $sec_process_cost = $cycle_time_process * $labour_cost;
            
            $sub_total_2 = $inj_cost + $sec_process_cost;

            // C. Sub Total 3 (Amortization)
            $mold_depr          = isset($post['mold_depreciation']) ? (float)$post['mold_depreciation'] : 0;
            $dies_price         = (float)$post['dies_price'];
            $jig_price          = (float)$post['jig_price'];
            $tooling_price      = (float)$post['tooling_price'];
            $fixture_cost_price = (float)$post['fixture_cost_price'];

            // Sesuai permintaan user: Mold Depr + Dies + Jig + Tooling + Fixture
            $sub_total_3 = $mold_depr + $dies_price + $jig_price + $tooling_price + $fixture_cost_price;

            // D. Hitung Biaya Tambahan (Overhead & Profit)
            $adm_foh_cost            = (float)$post['adm_foh_cost'];
            $ng_ratio_cost           = (float)$post['ng_ratio_cost'];
            $mtn_cost                = (float)$post['mtn_cost'];
            $total_packing_cost      = (float)$post['total_packing_cost'];
            $transportasion_cost_pcs = (float)$post['transportasion_cost_pcs'];
            $purging_cost            = (float)$post['purging_cost'];
            $profit_nominal          = (float)$post['profit_nominal'];

            // E. Kalkulasi Grand Total dan Selling Price
            $grand_total = $sub_total_1 + $sub_total_2 + $sub_total_3 
                        + $adm_foh_cost + $ng_ratio_cost + $mtn_cost 
                        + $total_packing_cost + $transportasion_cost_pcs 
                        + $purging_cost + $profit_nominal;

            $selling_price = round($grand_total); // Pembulatan standar

            // =========================================================
            // 3. PREPARE DATA UNTUK DISIMPAN
            // =========================================================
            
            // Tambahkan hasil perhitungan ke dalam array $post agar ikut tersimpan
            $post['sub_total_2'] = $sub_total_2;   // Menyimpan nilai desimal asli
            $post['sub_total_3'] = $sub_total_3;   // Menyimpan nilai desimal asli
            $post['sub_total']   = $grand_total;   // Menyimpan nilai desimal asli
            $post['grand_total'] = $selling_price; // Menyimpan nilai hasil round
            
            

            // 4. Simpan Data ke Database
            $send = $this->crud->create('breakdown_prices', $post);

            if ($send) {
                echo json_encode([
                    'status'  => true,
                    'theme'   => 'success',
                    'message' => 'Data Save Successfully. Selling Price: ' . number_format($selling_price, 2),
                    'data'    => $send
                ]);
            } else {
                echo json_encode([
                    'status'  => false,
                    'theme'   => 'error',
                    'message' => 'Failed to save data to database.'
                ]);
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();

    //         $old = $this->db->get_where('breakdown_prices', ['id' => $id])->row();

    //         if (!$old) {
    //             show_error("Data not found");
    //         }

    //         $post['revision_quotation_number'] = (int)$old->revision_quotation_number + 1;
    //         $send = $this->crud->update('breakdown_prices',['id' => $id],$post);
    //         echo $send;

    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();

            // 1. Dapatkan data lama sebagai patokan
            $old = $this->db->get_where('breakdown_prices', ['id' => $id])->row();

            if (!$old) {
                echo json_encode([
                    'status'  => false,
                    'theme'   => 'error',
                    'message' => 'Data not found in database.'
                ]);
                return;
            }

            // =========================================================
            // 2. MODIFIKASI UNTUK MENJADI DATA BARU
            // =========================================================
            
            // Buang ID bawaan dari form (jika ada) agar library CRUD men-generate ID baru
            if (isset($post['id'])) {
                unset($post['id']);
            }

            // Naikkan revisi quotation
            $post['revision_quotation_number'] = (int)$old->revision_quotation_number + 1;

            // =========================================================
            // 3. KALKULASI ULANG HARGA SEPERTI PADA FUNGSI CREATE
            // =========================================================
            
            // A. Sub Total 1
            $sub_total_1 = isset($post['sub_total_1']) ? (float)$post['sub_total_1'] : 0;

            // B. Sub Total 2 (Process Cost)
            $cavity_standard = (float)$post['cavity_standard'];
            $cav_std = ($cavity_standard > 0) ? $cavity_standard : 1;
            $inj_cost = ((float)$post['cycle_time'] / $cav_std) * (float)$post['plain_rate_sec'];
            $sec_process_cost = (float)$post['cycle_time_process'] * (float)$post['labour_cost'];
            $sub_total_2 = $inj_cost + $sec_process_cost;

            // C. Sub Total 3 (Amortization)
            $mold_depr = isset($post['mold_depreciation']) ? (float)$post['mold_depreciation'] : 0;
            $sub_total_3 = $mold_depr + (float)$post['dies_price'] + (float)$post['jig_price'] + 
                        (float)$post['tooling_price'] + (float)$post['fixture_cost_price'];

            // D. Overhead & Profit
            $overhead_profit = (float)$post['adm_foh_cost'] + (float)$post['ng_ratio_cost'] + 
                            (float)$post['mtn_cost'] + (float)$post['total_packing_cost'] + 
                            (float)$post['transportasion_cost_pcs'] + (float)$post['purging_cost'] + 
                            (float)$post['profit_nominal'];

            // E. Kalkulasi Grand Total
            $grand_total = $sub_total_1 + $sub_total_2 + $sub_total_3 + $overhead_profit;
            $selling_price = round($grand_total);

            // Masukkan hasil hitung ke $post
            $post['sub_total']   = $grand_total;
            $post['grand_total'] = $selling_price;
            
            // (Opsional) Hapus unset jika kolom sub_total_1 dll ada di database
            // if(isset($post['sub_total_1'])) unset($post['sub_total_1']);
            // if(isset($post['sub_total_2'])) unset($post['sub_total_2']);
            // if(isset($post['sub_total_3'])) unset($post['sub_total_3']);

            // =========================================================
            // 4. SIMPAN SEBAGAI DATA BARU (INSERT)
            // =========================================================
            
            // Kita gunakan create(), bukan update()
            $send = $this->crud->create('breakdown_prices', $post);

            if ($send) {
                echo json_encode([
                    'status'  => true,
                    'theme'   => 'success',
                    'title'   => 'Success',
                    'message' => 'New revision created successfully! Selling Price: ' . number_format($selling_price, 2),
                    'data'    => $send
                ]);
            } else {
                echo json_encode([
                    'status'  => false,
                    'theme'   => 'error',
                    'title'   => 'Failed',
                    'message' => 'Failed to create new revision data.'
                ]);
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('breakdown_prices', ["item_fg_id" => $data['item_fg_id'],"revision" => $data['revision'],"p_month" => $data['p_month'],"p_year" => $data['p_year']]);
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
                'p_month' => $data->val($i, 2),
                'p_year' => $data->val($i, 3),
                'revision' => $data->val($i, 4),
                'item_fg_number' => $data->val($i, 5),
                'model_name' => $data->val($i, 6),
                'order_estimation' => $data->val($i, 7),
                'model_life_time' => $data->val($i, 8),
                'start_mass_pro' => $data->val($i, 9),
                'l_t_dies_actual' => $data->val($i, 10),
                'quotation_date' => $data->val($i, 11),
                'price_cond' => $data->val($i, 12),
                'currency' => $data->val($i, 13),
                'mold_unit' => $data->val($i, 14),
                'dies_unit' => $data->val($i, 15),
                'dies_price' => $data->val($i, 16),
                'jig_unit' => $data->val($i, 17),
                'jig_price' => $data->val($i, 18),
                'tooling_unit' => $data->val($i, 19),
                'tooling_price' => $data->val($i, 20),
                'fixture_cost_unit' => $data->val($i, 21),
                'fixture_cost_price' => $data->val($i, 22)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/production_schedules.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/production_schedules.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/production_schedules.txt";
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
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);

    //         if (empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Item Finish Good " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
    //         } else {
    //             $dataFinal = array(
    //                 //field
    //                 "item_fg_id" => $item_fg->id,
    //                 "item_fg_number" => $item_fg->number,,
    //                 "item_fg_name" => $item_fg->name,
    //                 "model_name" => $data['model_name'],
    //                 "p_month" => $data['p_month'],
    //                 "p_year" => $data['p_year'],
    //                 "revision" => $data['revision'],
    //                 "order_estimation" => $data['order_estimation'],
    //                 "model_life_time" => $data['model_life_time'],
    //                 "start_mass_pro" => $data['start_mass_pro'],
    //                 "l_t_dies_actual" => $data['l_t_dies_actual'],
    //                 "supplier" => 'PT. BANSHU PLASTIC INDONESIA',
    //                 "quotation_date" => $data['quotation_date'],
    //                 "quotation_number" => $quotation_number,
    //                 "price_cond" => $data['price_cond'],
    //                 "currency" => $data['currency'],
    //                 "mold_unit" => $data['mold_unit'],
    //                 "dies_unit" => $data['dies_unit'],
    //                 "dies_price" => $data['dies_price'],
    //                 "jig_unit" => $data['jig_unit'],
    //                 "jig_price" => $data['jig_price'],
    //                 "tooling_unit" => $data['tooling_unit'],
    //                 "tooling_price" => $data['tooling_price'],
    //                 "fixture_cost_unit" => $data['fixture_cost_unit'],
    //                 "fixture_cost_price" => $data['fixture_cost_price'],
    //             );
    //             $send   = $this->crud->create('breakdown_prices', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            
            // Cek Process Number
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item Finish Good " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } else {

                $excel_date = $data['quotation_date']; 
                $time = strtotime($excel_date);
                
                if (!$time) {
                    $time = time(); 
                }

                $shortYear = date('y', $time);
                $monthRomawi = $this->get_romawi(date('n', $time));

                $this->db->select("MAX(CAST(SUBSTRING_INDEX(quotation_number, '/', 1) AS UNSIGNED)) as max_no"); 
                $this->db->like('quotation_number', "/BPI-MKT/QUOT/");
                $this->db->like('quotation_number', "/$shortYear", 'before'); 
                
                $query = $this->db->get('breakdown_prices');
                $row = $query->row();
                
                $next_val = ($row && $row->max_no) ? intval($row->max_no) + 1 : 1;
                $no_urut = sprintf('%03d', $next_val);
                
                $quotation_number = "$no_urut/BPI-MKT/QUOT/$monthRomawi/$shortYear";
                // ==========================================================

                $dataFinal = array(
                    "item_fg_id" => $item_fg->id,
                    "item_fg_number" => $item_fg->number,
                    "item_fg_name" => $item_fg->name,
                    "model_name" => $data['model_name'],
                    "p_month" => $data['p_month'],
                    "p_year" => $data['p_year'],
                    "revision" => $data['revision'],
                    "order_estimation" => $data['order_estimation'],
                    "model_life_time" => $data['model_life_time'],
                    "start_mass_pro" => $data['start_mass_pro'],
                    "l_t_dies_actual" => $data['l_t_dies_actual'],
                    "supplier" => 'PT. BANSHU PLASTIC INDONESIA',
                    "quotation_date" => $data['quotation_date'],
                    "quotation_number" => $quotation_number,
                    "price_cond" => $data['price_cond'],
                    "currency" => $data['currency'],
                    "mold_unit" => $data['mold_unit'],
                    "dies_unit" => $data['dies_unit'],
                    "dies_price" => $data['dies_price'],
                    "jig_unit" => $data['jig_unit'],
                    "jig_price" => $data['jig_price'],
                    "tooling_unit" => $data['tooling_unit'],
                    "tooling_price" => $data['tooling_price'],
                    "fixture_cost_unit" => $data['fixture_cost_unit'],
                    "fixture_cost_price" => $data['fixture_cost_price'],
                    "remarks" => 'Upload'
                );
                
                $send = $this->crud->create('breakdown_prices', $dataFinal);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=breakdown_prices_$format.xls");
        }

        $get = $this->input->get();

        $filter_period_month = $get['filter_period_month'];
        $filter_period_year = $get['filter_period_year'];
        $filter_from = $get['filter_from'];
        $filter_to = $get['filter_to'];
        $filter_item_fg_id = $get['filter_item_fg_id'];
        $filter_revision = $get['filter_revision'];
        $filter_customer_id = $get['filter_customer_id'];

        // $filter_operation = $this->input->get('filter_operation');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('breakdown_prices a');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.quotation_date >=', $filter_from);
            $this->db->where('a.quotation_date <=', $filter_to);
        }
        if ($filter_period_month != "") {
            $this->db->where('a.p_month', $filter_period_month);
        }

        if ($filter_period_year != "") {
            $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_customer_id != "") {
            $this->db->where('a.customer_id', $filter_customer_id);
        }

        // $this->db->group_by('a.p_month');
        // $this->db->group_by('a.p_year');
        // $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.revision');

        $records = $this->db->get()->result_array();
        
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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>BREAKDOWN PRICES</h3>
                </div>
            </center>
            
            <table id="customers" border="1">
            <tr>
                <th>No</th>
                <th>Product Number</th>
                <th>Product Name</th>
                <th>Customer Name</th>
                <th>Rev Cost Pattern</th>
                <th>Rev Breakdown Price</th>
                <th>Month Cost Pattern</th>
                <th>Year Cost Pattern</th>
                <th>Order Estimation</th>
                <th>Model Life Time</th>
                <th>Start Mass Pro</th>
                <th>L/T Dies Actual</th>
                <th>VENDOR/SUPPLIER/MAKER</th>
                <th>Quotation Date</th>
                <th>Quotation Number</th>
                <th>Price Cond</th>
                <th>Price Cond</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['revision'] . '</td>
                        <td>' . $data['revision_quotation_number'] . '</td>
                        <td>' . $data['p_month'] . '</td>
                        <td>' . $data['p_year'] . '</td>
                        <td>' . $data['order_estimation'] . '</td>
                        <td>' . $data['model_life_time'] . '</td>
                        <td>' . $data['start_mass_pro'] . '</td>
                        <td>' . $data['l_t_dies_actual'] . '</td>
                        <td>' . $data['supplier'] . '</td>
                        <td>' . $data['quotation_date'] . '</td>
                        <td>' . $data['quotation_number'] . '</td>
                        <td>' . $data['price_cond'] . '</td>
                        <td>' . $data['grand_total'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_breakdown($id)
    {
        $ids = base64_decode($id);

        // 1. Ambil data Header dari breakdown_prices
        $this->db->select('a.*');
        $this->db->from('breakdown_prices a');
        $this->db->where('a.id', $ids);
        $header = $this->db->get()->row();

        // var_dump($header);
        // return;

        if (!$header) {
            die("Data Breakdown tidak ditemukan.");
        }

        $breakdown_prices = $this->crud->reads('breakdown_prices', [], ["p_month" => $header->p_month, "p_year" => $header->p_year, "item_fg_id" => $header->item_fg_id, "revision" => $header->revision]);
        $breakdown_price  = $this->crud->read('breakdown_prices', [], ["p_month" => $header->p_month, "p_year" => $header->p_year, "item_fg_id" => $header->item_fg_id, "revision" => $header->revision]);

        if (!$breakdown_price) {
            die("Data tidak ditemukan.");
        }

        $approval = $this->crud->read('approvals', [], ["table_name" => "breakdown_prices"]);
        $user_0 = $this->crud->read('users', [], ["username" => $breakdown_price->created_by]); // Prepared
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]); // Checked
        $user_2 = (!empty($approval->user_approval_2)) ? $this->crud->read('users', [], ["username" => $approval->user_approval_2]) : (object) ["name" => ""]; // Approved

        $users_0 = $users_1 = $users_2 = '';
        
        if ($breakdown_price->approved >= 1) {
            $this->createQrcode($user_0->name, "assets/image/qrcode/");
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $user_0->name . '.png') . '" width="70"/>';
        }
        if ($breakdown_price->approved >= 2) {
            $this->createQrcode($user_1->name, "assets/image/qrcode/");
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="70"/>';
        }
        if ($breakdown_price->approved >= 3) {
            $this->createQrcode($user_2->name, "assets/image/qrcode/");
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="70"/>';
        }

        $config = $this->db->get('config')->row();
        $rows_per_page = 20;
        $total_pages = ceil(count($breakdown_prices) / $rows_per_page);

        $this->db->select("
            cp.*, 
            ifg.color as color_fg,
            ifg.weight,
            ifg.uom,
            
            COALESCE(f_vg.name, cp.part_name_vg) as name_vg, 
            ir_vg.color as color_vg,
            COALESCE(f_mb.name, cp.part_name_mb) as name_mb, 
            ir_mb.color as color_mb,
            ir_cp.color as color_cp,

            si_vg.maker as maker_vg,
            si_mb.maker as maker_mb,
            si_cp.maker as maker_cp,

            bn_vg.composition as comp_vg,
            bn_mb.composition as comp_mb,
            bn_cp.composition as comp_cp,

            ml.runner
        ");
        $this->db->from('cost_patterns cp');
        $this->db->join('item_fg ifg', 'ifg.id = cp.item_fg_id', 'left');

        /* JOIN LOADING (Runner & Cavity) */
        $this->db->join('menu_loadings ml', 'ml.item_fg_id = cp.item_fg_id', 'left');
        $this->db->join('molds mld', 'ml.mold_id = mld.id');

        /* --- JOIN VIRGIN (VG) --- */
        $this->db->join('item_rm ir_vg', 'ir_vg.id = cp.item_rm_id_vg', 'left');
        $this->db->join('item_family_subs f_vg', 'f_vg.id = ir_vg.item_sub_family_id', 'left');
        $this->db->join('bom bn_vg', 'bn_vg.item_fg_id = cp.item_fg_id AND bn_vg.item_rm_id = cp.item_rm_id_vg', 'left');
        // Subquery Supplier VG (Share 100)
        $sub_vg = "(SELECT item_rm_id, supplier_id, maker, price FROM supplier_items WHERE share_order = 100 GROUP BY item_rm_id) si_vg";
        $this->db->join($sub_vg, 'si_vg.item_rm_id = ir_vg.id', 'left');
        $this->db->join('suppliers s_vg', 's_vg.id = si_vg.supplier_id', 'left');

        /* --- JOIN MASTERBATCH (MB) --- */
        $this->db->join('item_rm ir_mb', 'ir_mb.id = cp.item_rm_id_mb', 'left');
        $this->db->join('item_family_subs f_mb', 'f_mb.id = ir_mb.item_sub_family_id', 'left');
        $this->db->join('bom bn_mb', 'bn_mb.item_fg_id = cp.item_fg_id AND bn_mb.item_rm_id = cp.item_rm_id_mb', 'left');
        // Subquery Supplier MB (Share 100)
        $sub_mb = "(SELECT item_rm_id, supplier_id, maker, price FROM supplier_items WHERE share_order = 100 GROUP BY item_rm_id) si_mb";
        $this->db->join($sub_mb, 'si_mb.item_rm_id = ir_mb.id', 'left');
        $this->db->join('suppliers s_mb', 's_mb.id = si_mb.supplier_id', 'left');

        /* --- JOIN CHILD PART (CP) --- */
        $this->db->join('item_rm ir_cp', 'ir_cp.id = cp.item_rm_id_cp', 'left');
        $this->db->join('bom bn_cp', 'bn_cp.item_fg_id = cp.item_fg_id AND bn_cp.item_rm_id = cp.item_rm_id_cp', 'left');
        // Subquery Supplier CP (Share 100)
        $sub_cp = "(SELECT item_rm_id, supplier_id, maker, price FROM supplier_items WHERE share_order = 100 GROUP BY item_rm_id) si_cp";
        $this->db->join($sub_cp, 'si_cp.item_rm_id = ir_cp.id', 'left');
        $this->db->join('suppliers s_cp', 's_cp.id = si_cp.supplier_id', 'left');

        $this->db->where([
            'cp.p_month'    => $header->p_month,
            'cp.p_year'     => $header->p_year,
            'cp.item_fg_id' => $header->item_fg_id,
            'cp.customer_id'=> $header->customer_id,
            'cp.revision'   => $header->revision
        ]);

        $this->db->group_by('cp.p_month');
        $this->db->group_by('cp.p_year');
        $this->db->group_by('cp.item_fg_id');
        $this->db->group_by('cp.customer_id');
        $this->db->group_by('cp.revision');
        

        $details = $this->db->get()->result();

        $this->db->select('cp.*');
        $this->db->from('cost_patterns cp');
        $this->db->where([
            'cp.p_month'    => (int) $header->p_month,
            'cp.p_year'     => (int) $header->p_year,
            'cp.item_fg_id' => $header->item_fg_id,
            'cp.customer_id'=> $header->customer_id,
            'cp.revision'   => $header->revision
        ]);
        $details2 = $this->db->get()->row();
        // die($this->db->last_query());

        // 3. Generate HTML
        $html = '
        <html>
        <head>
            <title>Breakdown Price - '.$header->quotation_number.' REV 0'.$header->revision_quotation_number.'</title>
            <style>
                body { font-family: Calibri, sans-serif; font-size: 11px; }
                .container { width: 210mm; padding: 10mm; margin: auto; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
                .border td, .border th { border: 1px solid black; padding: 4px; }
                /* Warna dihilangkan, hanya menyisakan format teks jika diperlukan */
                .bold { font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .title { font-size: 18px; font-weight: bold; text-align: center; text-decoration: underline; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <br><br><br><br><br><br><br><br><br><br>
                <div class="title">BREAKDOWN PRICE</div>

                <table>
                    <tr>
                        <td width="40%">CUSTOMER NAME :</td>
                        <td width="40%">VENDOR/SUPPLIER/MAKER NAME :</td>
                    </tr>
                </table>

                <table class="border">
                    <tr>
                        <td colspan="2" style="border: 1px solid black; font-weight:bold; height: 50px; text-align: center; vertical-align: middle;">
                            '.$details2->customer_name.'
                        </td>
                        <td colspan="2" style="border: 1px solid black; font-weight:bold; height: 50px; text-align: center; vertical-align: middle;">
                            '.$header->supplier.'
                        </td>
                    </tr>
                    <tr>
                        <td width="15%" class="bg-blue">Model Name</td><td width="35%">'.$header->model_name.'</td>
                        <td width="15%" class="bg-blue">Quotation Number</td><td class="bg-blue">'.$header->quotation_number.' REV 0'.$header->revision_quotation_number.'</td>
                    </tr>
                    <tr>
                        <td class="bg-blue">Part Number</td><td>'.$header->item_fg_number.'</td>
                        <td class="bg-blue">Quotation date</td><td>'.date("d-M-y", strtotime($header->quotation_date)).'</td>
                    </tr>
                    <tr>
                        <td class="bg-blue">Part Name</td><td>'.$header->item_fg_name.'</td>
                        <td colspan="2" rowspan="4" valign="top">
                            <table width="100%" class="border" style="margin-top:2px; text-align:center;">
                                <tr>
                                    <th width="33%">Approved</th>
                                    <th width="33%">Checked</th>
                                    <th width="33%">Prepared</th>
                                </tr>
                                <tr height="50">
                                    <td>' . $users_2 . '</td>
                                    <td>' . $users_1 . '</td>
                                    <td>' . $users_0 . '</td>
                                </tr>
                                <tr style="font-size:9px;">
                                    <td>' . ($user_2->name ?: '-') . '</td>
                                    <td>' . ($user_1->name ?: '-') . '</td>
                                    <td>' . ($user_0->name ?: '-') . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-blue">Order Estimation</td>
                        <td>'.number_format($header->order_estimation).' pcs/bulan</td>
                    </tr>
                    <tr>
                        <td class="bg-blue">Model Life Time</td>
                        <td>'.$header->model_life_time.'</td>
                    </tr>
                    <tr>
                        <td class="bg-blue">Start Mass Pro</td>
                        <td>'.$header->start_mass_pro.'</td>
                    </tr>
                </table>

                <table style="margin-top:5px;">
                    <tr>
                        <td>Price Cond.: <span style="border-bottom: 1px solid black; padding: 0 10px;">'.$header->price_cond.'</span></td>
                        <td class="text-right">Rate : USD 1 </td>
                    </tr>
                </table>

                <div style="margin-top:10px; overflow: hidden; width: 100%;">
                    <div style="float: left; font-weight: bold;">MATERIAL & PARTS COST</div>
                    <div style="float: right; font-weight: bold; font-size: 11px;">PRICE UNIT IN : '.$header->currency.'</div>
                </div>
                <table class="border text-center">
                    <tr class="bg-gray">
                        <th rowspan="2">NAME / SPECIFICATION</th>
                        <th rowspan="2">MAT\'L MAKER</th>
                        <th rowspan="2">COLOUR</th>
                        <th colspan="2">WEIGHT</th>
                        <th rowspan="2">UNIT</th>
                        <th rowspan="2">PRICE/ Kg</th>
                        <th rowspan="2">COST</th>
                    </tr>
                    <tr class="bg-gray">
                        <th>GROSS</th>
                        <th>NET</th>
                    </tr>';
                    // cek perhitungan net
                    $sub_total_1 = 0;
                    foreach($details as $d) {
                        // Hitung Runner per Pcs (dibagi cavity)
                        $runner = (float)$d->runner;
                        $cavity = (float)$d->cavity_standard > 0 ? (float)$d->cavity_standard : 1;
                        $runner_per_pcs = $runner / $cavity;

                        if($header->show_maker == 'YES'){
                            $maker = $d->maker_vg;
                        }else{
                            $maker = '-';
                        }

                        // --- 1. BLOK VIRGIN (VG) ---
                        if(!empty($d->item_rm_id_vg)) {
                            // $gross_vg = (float)$d->comp_vg * 1000; // Mengambil dari BOM
                            // $net_vg   = $gross_vg - $runner_per_pcs; // Rumus: Gross - (Runner/Cavity)
                            // $net_vg   = (float)$d->nett_vg;
                            // $cost_vg  = ($gross_vg * (float)$d->virgin_cost) / 1000; //perlu di tanyakn lagi
                            $gross_vg = (float)$details2->used_vg * 1000; // ambil dari cost pattern req Bu Septi 2026-07-15
                            $nett_vg = (float)$details2->nett_vg * 1000; // ambil dari cost pattern req Bu Septi 2026-07-15
                            $sub_total_1 += (float)$d->virgin_cost;

                            $html .= '<tr>
                                <td align="left">'.$d->name_vg.'</td> 
                                <td>'.($maker).'</td> 
                                <td>'.$d->color_vg.'</td>
                                <td align="right">'.number_format($gross_vg, 3).'</td> 
                                <td align="right">'.number_format($nett_vg, 3).'</td>   
                                <td align="center">gr</td>
                                <td align="right">'.number_format($header->price_vg, 2).'</td>
                                <td align="right">'.number_format($d->virgin_cost, 2).'</td>
                            </tr>';
                        }

                        // --- 2. BLOK MASTERBATCH (MB) ---
                        if(!empty($d->item_rm_id_mb)) {
                            $gross_mb = (float)$d->comp_mb * 1000; // Mengambil dari BOM
                            $net_mb   = $gross_mb;// penyesuaian 2026-05-12
                            // $cost_mb  = ($gross_mb * (float)$d->mb_cost) / 1000;
                            $sub_total_1 += (float)$d->mb_cost;

                            $html .= '<tr>
                                <td align="left">'.$d->name_mb.'</td>
                                <td>'.($d->maker_mb ?: '-').'</td>
                                <td>'.$d->color_mb.'</td>
                                <td align="right">'.number_format($gross_mb, 3).'</td>
                                <td align="right">'.number_format($d->weight, 3).'</td>
                                <td align="center">gr</td>
                                <td align="right">'.number_format($d->price_mb, 2).'</td>
                                <td align="right">'.number_format($d->mb_cost, 2).'</td>
                            </tr>';
                        }

                        // --- 3. BLOK CHILD PART (CP) ---
                        if(!empty($d->item_rm_id_cp)) {
                            $gross_cp = (float)$d->comp_cp; // Mengambil dari BOM
                            $net_cp   = $gross_cp; // penyesuaian 2026-05-12
                            // $cost_cp  = ($gross_cp * (float)$d->child_part_cost); // Tanpa dibagi 1000 jika per pcs
                            $sub_total_1 += (float)$d->child_part_cost;

                            $html .= '<tr>
                                <td align="left">'.$d->part_no_cp.'</td>
                                <td>'.($d->maker_cp ?: '-').'</td>
                                <td>'.$d->color_cp.'</td>
                                <td align="right">'.number_format($gross_cp, 3).'</td>
                                <td align="right">'.number_format($d->weight, 3).'</td>
                                <td align="center">'.($d->maker_cp ?: '-').'</td>
                                <td align="right">'.number_format($d->price_cp, 2).'</td>
                                <td align="right">'.number_format($d->child_part_cost, 2).'</td>
                            </tr>';
                        }
                    }

                    $process = !empty($details) ? $details[0] : null;

                    $sub_total_2 = 0;
                    $sub_total_3 = 0;
                    if ($process) {
                        // Hitung Injection Cost (Formula: (Cycle Time / Cavity) * Plain Rate)
                        $cav_std = ($header->cavity_standard > 0) ? $header->cavity_standard : 1;
                        $inj_cost = ($header->cycle_time / $cav_std) * $header->plain_rate_sec;
                        
                        $sec_process_cost = $header->cycle_time_process * $header->labour_cost;
                        $sub_total_2 = $inj_cost + $sec_process_cost;

                        $is_depreciation = ($process->depreciation == 'YES');

                        $volume = (float)$process->volume > 0 ? (float)$process->volume : 1;

                        if($header->cycle_time_process = 0 || $header->labour_cost = 0){
                            $tonage = $header->toonage;
                            $cavity_standard = $header->cavity_standard;
                        }else{
                            $tonage = 0;
                            $cavity_standard = 0;
                        }

                        // --- 1. Logika MOLD ---
                        $mold_name = ($is_depreciation) ? $process->mold_name : 'Mold';
                        $mold_unit = ($is_depreciation) ? $header->mold_unit : 1; 
                        $mold_price = ($is_depreciation) ? (float)$process->mold_price : 0;
                        $mold_amount = $mold_unit * $mold_price;
                        $mold_amortization = $process->mold_depreciation;

                        // --- 2. Logika DIES ---
                        $dies_unit = $is_depreciation ? (float)($header->dies_unit ?? 1) : 1;
                        $dies_price = $is_depreciation ? (float)($header->dies_price ?? 0) : 0;
                        $dies_amount = $dies_unit * $dies_price;
                        $dies_amortization = $dies_price;

                        // --- 3. Logika JIG ---
                        $jig_unit = $is_depreciation ? (float)($header->jig_unit ?? 1) : 1;
                        $jig_price = $is_depreciation ? (float)($header->jig_price ?? 0) : 0;
                        $jig_amount = $jig_unit * $jig_price;
                        $jig_amortization = $jig_price;

                        // --- 4. Logika TOOLING ---
                        $tooling_unit = $is_depreciation ? (float)($header->tooling_unit ?? 1) : 1;
                        $tooling_price = $is_depreciation ? (float)($header->tooling_price ?? 0) : 0;
                        $tooling_amount = $tooling_unit * $tooling_price;
                        $tooling_amortization = $tooling_price;

                        // --- 5. Logika FIXTURE ---
                        $fixture_cost_unit = $is_depreciation ? (float)($header->fixture_cost_unit ?? 1) : 1;
                        $fixture_cost_price = $is_depreciation ? (float)($header->fixture_cost_price ?? 0) : 0;
                        $fixture_amount = $fixture_cost_unit * $fixture_cost_price;
                        $fixture_amortization = $fixture_cost_price;

                        // Hitung Sub Total 3
                        $sub_total_3 = $mold_amortization + $dies_amortization + $jig_amortization + $tooling_amortization + $fixture_amortization;


                        $total_cost = $sub_total_1 
                                    + $sub_total_2 
                                    + $sub_total_3 
                                    + (float)($header->adm_foh_cost ?? 0)
                                    + (float)($header->ng_ratio_cost ?? 0)
                                    + (float)($header->mtn_cost ?? 0)
                                    + (float)($header->total_packing_cost ?? 0)
                                    + (float)($header->transportasion_cost_pcs ?? 0)
                                    + (float)($header->purging_cost ?? 0)
                                    + (float)($header->profit_nominal ?? 0);

                        $selling_price = round($total_cost);

                    }

        $html .= '
                    <tr class="bg-gray"><td colspan="7" class="text-right">Sub. Total 1</td><td class="text-right">'.number_format($sub_total_1, 2).'</td></tr>
                </table>

                <div style="font-weight:bold; margin-top:5px;">PROCESS COST</div>
                <table class="border text-center">
                    <tr class="bg-gray">
                        <th>PROCESS</th>
                        <th>M/C (TON)</th>
                        <th>CAVITY</th>
                        <th>CYCLE TIME</th>
                        <th>CHARGE RATE</th>
                        <th>COST</th>
                    </tr>
                    
                    <tr>
                        <td align="left">INJECTION</td>
                        <td>'.number_format($header->toonage).' T</td>
                        <td>'.number_format($header->cavity_standard).'</td>
                        <td>'.number_format($header->cycle_time, 2).' sec</td>
                        <td class="text-right">'.number_format($header->plain_rate_sec, 4).'</td>
                        <td class="text-right">'.number_format($inj_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td align="left" class="bg-blue">SECOND PROCESS</td>
                        <td>'.number_format($tonage).' T</td>
                        <td>'.number_format($cavity_standard).'</td>
                        <td>'.number_format($header->cycle_time_process, 2).' sec</td>
                        <td class="text-right">'.number_format($header->labour_cost, 4).'</td>
                        <td class="text-right">'.number_format($sec_process_cost, 2).'</td>
                    </tr>
                    <tr class="bg-gray">
                        <td colspan="5" class="text-right">Sub. Total 2</td>
                        <td class="text-right">'.number_format($sub_total_2, 2).'</td>
                    </tr>
                </table>

                <div style="font-weight:bold; margin-top:5px;">DIES/JIG/TOOLING/FIXTURE COST</div>
                <table class="border text-center">
                    <tr class="bg-gray">
                        <th rowspan="2">NAME</th>
                        <th rowspan="2">MAT\'L SPEC</th>
                        <th rowspan="2">UNIT</th>
                        <th rowspan="2">PRICE/UNIT</th>
                        <th rowspan="2">AMOUNT</th>
                        <th colspan="2">AMORTIZATION</th>
                    </tr>
                    <tr class="bg-gray">
                        <th>VOLUME</th>
                        <th>COST</th>
                    </tr>

                    <tr>
                        <td align="left">'.$mold_name.'</td>
                        <td>-</td>
                        <td>'.number_format($mold_unit).'</td>
                        <td align="right">'.number_format($mold_price, 2).'</td>
                        <td align="right">'.number_format($mold_amount, 2).'</td>
                        <td align="right">'.number_format($volume).'</td>
                        <td align="right">'.number_format($mold_amortization, 2).'</td>
                    </tr>
                    <tr>
                        <td align="left">Dies</td>
                        <td>-</td>
                        <td>'.number_format($dies_unit).'</td>
                        <td align="right">'.number_format($dies_price, 2).'</td>
                        <td align="right">'.number_format($dies_amount, 2).'</td>
                        <td align="right">'.number_format($volume).'</td>
                        <td align="right">'.number_format($dies_amortization, 2).'</td>
                    </tr>
                    <tr>
                        <td align="left">Jig</td>
                        <td>-</td>
                        <td>'.number_format($jig_unit).'</td>
                        <td align="right">'.number_format($jig_price, 2).'</td>
                        <td align="right">'.number_format($jig_amount, 2).'</td>
                        <td align="right">'.number_format($volume).'</td>
                        <td align="right">'.number_format($jig_amortization, 2).'</td>
                    </tr>
                    <tr>
                        <td align="left">Tooling</td>
                        <td>-</td>
                        <td>'.number_format($tooling_unit).'</td>
                        <td align="right">'.number_format($tooling_price, 2).'</td>
                        <td align="right">'.number_format($tooling_amount, 2).'</td>
                        <td align="right">'.number_format($volume).'</td>
                        <td align="right">'.number_format($tooling_amortization, 2).'</td>
                    </tr>
                    <tr>
                        <td align="left">Fixture Cost</td>
                        <td>-</td>
                        <td>'.number_format($fixture_cost_unit).'</td>
                        <td align="right">'.number_format($fixture_cost_price, 2).'</td>
                        <td align="right">'.number_format($fixture_amount, 2).'</td>
                        <td align="right">'.number_format($volume).'</td>
                        <td align="right">'.number_format($fixture_amortization, 2).'</td>
                    </tr>
                    <tr class="bg-gray">
                        <td colspan="6" class="text-right">Sub. Total 3</td>
                        <td class="text-right">'.number_format($sub_total_3, 2).'</td>
                    </tr>
                </table>

                <table class="border" style="margin-top:10px;">
                    <tr class="bg-gray">
                        <th colspan="2" align="left">COST SUMMARY</th>
                        <th width="30%">FORMULA</th>
                        <th width="15%">COST</th>
                    </tr>
                    <tr>
                        <td width="5%">1.</td>
                        <td>Material & parts</td>
                        <td class="text-center">Sub total 1</td>
                        <td class="text-right">'.number_format($sub_total_1, 2).'</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Process</td>
                        <td class="text-center">Sub total 2</td>
                        <td class="text-right">'.number_format($sub_total_2, 2).'</td>
                    </tr>
                    <tr class="bg-gray">
                        <td colspan="2" class="text-right">Sub. Total Manufacturing Cost</td>
                        <td class="text-center">1 + 2</td>
                        <td class="text-right">'.number_format($sub_total_1 + $sub_total_2, 2).'</td>
                    </tr>
                    <tr>
                        <td>3.</td><td>Dies/Jig/Tools/Fixture</td>
                        <td class="text-center">Sub Total 3</td>
                        <td class="text-right">'.number_format($sub_total_3, 2).'</td>
                    </tr>
                    <tr>
                        <td>4.</td>
                        <td>FOH and Administration</td>
                        <td class="text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->adm_foh_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td>5.</td>
                        <td>NG</td>
                        <td class=" text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->ng_ratio_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td>6.</td>
                        <td>Maintenance</td>
                        <td class=" text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->mtn_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td>7.</td>
                        <td>Packaging</td>
                        <td class=" text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->total_packing_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td>8.</td>
                        <td>Transport</td>
                        <td class=" text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->transportasion_cost_pcs, 2).'</td>
                    </tr>
                    <tr>
                        <td>9.</td>
                        <td>Purging</td>
                        <td class=" text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->purging_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td>10.</td>
                        <td>Profit</td>
                        <td class="text-center" style="font-size:9px;">-</td>
                        <td class="text-right">'.number_format($header->profit_nominal, 2).'</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">Total Cost</td>
                        <td class="text-right">'.number_format($total_cost, 2).'</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right" style="font-size:20px; font-weight:bold;">Selling Price</td>
                        <td class="text-right">'.number_format($selling_price, 2).'</td>
                    </tr>
                </table>
            </div>
            <script>window.print();</script>
        </body>
        </html>';

        echo $html;
    }
}