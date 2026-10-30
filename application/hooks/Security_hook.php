<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Security_hook
{
    private static $dangerous_exts = array(
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phpt', 'phps',
        'pht', 'phtm', 'phtml', 'phar', 'pgif',
        'inc', 'pl', 'py', 'cgi', 'sh', 'bash', 'exe', 'bat', 'cmd', 'dll',
        'jsp', 'asp', 'aspx', 'htm', 'html', 'shtm', 'shtml', 'xhtml', 'svg', 'js', 'vbs',
        'htaccess', 'htpasswd', 'ini'
    );

    public function validate_uploads()
    {
        if (empty($_FILES)) {
            return;
        }

        // Register auto-cleanup in case any controller temporarily saves to root
        register_shutdown_function(array($this, 'cleanup_root_leftovers'));

        foreach ($_FILES as $field => $file) {
            if (empty($file['name'])) {
                continue;
            }

            $names     = is_array($file['name']) ? $file['name'] : array($file['name']);
            $tmp_names = is_array($file['tmp_name']) ? $file['tmp_name'] : array($file['tmp_name']);

            foreach ($names as $idx => $name) {
                $tmp = (isset($tmp_names[$idx]) && is_string($tmp_names[$idx])) ? $tmp_names[$idx] : '';

                // 0. Reject nested arrays (name[a][b]) instead of letting strpos() throw TypeError
                if (!is_string($name)) {
                    $this->abort_upload($tmp, 'Invalid upload field structure');
                }

                if ($name === '') {
                    continue;
                }

                // 1. Block null byte injection
                if (strpos($name, "\0") !== false) {
                    $this->abort_upload($tmp, 'Null byte character detected in filename');
                }

                // 2. Block NTFS alternate data streams (e.g. shell.php::$DATA)
                if (strpos($name, ':') !== false) {
                    $this->abort_upload($tmp, 'Colon character is not permitted in filename');
                }

                // Windows strips trailing dots/spaces on save (shell.php. => shell.php)
                $normalized = rtrim($name, " .\t\r\n");

                $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));

                // 3. Block dangerous executable extensions unconditionally
                if (in_array($ext, self::$dangerous_exts, true)) {
                    $this->abort_upload($tmp, 'File extension .' . $ext . ' is not permitted');
                }

                // 4. Block double extension attacks (e.g. name.php.xls)
                $parts = explode('.', $normalized);
                if (count($parts) > 2) {
                    for ($i = 1; $i < count($parts) - 1; $i++) {
                        if (in_array(strtolower(trim($parts[$i])), self::$dangerous_exts, true)) {
                            $this->abort_upload($tmp, 'Double extension attack detected');
                        }
                    }
                }

                // 5. Strict whitelist for Excel import fields ('file_upload')
                if ($field === 'file_upload') {
                    $allowed_excel = array('xls', 'xlsx', 'csv');
                    if ($normalized !== $name || !in_array($ext, $allowed_excel, true)) {
                        $this->abort_upload($tmp, 'Only .xls, .xlsx, and .csv files are permitted for import');
                    }
                }
            }
        }
    }

    public function cleanup_root_leftovers()
    {
        if (empty($_FILES['file_upload']['name'])) {
            return;
        }

        $name = is_array($_FILES['file_upload']['name']) ? reset($_FILES['file_upload']['name']) : $_FILES['file_upload']['name'];
        if (!is_string($name)) {
            return;
        }

        $base = basename($name);
        if ($base === '' || $base === '.' || $base === '..') {
            return;
        }

        // CWD may change inside shutdown functions (e.g. to "/" under Apache/FPM),
        // which breaks relative paths and trips open_basedir. Use the web root explicitly.
        $file = FCPATH . $base;
        if (!is_file($file)) {
            return;
        }

        // Only remove files written during this request, so a crafted upload name
        // cannot be used to delete pre-existing files in the web root.
        clearstatcache(true, $file);
        $started = isset($_SERVER['REQUEST_TIME']) ? (int) $_SERVER['REQUEST_TIME'] : time();
        if (@filemtime($file) >= $started) {
            @unlink($file);
        }
    }

    private function abort_upload($tmp_path, $reason)
    {
        if (!empty($tmp_path) && file_exists($tmp_path)) {
            @unlink($tmp_path);
        }

        log_message('error', 'Security_hook upload blocked: ' . $reason);

        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'status'  => false,
            'title'   => 'Security Alert',
            'message' => 'Upload rejected: ' . $reason
        ));
        exit;
    }
}
