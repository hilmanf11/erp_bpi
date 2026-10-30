<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_types extends CI_Controller
{
    // Module Journal Posting biasa (journal_postings), dipakai journal type dengan Journal Inventory = NO
    public $finance_modules = ['SALES INVOICING', 'PURCHASE INVOICING', 'AP PAYMENT', 'AR RECEIPT', 'ASSET'];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('autopostingjournal');

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['finance_modules']   = $this->finance_modules;
            $data['inventory_modules'] = $this->inventory_modules();
            $data['divisions']         = $this->divisions();
            $this->load->view('template/header', $data);
            $this->load->view('finance/journal_types');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($module = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('*');
        $this->db->from('journal_types');
        if (!empty($post)) {
            $this->db->like('name', $post);
        }
        if (!empty($module)) {
            $mod = base64_decode($module);
            if ($mod === false || !mb_check_encoding($mod, 'UTF-8') || preg_match('/[^\x20-\x7E]/', $mod)) {
                $mod = $module;
            }
            $this->db->group_start();
            $this->db->like('module', $mod);
            $this->db->or_like('module', $module);
            $this->db->group_end();
            // Journal Posting biasa tidak memakai journal type inventory (dan sebaliknya)
            $this->db->where('is_journal_inventory', in_array($mod, $this->inventory_modules()) ? 1 : 0);
        }
        $this->db->order_by('name', 'asc');
        $send = $this->db->get()->result();
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules') ?? '[]');
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.account_name');
            $this->db->from('journal_types a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if ($filter->field == "account_number") {
                        $this->db->like("a.account_number", $filter->value);
                    } else {
                        $this->db->like($filter->field, $filter->value);
                    }
                }
            }
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

    /**
     * Module Journal Posting Inventory (journal_inventory), dipakai journal type dengan Journal Inventory = YES.
     * Diambil dari Autopostingjournal::$modules, kecuali module yang memakai journal type module lain (BPM -> TRANSACTION RM)
     */
    public function inventory_modules()
    {
        $modules = [];
        foreach ($this->autopostingjournal->modules as $name => $config) {
            if (!isset($config['setup'])) $modules[] = $name;
        }
        return $modules;
    }

    // Division item RM (INJ / MTS / ...) untuk memilih journal type inventory per item
    public function divisions()
    {
        $rows = $this->db->query("SELECT DISTINCT TRIM(division) AS division FROM item_rm WHERE TRIM(COALESCE(division, '')) <> '' ORDER BY 1")->result();
        return array_column($rows, 'division');
    }

    private function _validate_code($post, $id = null)
    {
        $code = trim($post['number'] ?? '');
        if (empty($code)) return "Journal Code is required.";

        $this->db->select('number, name');
        $this->db->from('journal_types');
        $this->db->where('deleted', 0);
        $this->db->where('number', $code);
        if ($id) $this->db->where('id !=', $id);
        $dup = $this->db->get()->row();

        return $dup ? "Journal Code '$code' already exists: {$dup->name}." : null;
    }

    /**
     * Journal type inventory tidak boleh dobel untuk kombinasi module + transaction type + division yang sama
     * (auto posting butuh tepat 1 journal type per kombinasi)
     * @return string|null Pesan error, null jika valid
     */
    private function _validate_duplicate($post, $id = null)
    {
        if ($post['is_journal_inventory'] != 1) return null;

        foreach (array_filter(array_map('trim', explode(',', $post['module']))) as $module) {
            $this->db->select('number, name');
            $this->db->from('journal_types');
            $this->db->where('deleted', 0);
            $this->db->where('is_journal_inventory', 1);
            $this->db->where("FIND_IN_SET(" . $this->db->escape($module) . ", REPLACE(module, ', ', ',')) > 0", NULL, FALSE);
            $this->db->where("COALESCE(transaction_type, '') = " . $this->db->escape((string) $post['transaction_type']), NULL, FALSE);
            $this->db->where("COALESCE(division, '') = " . $this->db->escape((string) $post['division']), NULL, FALSE);
            if ($id) $this->db->where('id !=', $id);
            $dup = $this->db->get()->row();
            if ($dup) {
                return "Journal Type inventory for module $module"
                    . ($post['transaction_type'] ? " ({$post['transaction_type']})" : "")
                    . ($post['division'] ? " division {$post['division']}" : " (all division)")
                    . " already exists: {$dup->number} {$dup->name}.";
            }
        }
        return null;
    }

    /**
     * Journal Posting biasa dan Journal Posting Inventory dipisah:
     * Journal Inventory = YES hanya boleh module inventory, NO hanya module finance
     * @return string|null Pesan error, null jika valid
     */
    private function _validate_modules($post)
    {
        $modules = array_filter(array_map('trim', explode(',', $post['module'] ?? '')));
        if (empty($modules)) return "Module is required.";

        $allowed = $post['is_journal_inventory'] == 1 ? $this->inventory_modules() : $this->finance_modules;
        $invalid = array_diff($modules, $allowed);
        if (!empty($invalid)) {
            return ($post['is_journal_inventory'] == 1 ? "Journal Inventory = YES" : "Journal Inventory = NO")
                . " can only use module: " . implode(', ', $allowed) . ". Invalid: " . implode(', ', $invalid);
        }
        return null;
    }

    private function _prepare_post($post)
    {
        if (isset($post['module']) && is_array($post['module'])) {
            $post['module'] = implode(',', $post['module']);
        }
        // Inventory Process hanya diisi jika journal type termasuk jurnal inventory
        $post['is_journal_inventory'] = (isset($post['is_journal_inventory']) && $post['is_journal_inventory'] == '1') ? 1 : 0;
        $post['inventory_process'] = ($post['is_journal_inventory'] == 1 && !empty($post['inventory_process'])) ? $post['inventory_process'] : null;
        // Transaction Type: jenis transaksi dalam 1 module (misal ADJ IN STO di TRANSACTION RM)
        $post['transaction_type'] = ($post['is_journal_inventory'] == 1 && !empty($post['transaction_type'])) ? $post['transaction_type'] : null;
        // Division: journal type inventory khusus division item (INJ / MTS), kosong = semua division
        $post['division'] = ($post['is_journal_inventory'] == 1 && !empty($post['division'])) ? trim($post['division']) : null;
        if ($post['is_journal_inventory'] == 1) {
            // Journal type inventory: Type = Inventory, akun DEBIT/CREDIT hanya dari Journal Setups
            $post['type']           = 'Inventory';
            $post['account_number'] = null;
            $post['status']         = null;
        }
        return $post;
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->_prepare_post($this->input->post());
                if ($error = $this->_validate_code($post) ?: $this->_validate_modules($post) ?: $this->_validate_duplicate($post)) {
                    echo json_encode(['title' => 'Error', 'message' => $error, 'theme' => 'error']);
                    return;
                }
                $send = $this->crud->create('journal_types', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $id   = base64_decode((string) $this->input->get('id'));
                $post = $this->_prepare_post($this->input->post());
                if ($error = $this->_validate_code($post, $id) ?: $this->_validate_modules($post) ?: $this->_validate_duplicate($post, $id)) {
                    echo json_encode(['title' => 'Error', 'message' => $error, 'theme' => 'error']);
                    return;
                }
                $send = $this->crud->update('journal_types', ["id" => $id], $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('journal_types', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=journal_types_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.account_name');
        $this->db->from('journal_types a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
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
                            <small>MASTER JOURNAL TYPES</small>
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
                <th>Journal Code</th>
                <th>Journal Name</th>
                <th>Type</th>
                <th>Module</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Status</th>
                <th>Journal Inventory</th>
                <th>Inventory Process</th>
                <th>Transaction Type</th>
                <th>Division</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $j_inv = ($data['is_journal_inventory'] == 1) ? 'YES' : 'NO';
            $inv_proc = !empty($data['inventory_process']) ? $data['inventory_process'] : '-';
            $trans_type = !empty($data['transaction_type']) ? $data['transaction_type'] : '-';
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['module'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['status'] . '</td>
                    <td>' . $j_inv . '</td>
                    <td>' . $inv_proc . '</td>
                    <td>' . $trans_type . '</td>
                    <td>' . (!empty($data['division']) ? $data['division'] : '-') . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
