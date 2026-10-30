<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auto Posting Journal Inventory
 *
 * Akun debit/kredit diambil dari Journal Types (module + is_journal_inventory = 1)
 * dan Journal Setups (DEBIT/CREDIT), tidak lagi hardcode.
 * Dokumentasi: context/auto_posting_journal_inventory.md
 */
class Autopostingjournal extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud');
    }

    /**
     * Module journal inventory (1 module = 1 menu)
     * - company_required: Journal Inventory manual wajib pilih company (supplier) untuk cari Document No
     * - process: fungsi build per module
     * - setup: [module, transaction_type] Journal Type yang dipakai, jika tidak punya Journal Type sendiri
     */
    public $modules = [
        'PURCHASE ORDER RECEIPT' => ['company_required' => 1, 'process' => '_build_por'],
        // BPM menu (tabel bpm) = INJ. BPM untuk MTS lewat Transaction RM (transaction type BPM)
        'BPM'                    => ['company_required' => 0, 'process' => '_build_bpm'],
        'TRANSACTION RM'         => ['company_required' => 0, 'process' => '_build_transaction_rm'],
        'SUPPLY SHEET'           => ['company_required' => 0, 'process' => '_build_supply_sheets'],
        'NON SUPPLY SHEET'       => ['company_required' => 0, 'process' => '_build_non_supply_sheets'],
        'MATERIAL REQUESTION'    => ['company_required' => 0, 'process' => '_build_material_requestion'],
        'WIP ADJUSTMENT FG'      => ['company_required' => 0, 'process' => '_build_wip_adjustment_fg'],
        'OUTPUT PRODUCTION'      => ['company_required' => 0, 'process' => '_build_output_production'],
        'ITEM NG'                => ['company_required' => 0, 'process' => '_build_item_ng'],
        'WIP RECEIPT'            => ['company_required' => 0, 'process' => '_build_wip_receipt'],
        'DELIVERY NOTE'          => ['company_required' => 0, 'process' => '_build_delivery_note'],
        'TRANSACTION FG'         => ['company_required' => 0, 'process' => '_build_transaction_fg'],
    ];

    // Nama modul lama (jenis transaksi) diarahkan ke module menu-nya
    private $module_aliases = [
        'ADJ IN STO'      => 'TRANSACTION RM',
        'ADJ OUT STO'     => 'TRANSACTION RM',
        'IN SUBCONT JASA' => 'WIP ADJUSTMENT FG',
    ];

    public function normalize_module($modul)
    {
        return $this->module_aliases[$modul] ?? $modul;
    }

    /**
     * Module auto posting yang memakai Journal Type dengan module + transaction_type ini
     * (misal TRANSACTION RM + BPM dipakai module TRANSACTION RM dan BPM)
     * @return string[]
     */
    public function modules_of_journal_type($module, $transaction_type = null)
    {
        $result = [];
        foreach ($this->modules as $name => $config) {
            if (isset($config['setup'])) {
                if ($config['setup'][0] == $module && $config['setup'][1] == $transaction_type) $result[] = $name;
            } elseif ($name == $module) {
                $result[] = $name;
            }
        }
        return $result;
    }

    // Lock MySQL (GET_LOCK) untuk semua posting: cek duplikat + nomor voucher/ID tidak bentrok saat posting bersamaan
    const POSTING_LOCK = 'journal_inventory_posting';
    const LOCK_TIMEOUT = 10;

    // Batas percobaan repost otomatis (cron). Setelah itu hanya repost manual
    const MAX_AUTO_ATTEMPTS = 5;

    // Hanya POR kategori RM yang dijurnal (POR kategori lain tidak di-scan, jurnalnya lewat Purchase Invoicing)
    const POR_CATEGORY = 'RM';

    // Dokumen tanpa qty issued/transaksi (misal SH di-close tanpa issue, atau yang di-issue hanya item CR):
    // tidak ada jurnal, dicatat COMPLETE di queue (bukan FAILED)
    const ERR_NO_QTY = 4401;
    const QUEUE_COMPLETE = 'COMPLETE';

    // Kondisi SQL: POR dengan receipt_no = $receipt_col berisi item selain kategori RM
    public function non_rm_por_sql($receipt_col)
    {
        return "EXISTS (SELECT 1 FROM purchase_order_receipts r_rm
            JOIN item_rm i_rm ON r_rm.item_rm_id = i_rm.id
            LEFT JOIN item_categories c_rm ON i_rm.item_category_id = c_rm.id
            WHERE r_rm.receipt_no = $receipt_col AND r_rm.deleted = 0 AND COALESCE(c_rm.number, '') <> '" . self::POR_CATEGORY . "')";
    }

    // Kondisi SQL journal_inventory_queue: POR non-RM tidak ditampilkan / di-repost (gagal posting lama sebelum aturan RM saja)
    public function queue_visible_sql()
    {
        return "NOT (journal_inventory_queue.modul = 'PURCHASE ORDER RECEIPT' AND " . $this->non_rm_por_sql('journal_inventory_queue.document_no') . ")";
    }

    /**
     * Auto Posting Journal Inventory
     * Per dokumen all-or-nothing. Gagal = tidak ada baris jurnal + dicatat di journal_inventory_queue (FAILED) untuk repost.
     * @param string $modul Modul Name
     * @param string $document_no Document Number (Receipt No, Request No, etc)
     * @param string $source SCAN / MANUAL / REPOST / CRON (dicatat di queue)
     * @return array ['status' => bool, 'message' => string, 'number' => voucher (jika sukses)]
     */
    public function inventory($modul, $document_no, $source = 'MANUAL')
    {
        if (empty($modul) || empty($document_no)) {
            return ['status' => false, 'message' => 'Module or Document No is missing.'];
        }

        $modul = $this->normalize_module($modul);
        if (!isset($this->modules[$modul])) {
            log_message('error', "AutoPosting: Module $modul not found.");
            return ['status' => false, 'message' => "Failed to Auto Journal: Module $modul is not found."];
        }

        // POR non-RM / label belum lengkap: belum (atau tidak) dijurnal, tidak dicatat sebagai gagal posting
        if ($modul == 'PURCHASE ORDER RECEIPT') {
            $ready = $this->is_por_ready($document_no);
            if (!$ready['ready']) {
                return ['status' => false, 'message' => $ready['message']];
            }
        }

        if (!$this->_lock()) {
            $result = ['status' => false, 'message' => "Error: Another journal posting is in progress, please try again."];
            $this->_queue_result($modul, $document_no, $result, $source);
            return $result;
        }

        // db_debug dimatikan sementara: error DB jadi Exception (masuk queue), bukan halaman error yang menghentikan request scan
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            // Cek duplikat di dalam lock
            if ($this->check_duplicate_entry($modul, $document_no)) {
                log_message('debug', "$document_no for module $modul has been posted before. Skip.");
                $this->queue_mark_success($modul, $document_no);
                return [
                    'status'  => false,
                    'message' => "Skip: Document $document_no has already been posted to Journal."
                ];
            }

            $number = null;
            $result = $this->_run_transaction(function () use ($modul, $document_no, &$number) {
                $entry  = $this->{$this->modules[$modul]['process']}($modul, $document_no);
                $rows   = $this->_compose_rows($entry);
                $this->_assert_balanced($rows);
                $number = $this->_insert_rows($modul, $rows);
            });
            if ($result['status']) {
                $result['number']  = $number;
                $result['message'] = "Success: Document $document_no posted to Journal $number.";
            } elseif (!empty($result['complete'])) {
                $result['message'] = "Complete (tidak dijurnal, qty 0): " . $result['message'];
            }

            $this->_queue_result($modul, $document_no, $result, $source);
            return $result;

        } finally {
            $this->db->db_debug = $db_debug;
            $this->_unlock();
        }
    }

    private function _lock()
    {
        $row = $this->db->query("SELECT GET_LOCK(?, ?) AS l", [self::POSTING_LOCK, self::LOCK_TIMEOUT])->row();
        return $row && $row->l == 1;
    }

    private function _unlock()
    {
        $this->db->query("SELECT RELEASE_LOCK(?)", [self::POSTING_LOCK]);
    }


    /** -------------------------------------------
     * Queue posting (journal_inventory_queue): catatan dokumen yang gagal diposting untuk repost
     ---------------------------------------------- */

    /**
     * Catat hasil posting. Sukses: status SUCCESS + nomor voucher. Gagal: status FAILED + pesan error.
     * Error saat menulis queue hanya di-log supaya tidak mengubah hasil posting.
     */
    private function _queue_result($modul, $document_no, $result, $source)
    {
        try {
            $now  = date('Y-m-d H:i:s');
            $user = !empty($this->session->username) ? $this->session->username : null;
            $complete = !empty($result['complete']);
            $data = [
                'status'            => $result['status'] ? 'SUCCESS' : ($complete ? self::QUEUE_COMPLETE : 'FAILED'),
                'last_error'        => $result['status'] ? null : $result['message'],
                'last_source'       => $source,
                'journal_number'    => $result['number'] ?? null,
                'last_attempt_date' => $now,
                'last_attempt_by'   => $user,
            ];

            $queue = $this->db->get_where('journal_inventory_queue', ['modul' => $modul, 'document_no' => $document_no])->row();
            if ($queue) {
                $this->db->set('attempts', 'attempts + 1', FALSE);
                $this->db->update('journal_inventory_queue', $data, ['id' => $queue->id]);
            } elseif (!$result['status'] && !$complete) {
                // Dokumen yang langsung sukses / tanpa qty tidak perlu dicatat
                $this->db->insert('journal_inventory_queue', array_merge($data, [
                    'modul'        => $modul,
                    'document_no'  => $document_no,
                    'attempts'     => 1,
                    'created_date' => $now,
                ]));
            }
        } catch (Exception $e) {
            log_message('error', "AutoPosting queue $modul $document_no: " . $e->getMessage());
        }
    }

    /**
     * Tandai queue SUCCESS jika dokumen sudah dijurnal dengan cara lain (posting manual dari form / sudah ada sebelumnya)
     */
    public function queue_mark_success($modul, $document_no, $number = null)
    {
        $this->db->update('journal_inventory_queue', [
            'status'            => 'SUCCESS',
            'last_error'        => null,
            'journal_number'    => $number,
            'last_attempt_date' => date('Y-m-d H:i:s'),
        ], ['modul' => $modul, 'document_no' => $document_no, 'status' => 'FAILED']);
    }

    /**
     * Repost dokumen FAILED di queue
     * @param array|null $ids          id queue tertentu (repost manual), null = semua FAILED
     * @param string     $source       REPOST / CRON
     * @param int|null   $max_attempts lewati dokumen yang sudah dicoba >= batas ini (cron), null = tanpa batas
     * @return array ['success' => n, 'failed' => n, 'results' => [document_no => message]]
     */
    public function repost_failed($ids = null, $source = 'REPOST', $max_attempts = null)
    {
        $this->db->select('id, modul, document_no');
        $this->db->from('journal_inventory_queue');
        $this->db->where('status', 'FAILED');
        $this->db->where($this->queue_visible_sql(), NULL, FALSE);
        if (!empty($ids)) $this->db->where_in('id', $ids);
        if ($max_attempts) $this->db->where('attempts <', $max_attempts);
        $this->db->order_by('id', 'asc');
        $queues = $this->db->get()->result();

        $summary = ['success' => 0, 'complete' => 0, 'failed' => 0, 'results' => []];
        foreach ($queues as $q) {
            $result = $this->inventory($q->modul, $q->document_no, $source);
            $summary[$result['status'] ? 'success' : (!empty($result['complete']) ? 'complete' : 'failed')]++;
            $summary['results'][$q->document_no] = $result['message'];
        }
        return $summary;
    }

    /**
     * Sapu jaring POR: POR sejak $from_date yang semua labelnya sudah di-scan tapi belum dijurnal
     * (misal pemicu scan tidak jalan). Dokumen yang sudah FAILED di queue dilewati (ditangani repost_failed).
     * @return array ['success' => n, 'failed' => n, 'results' => [...]]
     */
    public function sweep_por($from_date, $source = 'CRON')
    {
        $this->db->select('a.receipt_no');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('(SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels GROUP BY receipt_id) l', 'a.receipt_id = l.receipt_id', 'left', FALSE);
        $this->db->where('a.deleted', 0);
        $this->db->where('a.receipt_date >=', $from_date);
        $this->db->where("NOT EXISTS (SELECT 1 FROM journal_inventory j WHERE j.modul = 'PURCHASE ORDER RECEIPT' AND j.document_no = a.receipt_no)", NULL, FALSE);
        $this->db->where("NOT EXISTS (SELECT 1 FROM journal_inventory_queue q WHERE q.modul = 'PURCHASE ORDER RECEIPT' AND q.document_no = a.receipt_no)", NULL, FALSE);
        $this->db->where("NOT " . $this->non_rm_por_sql('a.receipt_no'), NULL, FALSE);
        $this->db->group_by('a.receipt_no');
        // Semua label sudah di-scan (sama dengan is_por_ready)
        $this->db->having('SUM(CASE WHEN COALESCE(l.total_scan, 0) < a.qty_label THEN 1 ELSE 0 END) = 0', NULL, FALSE);
        $receipts = $this->db->get()->result();

        $summary = ['success' => 0, 'failed' => 0, 'results' => []];
        foreach ($receipts as $r) {
            $result = $this->inventory('PURCHASE ORDER RECEIPT', $r->receipt_no, $source);
            $summary[$result['status'] ? 'success' : 'failed']++;
            $summary['results'][$r->receipt_no] = $result['message'];
        }
        return $summary;
    }

    /**
     * Pemicu dari scan label (Item Receipts / Barcode Divides):
     * jika label milik POR dan semua label POR sudah di-scan, posting jurnal POR.
     * Tidak pernah melempar error ke pemanggil supaya proses scan tidak terganggu.
     * @return array|null Hasil inventory(), null jika belum perlu posting
     */
    public function auto_post_por_by_label($label_no)
    {
        try {
            $this->db->select('b.receipt_no');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $label_no);
            $por = $this->db->get()->row();

            // Bukan label POR, belum semua label di-scan, atau sudah dijurnal
            if (!$por) return null;
            if (!$this->is_por_ready($por->receipt_no)['ready']) return null;
            if ($this->check_duplicate_entry('PURCHASE ORDER RECEIPT', $por->receipt_no)) return null;

            // Gagal tercatat di journal_inventory_queue (FAILED), bisa di-repost dari menu Journal Inventory
            $result = $this->inventory('PURCHASE ORDER RECEIPT', $por->receipt_no, 'SCAN');
            if (!$result['status']) {
                log_message('error', "AutoPosting POR {$por->receipt_no} (label $label_no): " . $result['message']);
            }
            return $result;

        } catch (Throwable $e) {
            log_message('error', "AutoPosting POR by label $label_no: " . $e->getMessage());
            return null;
        }
    }

    /** -------------------------------------------
     * Pemicu otomatis per module (tabel pemicu dari user, 2026-10-08)
     * Semua pemicu tidak pernah melempar error ke pemanggil; gagal posting tercatat di journal_inventory_queue.
     ---------------------------------------------- */

    // Jenis transaksi Transaction RM yang dijurnal otomatis
    public $transaction_rm_auto_types = ['BPM', 'ADJ IN STO', 'ADJ OUT STO', 'BPB', 'RETURN NG TO SUPPLIER', 'REPLACEMENT NG FROM SUPPLIER'];

    // Replacement NG: INJ dijurnal saat semua label di-scan, selain INJ saat save di Transaction RM
    const REPLACEMENT_TYPE = 'REPLACEMENT NG FROM SUPPLIER';

    /**
     * Posting jika dokumen belum dijurnal. Error hanya di-log + queue.
     * @return array|null Hasil inventory(), null jika dilewati
     */
    private function _auto_post($modul, $document_no, $source)
    {
        try {
            if ($this->check_duplicate_entry($modul, $document_no)) return null;
            $result = $this->inventory($modul, $document_no, $source);
            if (!$result['status']) {
                log_message('error', "AutoPosting $modul $document_no ($source): " . $result['message']);
            }
            return $result;
        } catch (Throwable $e) {
            log_message('error', "AutoPosting $modul $document_no ($source): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Semua baris dokumen (deleted = 0) sudah selesai
     * @param array $closed_status Nilai status yang dianggap selesai
     */
    private function _all_rows_closed($table, $request_no, array $closed_status = [1])
    {
        $this->db->select("COUNT(*) AS total, SUM(CASE WHEN status IN (" . implode(',', array_map('intval', $closed_status)) . ") THEN 1 ELSE 0 END) AS closed", FALSE);
        $this->db->from($table);
        $this->db->where('request_no', $request_no);
        $this->db->where('deleted', 0);
        $row = $this->db->get()->row();
        return $row && $row->total > 0 && $row->total == $row->closed;
    }

    /**
     * Pemicu scan label di Item Receipts: label POR atau label Replacement NG (INJ)
     */
    public function auto_post_by_label($label_no)
    {
        $result = $this->auto_post_por_by_label($label_no);
        if ($result !== null) return $result;

        try {
            $label = $this->db->select('request_no')->get_where('transaction_labels', ['label_no' => $label_no])->row();
            return $label ? $this->auto_post_transaction_rm($label->request_no, 'SCAN') : null;
        } catch (Throwable $e) {
            log_message('error', "AutoPosting by label $label_no: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Transaction RM: $trigger SAVE (setelah semua baris tersimpan) atau SCAN (label Replacement NG)
     * - Hanya jenis di $transaction_rm_auto_types
     * - Replacement NG semua item INJ: hanya SCAN dan semua label sudah di-scan. Selain itu: hanya SAVE
     */
    public function auto_post_transaction_rm($request_no, $trigger = 'SAVE')
    {
        try {
            $this->db->select('a.transaction_type, SUM(CASE WHEN TRIM(c.division) = \'INJ\' THEN 0 ELSE 1 END) AS non_inj', FALSE);
            $this->db->from('transaction_rm a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
            $this->db->where('a.request_no', $request_no);
            $this->db->where('a.deleted', 0);
            $this->db->group_by('a.transaction_type');
            $types = $this->db->get()->result();

            if (count($types) != 1) return null;
            $type = strtoupper(trim((string) $types[0]->transaction_type));
            if (!in_array($type, $this->transaction_rm_auto_types)) return null;

            $by_scan = ($type == self::REPLACEMENT_TYPE && $types[0]->non_inj == 0);
            if ($by_scan != ($trigger == 'SCAN')) return null;

            if ($by_scan) {
                $label = $this->db->query("SELECT COUNT(*) AS total, SUM(status) AS scanned FROM transaction_labels WHERE request_no = ? AND deleted = 0", [$request_no])->row();
                if (!$label || $label->total == 0 || $label->scanned < $label->total) return null;
            }

            return $this->_auto_post('TRANSACTION RM', $request_no, $trigger);
        } catch (Throwable $e) {
            log_message('error', "AutoPosting TRANSACTION RM $request_no: " . $e->getMessage());
            return null;
        }
    }

    // BPM menu (INJ): setelah scan, jika semua item BPM sudah ter-scan penuh (bpm.status = 1)
    public function auto_post_bpm($request_no)
    {
        if (!$this->_all_rows_closed('bpm', $request_no)) return null;
        return $this->_auto_post('BPM', $request_no, 'SCAN');
    }

    /**
     * Issued Materials / Closing SH: Supply Sheet (SH-), Non Supply Sheet / Kanban (REQ-), Material Request (PRQ-)
     * dijurnal jika semua item dokumen sudah selesai di-issue
     */
    public function auto_post_supply_document($request_no)
    {
        $documents = [
            'SUPPLY SHEET'        => ['supply_sheets', [1, 2]],   // 2 = Closing SH
            'NON SUPPLY SHEET'    => ['supply_materials', [1]],
            'MATERIAL REQUESTION' => ['supply_requestions', [1]],
        ];

        foreach ($documents as $modul => list($table, $closed_status)) {
            $exists = $this->db->where(['request_no' => $request_no, 'deleted' => 0])->count_all_results($table);
            if ($exists == 0) continue;
            if (!$this->_all_rows_closed($table, $request_no, $closed_status)) return null;
            return $this->_auto_post($modul, $request_no, 'ISSUED');
        }
        return null;
    }

    // Jenis transaksi Transaction FG yang dijurnal otomatis (master fg_transactions)
    public $transaction_fg_auto_types = ['ADJ IN STO', 'ADJ OUT STO', 'BPB', 'RECEIPT FG', 'RETURN FROM CUSTOMER'];

    // Transaction FG: setelah Save All (tidak ada scan)
    public function auto_post_transaction_fg($request_no)
    {
        try {
            $types = $this->db->select('DISTINCT UPPER(TRIM(transaction_type)) AS type', FALSE)
                ->where(['request_no' => $request_no, 'deleted' => 0])->get('transaction_fg')->result();
            if (count($types) != 1 || !in_array($types[0]->type, $this->transaction_fg_auto_types)) return null;
            return $this->_auto_post('TRANSACTION FG', $request_no, 'SAVE');
        } catch (Throwable $e) {
            log_message('error', "AutoPosting TRANSACTION FG $request_no: " . $e->getMessage());
            return null;
        }
    }

    // Output Production, Item NG, Delivery Note: setelah Save All / upload (tidak ada scan di dokumen ini)
    public function auto_post_on_save($modul, $document_no)
    {
        if (!in_array($modul, ['OUTPUT PRODUCTION', 'ITEM NG', 'DELIVERY NOTE'])) return null;
        return $this->_auto_post($modul, $document_no, 'SAVE');
    }

    /**
     * WIP Receipt: dijurnal jika semua baris status = 1
     * - INJ: status 1 setelah semua label di-scan di Scan Receiving FG (Item_receipts_fg) -> $source SCAN
     * - MTS: status 1 langsung saat save -> $source SAVE
     */
    public function auto_post_wip_receipt($document_no, $source = 'SCAN')
    {
        try {
            if (!$this->is_wip_receipt_ready($document_no)) return null;
            return $this->_auto_post('WIP RECEIPT', $document_no, $source);
        } catch (Throwable $e) {
            log_message('error', "AutoPosting WIP RECEIPT $document_no: " . $e->getMessage());
            return null;
        }
    }

    // Semua baris WIP Receipt sudah closed (INJ: semua label di-scan)
    public function is_wip_receipt_ready($document_no)
    {
        $row = $this->db->query("SELECT COUNT(*) AS total, SUM(status = 1) AS closed FROM wip_receipts WHERE document_no = ? AND deleted = 0", [$document_no])->row();
        return $row && $row->total > 0 && $row->total == $row->closed;
    }

    /**
     * Preview jurnal tanpa insert (dipakai Journal Inventory manual), hasilnya sama dengan inventory()
     * @return array ['status' => bool, 'message' => string, 'rows' => [...], 'footer' => [...]]
     */
    public function preview($modul, $document_no)
    {
        $modul = $this->normalize_module($modul);
        if (!isset($this->modules[$modul])) {
            return ['status' => false, 'message' => "Module $modul is not found.", 'rows' => [], 'footer' => []];
        }

        try {
            $entry = $this->{$this->modules[$modul]['process']}($modul, $document_no);
            $rows  = $this->_compose_rows($entry);
            $this->_assert_balanced($rows);
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage(), 'rows' => [], 'footer' => []];
        }

        $footer = [[
            'currency'        => 'TOTAL',
            'original_debit'  => array_sum(array_column($rows, 'original_debit')),
            'original_credit' => array_sum(array_column($rows, 'original_credit')),
            'local_debit'     => array_sum(array_column($rows, 'local_debit')),
            'local_credit'    => array_sum(array_column($rows, 'local_credit')),
        ]];

        return ['status' => true, 'message' => 'Ready to post', 'rows' => $rows, 'footer' => $footer];
    }

    /**
     * Kondisi SQL filter "Status Posting" di modul sumber (Supply Sheet, BPM, POR, dll):
     * CLOSED = dokumen sudah punya voucher di journal_inventory, OPEN = belum.
     * Dokumen COMPLETE (qty 0) tidak punya voucher, jadi termasuk OPEN.
     * @param string[] $moduls       Module journal_inventory dokumen ini
     * @param string   $document_col Kolom nomor dokumen di query sumber (misal 'a.request_no')
     * @param string   $status       OPEN / CLOSED, selain itu null (tanpa filter)
     * @return string|null
     */
    public function posting_status_sql(array $moduls, $document_col, $status)
    {
        $status = strtoupper(trim((string) $status));
        if (!in_array($status, ['OPEN', 'CLOSED'], true) || empty($moduls)) {
            return null;
        }
        $in = implode(', ', array_map([$this->db, 'escape'], $moduls));
        $exists = "EXISTS (SELECT 1 FROM journal_inventory ji_ps WHERE ji_ps.modul IN ($in) AND ji_ps.document_no = $document_col)";
        return $status === 'CLOSED' ? $exists : "NOT $exists";
    }

    /**
     * Dokumen yang sudah dijurnal tidak boleh diubah/dihapus/dibuka lagi (jurnal tidak ikut berubah).
     * @param string[] $moduls  Module journal_inventory yang dicek
     * @return array|null Respons JSON siap kirim jika sudah dijurnal, null jika belum
     */
    public function journaled_block(array $moduls, $document_no)
    {
        if (empty($document_no)) return null;
        $row = $this->db->select('number')->where_in('modul', $moduls)->where('document_no', $document_no)->get('journal_inventory', 1)->row();
        if (!$row) return null;
        return [
            'title'   => 'Failed',
            'message' => "Document $document_no has been posted to Journal Inventory ({$row->number}) and cannot be changed. Delete the journal first.",
            'theme'   => 'error',
            'success' => false,
        ];
    }

    // Journal Duplication Protection
    public function check_duplicate_entry($modul, $doc_no)
    {
        $check = $this->db->get_where('journal_inventory', [
            'modul' => $modul,
            'document_no' => $doc_no
        ])->num_rows();

        return ($check > 0);
    }

    /**
     * Get Journal Type inventory + akun DEBIT/CREDIT dari Journal Setup
     * @param string      $module           Nilai module pada journal_types (nama menu, misal: PURCHASE ORDER RECEIPT)
     * @param string|null $transaction_type Jenis transaksi dalam module (misal: ADJ IN STO untuk TRANSACTION RM), null = tanpa jenis
     * @param string|null $division         Division item (INJ / MTS / ...). Dipilih journal type dengan division sama,
     *                                      jika tidak ada pakai journal type dengan division kosong (berlaku semua division)
     * @return array ['journal_type_id', 'journal_type_number', 'debit' => {account_number, account_name}, 'credit' => {...}]
     * @throws Exception jika belum di-setting atau setting ambigu
     */
    public function get_inventory_setup($module, $transaction_type = null, $division = null)
    {
        $this->db->select('id, number, name, division');
        $this->db->from('journal_types');
        $this->db->where('deleted', 0);
        $this->db->where('is_journal_inventory', 1);
        $this->db->where("FIND_IN_SET(" . $this->db->escape($module) . ", REPLACE(module, ', ', ',')) > 0", NULL, FALSE);
        if (!empty($transaction_type)) {
            $this->db->where('transaction_type', $transaction_type);
        } else {
            $this->db->group_start()->where('transaction_type IS NULL', NULL, FALSE)->or_where('transaction_type', '')->group_end();
        }
        $candidates = $this->db->get()->result();

        // Division sama diutamakan, lalu division kosong
        $division = trim((string) $division);
        $journal_types = array_values(array_filter($candidates, function ($jt) use ($division) {
            return $division !== '' && trim((string) $jt->division) === $division;
        }));
        if (empty($journal_types)) {
            $journal_types = array_values(array_filter($candidates, function ($jt) {
                return trim((string) $jt->division) === '';
            }));
        }

        $label = $module . (!empty($transaction_type) ? " ($transaction_type)" : "") . ($division !== '' ? " division $division" : "");
        if (empty($journal_types)) {
            throw new Exception("Journal Type inventory for module $label is not set! Please set it in Journal Types (Journal Inventory = YES).");
        }
        if (count($journal_types) > 1) {
            $numbers = implode(', ', array_column($journal_types, 'number'));
            throw new Exception("More than one Journal Type inventory for module $label ($numbers). Please keep only one.");
        }
        $journal_type = $journal_types[0];

        $this->db->select('a.account_number, a.status, b.account_name');
        $this->db->from('journal_setups a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.journal_type_id', $journal_type->id);
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.flag', 'asc');
        $setups = $this->db->get()->result();

        $debit  = array_values(array_filter($setups, function ($s) { return strtoupper($s->status) == 'DEBIT'; }));
        $credit = array_values(array_filter($setups, function ($s) { return strtoupper($s->status) == 'CREDIT'; }));

        if (count($debit) != 1 || count($credit) != 1) {
            throw new Exception("Journal Setup for {$journal_type->number} ({$journal_type->name}) must have exactly 1 DEBIT and 1 CREDIT account.");
        }

        return [
            'journal_type_id'     => $journal_type->id,
            'journal_type_number' => $journal_type->number,
            'debit'               => $debit[0],
            'credit'              => $credit[0],
        ];
    }

    // Get Rate
    /**
     * Kurs tengah (middle) pada tanggal transaksi
     * @throws Exception jika kurs mata uang asing belum diisi (jangan jatuh ke 1.0: nilai asing tercatat sama dengan rupiah)
     */
    public function get_rate($date, $currency) {
        if (empty($currency) || $currency == "IDR") return 1.0;
        $r = $this->db->get_where('standard_exchange_rates', [
            'currency_from' => $currency,
            'start_date <=' => $date,
            'end_date >='   => $date,
            'deleted'       => 0
        ])->row();
        if (!$r || (float) $r->middle <= 0) {
            throw new Exception("Exchange rate $currency for $date is not available! Please update Standard Exchange Rate");
        }
        return (float) $r->middle;
    }

    /**
     * Kurs middle Exchange Rates (bukan Standard Exchange Rate) pada periode yang mencakup $date.
     * Dipakai POR, supaya nilai IDR GR/IR di POR sama dengan PI (PI memakai exchange_rates middle per periode).
     */
    public function get_rate_period($date, $currency) {
        if (empty($currency) || $currency == "IDR") return 1.0;
        $r = $this->db->get_where('exchange_rates', [
            'currency_from' => $currency,
            'currency_to'   => 'IDR',
            'start_date <=' => $date,
            'end_date >='   => $date,
            'deleted'       => 0
        ])->row();
        if (!$r || (float) $r->middle <= 0) {
            throw new Exception("Exchange rate $currency for $date is not available! Please update Exchange Rate");
        }
        return (float) $r->middle;
    }

    /**
     * Get Standard Price
     * jika pricenya 0 atau tidak terdaftar pada standard price maka amount debit/credit akan 0, jika amount 0 tidak bisa diposting. (Bu Nina)
     */

    // Get Price RM
    public function get_price_rm($journal_date, $item_rm_id)
    {
        $ref_date = !empty($journal_date) ? strtotime((string) $journal_date) : time();
        $d = date("Y-m-d", $ref_date);

        $this->db->order_by('start_date', 'DESC');
        $r = $this->db->get_where('standard_price_rm', [
            'item_rm_id'    => $item_rm_id,
            'start_date <=' => $d,
            'end_date >='   => $d,
            'deleted'       => 0,
        ])->row();

        return ($r) ? (float)$r->price : 0;
    }

    /**
     * Harga RM untuk transaksi non purchase (BPM, ADJ STO, Supply Sheet, dll.), dalam IDR.
     * Urutan: harga PO terakhir (po_date <= tanggal transaksi) → Standard Price RM → 0 (tidak ditemukan).
     * Harga PO = price x (1 - discount), mata uang supplier dikonversi ke IDR dengan kurs tanggal transaksi.
     */
    public function get_price_rm_for_journal($journal_date, $item_rm_id)
    {
        $ref_date = !empty($journal_date) ? date('Y-m-d', strtotime((string) $journal_date)) : date('Y-m-d');

        $this->db->select('a.price, a.discount, d.currency');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers d', 'a.supplier_id = d.id', 'left');
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->where('a.deleted', 0);
        $this->db->where('a.price >', 0);
        $this->db->where('a.po_date <=', $ref_date);
        $this->db->order_by('a.po_date', 'DESC');
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit(1);
        $po = $this->db->get()->row();

        if ($po) {
            $price = (float) $po->price * (1 - ((float) $po->discount / 100));
            return $price * $this->get_rate($ref_date, $po->currency);
        }

        return $this->get_price_rm($journal_date, $item_rm_id);
    }

    // Get Price FG
    public function get_price_fg($journal_date, $item_fg_id)
    {
        $ref_date = !empty($journal_date) ? strtotime((string) $journal_date) : time();
        $transaction_from = date("Y-m-01", $ref_date);
        $transaction_to   = date("Y-m-t", $ref_date);

        $r = $this->db->get_where('standard_price_fg', [
            'item_fg_id'    => $item_fg_id,
            'start_date <=' => $transaction_from,
            'end_date >='   => $transaction_to,
        ])->row();

        return ($r) ? (float)$r->price : 0;
    }

    /**
     * Cek POR siap dijurnal: semua item kategori RM dan semua label sudah di-scan (sama dengan Status POR = CLOSED)
     * @return array ['ready' => bool, 'message' => string]
     */
    public function is_por_ready($receipt_no)
    {
        $this->db->select('a.receipt_id, a.qty_label, COALESCE(l.total_scan, 0) as total_scan, COALESCE(ic.number, \'\') as category', FALSE);
        $this->db->from('purchase_order_receipts a');
        $this->db->join('(SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels GROUP BY receipt_id) l', 'a.receipt_id = l.receipt_id', 'left', FALSE);
        $this->db->join('item_rm i', 'a.item_rm_id = i.id', 'left');
        $this->db->join('item_categories ic', 'i.item_category_id = ic.id', 'left');
        $this->db->where('a.receipt_no', $receipt_no);
        $this->db->where('a.deleted', 0);
        $rows = $this->db->get()->result();

        if (empty($rows)) {
            return ['ready' => false, 'message' => "Receipt No $receipt_no not found."];
        }

        foreach ($rows as $row) {
            if ($row->category != self::POR_CATEGORY) {
                $category = $row->category !== '' ? $row->category : 'without category';
                return ['ready' => false, 'message' => "Receipt No $receipt_no is $category, only " . self::POR_CATEGORY . " receipts are posted to Journal Inventory."];
            }
        }
        foreach ($rows as $row) {
            if ($row->total_scan < $row->qty_label) {
                return ['ready' => false, 'message' => "Receipt No $receipt_no: labels of {$row->receipt_id} have not been fully scanned ({$row->total_scan}/{$row->qty_label}). It will be posted automatically when all labels are scanned."];
            }
        }

        return ['ready' => true, 'message' => 'Ready to post'];
    }

    // Auto Generate ID
    private function _generate_journal_id()
    {
        $date = date('Ymd');
        $prefix = $date;

        $this->db->select_max('id', 'last_id');
        $this->db->like('id', $prefix, 'after');
        $query = $this->db->get('journal_inventory')->row();

        if ($query->last_id) {
            // Ambil 6 digit terakhir, lalu tambah 1
            $last_increment = (int) substr($query->last_id, -6);
            $new_increment = str_pad($last_increment + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $new_increment = '000001';
        }

        return $prefix . $new_increment;
    }

    // Auto Generate Voucher Number
    private function _generate_voucher_no($journal_date)
    {
        // Check if the date needs to be decoded (if from AJAX) or used directly
        $raw_date = (base64_encode(base64_decode($journal_date, true)) === $journal_date)
                    ? base64_decode($journal_date)
                    : $journal_date;

        $prefix = "GLINV" . date("ym", strtotime((string) $raw_date));

        $this->db->select_max('number', 'kode');
        $this->db->like('number', $prefix, 'after');
        $query = $this->db->get('journal_inventory');

        $row = $query->row();
        $last_number = $row->kode;

        if ($last_number == NULL) {
            $autoID = "0001";
        } else {
            $urutan = (int) substr($last_number, -4);
            $urutan++;
            $autoID = sprintf("%04d", $urutan);
        }

        return $prefix . $autoID;
    }

    // Description: Supplier | Document | Reff | Item No | Item Name (bagian kosong dilewati)
    private function _description(array $parts)
    {
        return implode(" | ", array_filter($parts, function ($p) { return $p !== null && $p !== ''; }));
    }

    /**
     * Jalankan proses posting dalam 1 DB Transaction (Pak Angga)
     * @param callable $process function() yang melempar Exception jika gagal
     */
    private function _run_transaction(callable $process)
    {
        $this->db->trans_begin();

        try {
            $process();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction Failed");
            }
            $this->db->trans_commit();
            return ['status' => true, 'message' => 'Success'];

        } catch (Throwable $e) {
            // Rollback jika terjadi error (termasuk Error PHP, misal hasil query gagal saat db_debug = FALSE)
            $this->db->trans_rollback();
            $msg = "Error: " . $e->getMessage();
            if ($e->getCode() === self::ERR_NO_QTY) {
                return ['status' => false, 'complete' => true, 'message' => $e->getMessage()];
            }
            log_message('error', "AutoPosting: " . $msg);
            return ['status' => false, 'message' => $msg];
        }
    }

    /**
     * Pemilih setup per division (di-cache per dokumen): function($division) => get_inventory_setup()
     */
    private function _setup_resolver($module, $transaction_type = null)
    {
        $cache = [];
        return function ($division) use ($module, $transaction_type, &$cache) {
            $key = trim((string) $division);
            if (!array_key_exists($key, $cache)) {
                $cache[$key] = $this->get_inventory_setup($module, $transaction_type, $key);
            }
            return $cache[$key];
        };
    }

    /**
     * Susun baris jurnal: 1 baris DEBIT per item + 1 baris CREDIT total per journal type
     * (credit_per_line = true: 1 baris CREDIT per item, nilai & description sama dengan DEBIT-nya)
     * Journal type & akun per item (lines[].setup), sehingga 1 dokumen INJ + MTS tetap 1 voucher.
     * @param array $entry Hasil _build_xxx(): doc, lines, credit_per_line (opsional)
     *                     doc:   journal_date, document_no, invoice_no, currency, rate, company_id, company_name, description
     *                     lines: setup (get_inventory_setup), amount_original, rate, invoice_no, description
     */
    private function _compose_rows($entry)
    {
        $doc   = $entry['doc'];
        $lines = $entry['lines'];

        if (empty($lines)) {
            throw new Exception("No journal lines for document {$doc['document_no']}.");
        }

        $base = [
            'journal_date'    => $doc['journal_date'],
            'trans_date'      => date('Y-m-d'),
            'document_no'     => $doc['document_no'],
            'currency'        => $doc['currency'],
            'company_name'    => $doc['company_name'] ?? '',
            'company_id'      => $doc['company_id'] ?? '',
        ];

        $rows    = [];
        $credits = []; // DEBIT pasangan CREDIT: [index baris DEBIT => setup]
        $totals  = []; // total per journal type untuk CREDIT gabungan
        $total_orig_all  = 0;
        $total_local_all = 0;

        // DEBIT
        foreach ($lines as $line) {
            $setup = $line['setup'];
            // Dibulatkan 2 desimal (kolom decimal(20,2)) supaya total DEBIT = CREDIT juga setelah disimpan
            $amount_original = round((float) $line['amount_original'], 2);
            $amount_local    = round($amount_original * $line['rate'], 2);

            $credits[count($rows)] = $setup;
            $rows[] = array_merge($base, [
                'journal_type_id' => $setup['journal_type_id'],
                'invoice_no'      => $line['invoice_no'] ?? '',
                'account_number'  => $setup['debit']->account_number,
                'account_name'    => $setup['debit']->account_name,
                'original_debit'  => $amount_original,
                'original_credit' => 0,
                'local_debit'     => $amount_local,
                'local_credit'    => 0,
                'rates'           => $line['rate'],
                'description'     => $line['description'],
            ]);

            $id = $setup['journal_type_id'];
            if (!isset($totals[$id])) $totals[$id] = ['setup' => $setup, 'original' => 0, 'local' => 0];
            $totals[$id]['original'] += $amount_original;
            $totals[$id]['local']    += $amount_local;

            $total_orig_all  += $amount_original;
            $total_local_all += $amount_local;
        }

        // validasi total
        if ($total_local_all <= 0 || $total_orig_all <= 0) {
            throw new Exception("Transaction amount is zero! Posting Journal canceled.");
        }

        // CREDIT per item (POR, format Finance): pasangan tiap baris DEBIT
        if (!empty($entry['credit_per_line'])) {
            foreach ($credits as $i => $setup) {
                $debit  = $rows[$i];
                $rows[] = array_merge($debit, [
                    'account_number'  => $setup['credit']->account_number,
                    'account_name'    => $setup['credit']->account_name,
                    'original_debit'  => 0,
                    'original_credit' => $debit['original_debit'],
                    'local_debit'     => 0,
                    'local_credit'    => $debit['local_debit'],
                ]);
            }
            return $rows;
        }

        // CREDIT total per journal type
        foreach ($totals as $id => $total) {
            $rows[] = array_merge($base, [
                'journal_type_id' => $id,
                'invoice_no'      => $doc['invoice_no'] ?? '',
                'account_number'  => $total['setup']['credit']->account_number,
                'account_name'    => $total['setup']['credit']->account_name,
                'original_debit'  => 0,
                'original_credit' => $total['original'],
                'local_debit'     => 0,
                'local_credit'    => $total['local'],
                'rates'           => $doc['rate'],
                'description'     => $doc['description'],
            ]);
        }

        return $rows;
    }

    /**
     * Pastikan total DEBIT = total CREDIT (original & local) sebelum disimpan
     * @throws Exception jika tidak balance
     */
    private function _assert_balanced($rows)
    {
        foreach (['original', 'local'] as $type) {
            $debit  = round(array_sum(array_column($rows, "{$type}_debit")), 2);
            $credit = round(array_sum(array_column($rows, "{$type}_credit")), 2);
            if ($debit != $credit) {
                throw new Exception("Journal is not balanced ($type debit $debit, credit $credit). Posting Journal canceled.");
            }
        }
    }

    /**
     * Insert baris jurnal ke journal_inventory dalam 1 voucher
     * @param string $modul Nilai modul yang disimpan di journal_inventory (dipakai cek duplikat)
     * @return string Nomor voucher
     */
    private function _insert_rows($modul, $rows)
    {
        $voucher_no = $this->_generate_voucher_no($rows[0]['journal_date']);
        // created_by FK ke users.username, tanpa session (misal dari API) diisi NULL
        $created_by = !empty($this->session->username) ? $this->session->username : null;

        foreach ($rows as $row) {
            $data = array_merge($row, [
                'id'           => $this->_generate_journal_id(),
                'number'       => $voucher_no,
                'modul'        => $modul,
                'remarks'      => "Auto Posting Journal",
                'created_date' => date('Y-m-d H:i:s'),
                'created_by'   => $created_by,
            ]);

            if (!$this->db->insert('journal_inventory', $data)) {
                $db_error = $this->db->error();
                throw new Exception("Database Error: " . $db_error['message']);
            }
        }

        return $voucher_no;
    }

    /**
     * Entry jurnal dengan harga Standard Price (IDR), dipakai modul selain POR
     * @param callable $setup_for _setup_resolver(): function($division) => setup
     * @param array $records  Wajib berisi: request_date, request_no, qty_field, item_no, item_name, item_division (opsional)
     * @param string $ref_field  Kolom referensi untuk invoice_no & description
     * @param callable $get_price function($row) => price
     * @param string $price_source Keterangan sumber harga untuk pesan error
     */
    private function _standard_price_entry(callable $setup_for, $document_no, $records, $qty_field, $ref_field, callable $get_price, $price_source = 'harga PO terakhir dan Standard Price tidak ada')
    {
        $currency = "IDR"; // default
        $groups = [];

        foreach ($records as $row) {
            if ((float) $row[$qty_field] <= 0) continue; // tidak ada mutasi
            $price = $get_price($row);
            if ($price <= 0) {
                throw new Exception("Harga tidak ditemukan untuk item {$row['item_no']} ($price_source)");
            }

            // Item yang sama dalam 1 dokumen dijadikan 1 baris DEBIT
            $setup = $setup_for($row['item_division'] ?? null);
            $rate  = $this->get_rate($row['request_date'], $currency);
            $key   = ($row['item_rm_id'] ?? ($row['item_fg_id'] ?? $row['item_no'])) . '|' . $setup['journal_type_id'] . '|' . $rate;
            if (!isset($groups[$key])) {
                $groups[$key] = ['row' => $row, 'setup' => $setup, 'rate' => $rate, 'amount' => 0, 'refs' => []];
            }
            $groups[$key]['amount'] += (float) $row[$qty_field] * $price;
            $groups[$key]['refs'][$row[$ref_field] ?? ''] = true;
        }

        $lines = [];
        foreach ($groups as $g) {
            $row = $g['row'];
            $ref = count($g['refs']) > 1 ? '' : ($row[$ref_field] ?? '');
            $lines[] = [
                'setup'           => $g['setup'],
                'amount_original' => $g['amount'],
                'rate'            => $g['rate'],
                'invoice_no'      => ($ref !== '') ? $ref : $document_no,
                // Description: doc_no;product_no
                'description'     => $document_no . ';' . trim((string) $row['item_no']),
            ];
        }

        if (empty($lines)) {
            throw new Exception("No issued/transaction qty for document $document_no.", self::ERR_NO_QTY);
        }

        $first = $records[0];
        return [
            'doc'   => [
                'journal_date' => $first['request_date'],
                'document_no'  => $first['request_no'],
                'invoice_no'   => $first[$ref_field] ?? '',
                'currency'     => $currency,
                'rate'         => $this->get_rate($first['request_date'], $currency),
                'company_id'   => '',
                'company_name' => '',
                'description'  => $document_no,
            ],
            'lines' => $lines,
        ];
    }


    /** -------------------------------------------
     * Build Journal Inventory per Modul (dipanggil di dalam DB transaction / preview)
     ---------------------------------------------- */

    // PURCHASE ORDER RECEIPT - harga dari PO (qty PO x price x (1 - discount))
    private function _build_por($modul, $receipt_no)
    {
        $ready = $this->is_por_ready($receipt_no);
        if (!$ready['ready']) {
            throw new Exception($ready['message']);
        }

        $setup_for = $this->_setup_resolver('PURCHASE ORDER RECEIPT');

        // Get Data POR (Query Detail per Item)
        $this->db->select("
            a.receipt_no as document_no,
            a.receipt_date as trans_date,
            a.po_no as invoice_no,
            a.item_rm_id,
            c.name as item_name,
            c.number as item_no,
            c.division as item_division,
            b.name as supplier_name,
            b.currency,
            a.supplier_id,
            a.qty_receipt2 as qty,
            f.price,
            f.discount
        ");
        // Rumus Total per Item (Debit)
        $this->db->select("((a.qty_receipt2 * f.price) * (1 - (COALESCE(f.discount, 0) / 100))) as item_total_original", FALSE);
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('purchase_orders f', "a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id", 'left');
        $this->db->where('a.receipt_no', $receipt_no);
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.receipt_id', 'asc');
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Receipt No: $receipt_no");
        }

        // Item yang sama dalam 1 POR (PO sama) dijadikan 1 baris DEBIT + 1 CREDIT
        $lines = [];
        foreach ($records as $row) {
            if ((float) $row['price'] <= 0) {
                throw new Exception("PO price is empty for item {$row['item_no']} (PO {$row['invoice_no']})!");
            }

            $setup = $setup_for($row['item_division']);
            $key   = $row['item_rm_id'] . '|' . $row['invoice_no'] . '|' . $setup['journal_type_id'];
            if (isset($lines[$key])) {
                $lines[$key]['amount_original'] += $row['item_total_original'];
                continue;
            }
            $lines[$key] = [
                'setup'           => $setup,
                'amount_original' => $row['item_total_original'],
                'rate'            => $this->get_rate_period($row['trans_date'], $row['currency']),
                'invoice_no'      => $row['invoice_no'],
                // Description POR: doc_no;product_no (format Finance)
                'description'     => $receipt_no . ';' . trim((string) $row['item_no']),
            ];
        }
        $lines = array_values($lines);

        $first = $records[0];
        return [
            'doc'   => [
                'journal_date' => $first['trans_date'],
                'document_no'  => $first['document_no'],
                'invoice_no'   => $first['invoice_no'],
                'currency'     => $first['currency'],
                'rate'         => $this->get_rate_period($first['trans_date'], $first['currency']),
                'company_id'   => $first['supplier_id'],
                'company_name' => $first['supplier_name'],
                'description'  => $receipt_no,
            ],
            'lines' => $lines,
            // GR/IR dicatat per item, sama dengan contoh jurnal Finance
            'credit_per_line' => true,
        ];
    }

    // BPM menu (INJ) - SCAN BPM. Journal Type module BPM
    private function _build_bpm($modul, $document_no)
    {
        $setup_for = $this->_setup_resolver($modul);

        // Get BPM
        $this->db->select('a.*, b.number as item_no, b.name as item_name, b.uom, b.division as item_division');
        $this->db->from('bpm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.status', 1); // BPM status=closed
        $this->db->where('a.deleted', 0);
        $this->db->where('a.request_no', $document_no);
        $this->db->order_by('a.request_id', 'ASC');
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
        }

        return $this->_standard_price_entry($setup_for, $document_no, $records, 'qty', 'request_id', function ($row) {
            return $this->get_price_rm_for_journal($row['request_date'], $row['item_rm_id']);
        });
    }

    // Transaction RM - Journal Type dipilih dari transaction_type dokumen (ADJ IN STO, ADJ OUT STO, dll.)
    private function _build_transaction_rm($modul, $document_no)
    {
        // Get Transaction
        $this->db->select('a.*, c.number as item_no, c.name as item_name, c.uom, c.division as item_division');
        $this->db->from('transaction_rm a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.request_no', $document_no);
        $this->db->order_by('a.request_no', 'ASC');
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Request No: $document_no");
        }

        $transaction_types = array_unique(array_column($records, 'transaction_type'));
        if (count($transaction_types) != 1 || empty($transaction_types[0])) {
            throw new Exception("Transaction Type of Request No: $document_no is empty or not unique.");
        }
        $setup_for = $this->_setup_resolver($modul, $transaction_types[0]);

        return $this->_standard_price_entry($setup_for, $document_no, $records, 'qty', 'request_id', function ($row) {
            return $this->get_price_rm_for_journal($row['request_date'], $row['item_rm_id']);
        });
    }

    // Supply Sheet
    private function _build_supply_sheets($modul, $document_no)
    {
        $setup_for = $this->_setup_resolver($modul);

        // Get Transaction (qty yang benar-benar di-issued per item RM)
        $this->db->select('a.*, c.number as item_no, b.name as item_name, c.division as item_division, COALESCE(i.qty_issued, 0) as qty_issued', FALSE);
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.status', [1, 2]); // 2 = Closing SH
        $this->db->where('a.request_no', $document_no);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
        }

        try {
            return $this->_standard_price_entry($setup_for, $document_no, $records, 'qty_issued', 'workorder', function ($row) {
                return $this->get_price_rm_for_journal($row['request_date'], $row['item_rm_id']);
            });
        } catch (Exception $e) {
            if ($e->getCode() !== self::ERR_NO_QTY) throw $e;
            throw new Exception($this->_supply_sheet_no_qty_reason($document_no, $records), self::ERR_NO_QTY);
        }
    }

    /**
     * Keterangan kenapa Supply Sheet tidak punya qty RM untuk dijurnal:
     * - yang di-issue hanya item lain (pengganti CR-/PL-/equivalent), atau
     * - tidak ada issue sama sekali; kalau issue WO ini tercatat di nomor SH lain, nomor itu disebut (termasuk SH yang sudah dihapus)
     */
    private function _supply_sheet_no_qty_reason($document_no, array $records)
    {
        $this->db->select('c.number as item_no, SUM(d.qty) as qty', FALSE);
        $this->db->from('issued_material_details d');
        $this->db->join('item_rm c', 'c.id = d.item_rm_id', 'left');
        $this->db->where('d.request_no', $document_no);
        $this->db->where('d.deleted', 0);
        $this->db->group_by('c.number');
        $issued = $this->db->get()->result_array();

        if (!empty($issued)) {
            $items = array_map(function ($r) {
                return trim((string) $r['item_no']) . ' = ' . (float) $r['qty'];
            }, $issued);
            return "Supply Sheet $document_no: yang di-issue bukan item RM di Supply Sheet (" . implode(', ', $items)
                . "), misalnya item pengganti CR-/PL-/equivalent. Item pengganti tidak dijurnal.";
        }

        $workorder = trim((string) ($records[0]['workorder'] ?? ''));
        $reason    = "Supply Sheet $document_no di-close tanpa issue (tidak ada label yang di-scan)";
        if ($workorder === '') {
            return "$reason.";
        }

        // Issue untuk WO yang sama tapi tercatat di nomor SH lain
        $this->db->select("d.request_no, SUM(d.qty) as qty, MAX(CASE WHEN s.request_no IS NULL THEN 1 ELSE 0 END) as sh_deleted", FALSE);
        $this->db->from('issued_material_details d');
        $this->db->join('(SELECT DISTINCT request_no, workorder FROM issued_materials) m', 'm.request_no = d.request_no', 'inner', FALSE);
        $this->db->join('(SELECT DISTINCT request_no FROM supply_sheets WHERE deleted = 0) s', 's.request_no = d.request_no', 'left', FALSE);
        $this->db->where('m.workorder', $workorder);
        $this->db->where('d.request_no <>', $document_no);
        $this->db->where('d.deleted', 0);
        $this->db->group_by('d.request_no');
        $others = $this->db->get()->result_array();

        if (empty($others)) {
            return "$reason. WO $workorder juga tidak punya issue di Supply Sheet lain.";
        }

        $list = array_map(function ($r) {
            return $r['request_no'] . ' (qty ' . (float) $r['qty'] . ((int) $r['sh_deleted'] === 1 ? ', SH sudah dihapus, issue tidak dijurnal' : '') . ')';
        }, $others);
        return "$reason. Issue WO $workorder tercatat di: " . implode(', ', $list) . ".";
    }

    /**
     * Non Supply Sheet - Journal Type dipilih dari type dokumen (supply_materials.type):
     * - Issued Production (laporan: KANBAN PRD)
     * - Issued Customer   (laporan: KANBAN SUBCONT PRODUCT)
     * - Issued Subcont    (laporan: KANBAN SUBCONT JASA)
     */
    private function _build_non_supply_sheets($modul, $document_no)
    {
        $check_sm = $this->db->get_where('supply_materials', ['request_no' => $document_no])->row();
        if (!$check_sm) {
            throw new Exception("Non Supply Sheet not found");
        }

        if (empty($check_sm->type)) {
            throw new Exception("Type is not defined for this document of NON SUPPLY SHEET");
        }

        $setup_for = $this->_setup_resolver($modul, $check_sm->type);

        // Get Transaction
        $this->db->select('a.*, b.number as item_no, b.name as item_name, b.uom, b.division as item_division');
        $this->db->from('supply_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 1);
        $this->db->where('a.request_no', $document_no);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
        }

        return $this->_standard_price_entry($setup_for, $document_no, $records, 'qty', 'workorder', function ($row) {
            return $this->get_price_rm_for_journal($row['request_date'], $row['item_rm_id']);
        });
    }

    // Material Requestion
    private function _build_material_requestion($modul, $document_no)
    {
        $setup_for = $this->_setup_resolver($modul);

        // Get Transaction
        $this->db->select('a.*, b.number as item_no, b.name as item_name, b.uom, b.division as item_division');
        $this->db->from('supply_requestions a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 1);
        $this->db->where('a.request_no', $document_no);
        $this->db->order_by('a.request_no', 'DESC');
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
        }

        return $this->_standard_price_entry($setup_for, $document_no, $records, 'qty', 'workorder', function ($row) {
            return $this->get_price_rm_for_journal($row['request_date'], $row['item_rm_id']);
        });
    }

    // WIP Adjustment FG - harga Standard Price FG
    // Division per item dari item_fg.division_id (journal type division item diutamakan, lalu division kosong)
    private function _build_wip_adjustment_fg($modul, $document_no)
    {
        $records = $this->_fg_records('wip_adjustment_fg', 'request_no', $document_no,
            'a.request_date, a.request_no, a.workorder, a.qty');
        return $this->_fg_price_entry($this->_setup_resolver($modul), $document_no, $records, 'qty', 'workorder');
    }

    /**
     * Ambil baris dokumen FG untuk _standard_price_entry: request_date, request_no, item_fg_id, item_no, item_name, item_division
     * @param string $select Kolom tambahan dari tabel sumber (alias a), wajib berisi request_date & request_no
     */
    private function _fg_records($table, $doc_col, $document_no, $select, $division_col = 'd.number')
    {
        $this->db->select("$select, b.id AS item_fg_id, b.number AS item_no, b.name AS item_name, b.uom, $division_col AS item_division", FALSE);
        $this->db->from("$table a");
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('divisions d', 'b.division_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.$doc_col", $document_no);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Document No: $document_no");
        }
        return $records;
    }

    private function _fg_price_entry($setup_for, $document_no, $records, $qty_field, $ref_field)
    {
        return $this->_standard_price_entry($setup_for, $document_no, $records, $qty_field, $ref_field, function ($row) {
            return $this->get_price_fg($row['request_date'], $row['item_fg_id']);
        }, 'Standard Price FG periode ' . date('m/Y', strtotime((string) $records[0]['request_date'])) . ' tidak ada');
    }

    // Output Production (Shop Floor Control): qty FG + qty WIP
    private function _build_output_production($modul, $document_no)
    {
        $records = $this->_fg_records('output_productions', 'number', $document_no,
            'a.trans_date AS request_date, a.number AS request_no, a.wo_no, (COALESCE(a.qty, 0) + COALESCE(a.qty_wip, 0)) AS qty_total');
        return $this->_fg_price_entry($this->_setup_resolver($modul), $document_no, $records, 'qty_total', 'wo_no');
    }

    // Item NG: 1 baris per komponen RM, nilai pakai qty produk NG (qty_product) x harga FG, sama dengan Report Value NG
    private function _build_item_ng($modul, $document_no)
    {
        $this->db->select("a.document AS request_no, MIN(a.trans_date) AS request_date, MIN(a.workorder) AS workorder,
            CAST(a.qty_product AS DECIMAL(15,4)) AS qty_product,
            b.id AS item_fg_id, MAX(b.number) AS item_no, MAX(b.name) AS item_name, MAX(d.number) AS item_division", FALSE);
        $this->db->from('item_ng a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('divisions d', 'b.division_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.document', $document_no);
        $this->db->group_by(['a.document', 'b.id', 'a.qty_product']);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            throw new Exception("No records found for Document No: $document_no");
        }
        return $this->_fg_price_entry($this->_setup_resolver($modul), $document_no, $records, 'qty_product', 'workorder');
    }

    // WIP Receipt: hanya jika semua baris closed (INJ: semua label di-scan)
    private function _build_wip_receipt($modul, $document_no)
    {
        if (!$this->is_wip_receipt_ready($document_no)) {
            throw new Exception("WIP Receipt $document_no is not closed yet (not all labels have been scanned).");
        }
        $records = $this->_fg_records('wip_receipts', 'document_no', $document_no,
            'a.trans_date AS request_date, a.document_no AS request_no, a.wo_no, a.qty', 'a.division');
        return $this->_fg_price_entry($this->_setup_resolver($modul), $document_no, $records, 'qty', 'wo_no');
    }

    // Delivery Note: DN hanya bisa dibuat dari DO yang sudah full scan (Shipping Orders)
    private function _build_delivery_note($modul, $document_no)
    {
        $records = $this->_fg_records('delivery_notes', 'delivery_note_no', $document_no,
            'a.delivery_note_date AS request_date, a.delivery_note_no AS request_no, a.delivery_order_no, a.qty, a.customer_id', 'a.division');
        $entry = $this->_fg_price_entry($this->_setup_resolver($modul), $document_no, $records, 'qty', 'delivery_order_no');

        $customer = $this->db->select('id, name')->get_where('customers', ['id' => $records[0]['customer_id']])->row();
        if ($customer) {
            $entry['doc']['company_id']   = $customer->id;
            $entry['doc']['company_name'] = $customer->name;
        }
        return $entry;
    }

    // Transaction FG: journal type per jenis transaksi (seperti Transaction RM)
    private function _build_transaction_fg($modul, $document_no)
    {
        $records = $this->_fg_records('transaction_fg', 'request_no', $document_no,
            'a.request_date, a.request_no, a.transaction_type, a.qty');

        $transaction_types = array_values(array_unique(array_map(function ($r) { return strtoupper(trim((string) $r['transaction_type'])); }, $records)));
        if (count($transaction_types) != 1 || $transaction_types[0] === '') {
            throw new Exception("Transaction Type of Request No: $document_no is empty or not unique.");
        }
        return $this->_fg_price_entry($this->_setup_resolver($modul, $transaction_types[0]), $document_no, $records, 'qty', 'request_no');
    }

}
