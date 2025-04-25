<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class M_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('emails');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function purchaseRequests($month = "", $year = "", $revision = "")
    {
        $period = $year . "-" . $month;
        $querys = $this->crud->query("SELECT a.name, coalesce(SUM(c.qty), 0) as total FROM item_familys a 
        JOIN item_rm b ON a.id = b.item_family_id
        LEFT JOIN purchase_requests c ON b.id = c.item_rm_id and c.request_date LIKE '%$period%'
        GROUP BY a.number
        HAVING SUM(c.qty) > 0");

        die(json_encode($querys));
    }

    public function purchaseOrders($month = "", $year = "")
    {
        $period = $year . "-" . $month;
        $querys = $this->crud->query("SELECT a.name, coalesce(SUM(c.qty), 0) as total FROM item_familys a 
        JOIN item_rm b ON a.id = b.item_family_id
        LEFT JOIN purchase_orders c ON b.id = c.item_rm_id and c.po_date LIKE '%$period%'
        GROUP BY a.number
        HAVING SUM(c.qty) > 0");

        die(json_encode($querys));
    }

    public function SupplierPurchaseOrders($month = "", $year = "")
    {
        $period = $year . "-" . $month;
        $querys = $this->crud->query("SELECT a.name, coalesce(SUM(b.qty), 0) as total_qty, coalesce(SUM(b.total), 0) as total_sub, a.currency as type FROM suppliers a 
        LEFT JOIN purchase_orders b ON b.supplier_id = a.id and b.po_date LIKE '%$period%'
        GROUP BY a.number
        ORDER BY coalesce(SUM(b.qty), 0) desc
        LIMIT 10");

        die(json_encode($querys));
    }
}
