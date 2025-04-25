<?php

class PhpSpreadsheet
{
    public function __construct()
    {
        // Memuat autoloader PhpSpreadsheet
        require_once APPPATH . 'third_party/PhpSpreadsheet/Autoloader.php';
    }
}
