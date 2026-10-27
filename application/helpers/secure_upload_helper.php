<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('secure_excel_upload')) {
    /**
     * Staging aman file upload Excel ke direktori temporary non-public dengan nama acak.
     * Mengembalikan array ['status' => bool, 'path' => string, 'filename' => string, 'message' => string]
     *
     * @param string $field Nama input file di $_FILES
     * @param array $allowed_exts Ekstensi yang diizinkan (default: xls, xlsx, csv)
     * @param int $max_size Ukuran maksimal dalam byte (default: 20MB)
     * @return array
     */
    function secure_excel_upload($field = 'file_upload', $allowed_exts = array('xls', 'xlsx', 'csv'), $max_size = 20971520)
    {
        if (empty($_FILES[$field]['name']) || empty($_FILES[$field]['tmp_name'])) {
            return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'No file uploaded');
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'Upload error code: ' . $file['error']);
        }

        if ($file['size'] > $max_size) {
            return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'File size exceeds maximum allowed limit (20MB)');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts, true)) {
            return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'Invalid file extension: .' . $ext);
        }

        // Validate MIME type
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            if (PHP_VERSION_ID < 80100) finfo_close($finfo); // no-op sejak PHP 8.1, deprecated di PHP 8.5

            $valid_mimes = array(
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-office',
                'application/octet-stream',
                'text/csv',
                'text/plain',
                'application/zip',
                'application/x-zip-compressed'
            );

            if (!in_array($mime, $valid_mimes, true)) {
                return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'Invalid MIME type: ' . $mime);
            }
        }

        // Simpan ke direktori temporary OS dengan nama hash acak (TIDAK di web root!)
        $safe_name = 'erp_xls_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $dest_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $safe_name;

        if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
            return array('status' => false, 'path' => '', 'filename' => '', 'message' => 'Failed to move file to temporary storage');
        }

        chmod($dest_path, 0600); // Hanya dapat dibaca/tulis oleh proses web server

        return array(
            'status'   => true,
            'path'     => $dest_path,
            'filename' => $safe_name,
            'message'  => 'Upload successful'
        );
    }
}
