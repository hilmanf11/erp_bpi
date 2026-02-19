<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Output $output
 * @property Crud $crud
 * @property Convertcurrency $convertcurrency
 */
class Ecoretax_reference extends CI_Controller
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
    
    function getKeteranganData() 
    {
        $faktur_kode = $this->input->get('faktur_kode') ?? null;
        $data = [];

        $data_07 = [
            ['value' => 'TD.00501', 'description' => '1 - untuk Kawasan Bebas'],
            ['value' => 'TD.00502', 'description' => '2 - untuk Tempat Penimbunan Berikat'],
            ['value' => 'TD.00503', 'description' => '3 - untuk Hibah dan Bantuan Luar Negeri'],
            ['value' => 'TD.00504', 'description' => '4 - untuk Avtur'],
            ['value' => 'TD.00505', 'description' => '5 - untuk Lainnya'],
            ['value' => 'TD.00506', 'description' => '6 - untuk Kontraktor Perjanjian Karya Pengusahaan Pertambangan Batubara Generasi I'],
            ['value' => 'TD.00507', 'description' => '7 - untuk Penyerahan bahan bakar minyak untuk Kapal Angkutan Laut'],
            ['value' => 'TD.00508', 'description' => '8 - untuk Penyerahan jasa kena pajak terkait alat angkutan tertentu'],
            ['value' => 'TD.00509', 'description' => '9 - untuk Penyerahan BKP Tertentu di KEK'],
            ['value' => 'TD.00510', 'description' => '10 - untuk BKP tertentu yang bersifat strategis berupa anode slime'],
            ['value' => 'TD.00511', 'description' => '11 - untuk Penyerahan alat angkutan tertentu dan/atau Jasa Kena Pajak terkait alat angkutan tertentu'],
            ['value' => 'TD.00512', 'description' => '12 - untuk Penyerahan kepada Kontraktor Kerja Sama Migas yang mengikuti ketentuan Peraturan Pemerintah Nomor 27 Tahun 2017'],
            ['value' => 'TD.00513', 'description' => '13 - Penyerahan Rumah Tapak dan Satuan Rumah Susun Rumah Susun Ditanggung Pemerintah Tahun Anggaran 2025'],
            ['value' => 'TD.00514', 'description' => '14 - Penyerahan Jasa Sewa Ruangan atau Bangunan Kepada Pedagang Eceran yang Ditanggung Pemerintah Tahun Anggaran 2021'],
            ['value' => 'TD.00515', 'description' => '15 - Penyerahan Barang dan Jasa Dalam Rangka Penanganan Pandemi COVID-19 (PMK 239/PMK. 03/2020)'],
            ['value' => 'TD.00516', 'description' => '16 - Insentif PMK-103/PMK.010/2021 berupa PPN atas Penyerahan Rumah Tapak dan Unit Hunian Rumah Susun yang Ditanggung Pemerintah Tahun Anggaran 2021'],
            ['value' => 'TD.00517', 'description' => '17 - Kawasan Ekonomi Khusus PP nomor 40 Tahun 2021'],
            ['value' => 'TD.00518', 'description' => '18 - Kawasan Bebas PP nomor 41 Tahun 2021'],
            ['value' => 'TD.00519', 'description' => '19 - Penyerahan Rumah Tapak dan Unit Hunian Rumah Susun yang Ditanggung Pemerintah Tahun Anggaran 2022'],
            ['value' => 'TD.00520', 'description' => '20 - PPN Ditanggung Pemerintah dalam rangka Penanganan Pandemi Corona Virus'],
            ['value' => 'TD.00521', 'description' => '21 - Penyerahan kepada Kontraktor Kerja Sama Migas yang mengikuti ketentuan Peraturan Pemerintah Nomor 53 Tahun 2017'],
            ['value' => 'TD.00522', 'description' => '22 - BKP strategis tertentu dalam bentuk anode slime dan emas butiran'],
            ['value' => 'TD.00523', 'description' => '23 - untuk penyerahan kertas koran dan/atau majalah'],
            ['value' => 'TD.00524', 'description' => '24 - PPN Ditanggung Pemerintah'],
            ['value' => 'TD.00525', 'description' => '25 - BKP dan JKP tertentu'],
            ['value' => 'TD.00526', 'description' => '26 - Penyerahan BKP dan JKP di Ibu Kota Negara baru'],
            ['value' => 'TD.00527', 'description' => '27 - Penyerahan kendaraan listrik berbasis baterai'],
            ['value' => 'TD.00528', 'description' => '28 - Insentif Tambahan Penyerahan Rumah Tapak dan Satuan Rumah Susun Rumah Susun Ditanggung Pemerintah Tahun Anggaran 2025'],
            ['value' => 'TD.00529', 'description' => '29 - PPN atas Penyerahan Hewan Khusus Tertentu Berupa Kuda serta Perlengkapan Pendukungnya Pemerintah Tahun Anggaran 2025'],
            ['value' => 'TD.00530', 'description' => '30 - PPN atas Penyerahan Bekal Khusus Operasi Tertentu Yang Ditanggung Pemerintah Tahun Anggaran 2025'],
            ['value' => 'TD.00531', 'description' => '31 - Penyerahan Rumah Tapak dan Satuan Rumah Susun Rumah Susun Ditanggung Pemerintah Tahun Anggaran 2026'],
        ];

        $data_08 = [
            ['value' => 'TD.00501', 'description' => '1 - untuk BKP dan JKP Tertentu'],
            ['value' => 'TD.00502', 'description' => '2 - untuk BKP Tertentu yang Bersifat Strategis'],
            ['value' => 'TD.00503', 'description' => '3 - untuk Jasa Kebandarudaraan'],
            ['value' => 'TD.00504', 'description' => '4 - untuk Lainnya'],
            ['value' => 'TD.00505', 'description' => '5 - untuk BKP Tertentu yang Bersifat Strategis sesuai PP'],
            ['value' => 'TD.00506', 'description' => '6 - untuk Penyerahan Jasa Kepelabuhan Tertentu untuk kegiatan angkutan laut Luar Negeri'],
            ['value' => 'TD.00507', 'description' => '7 - untuk Penyerahan Air Bersih'],
            ['value' => 'TD.00508', 'description' => '8 - Penyerahan BKP tertentu yang bersifat strategis'],
            ['value' => 'TD.00509', 'description' => '9 - Penyerahan kepada Perwakilan Negara Asing dan Badan'],
            ['value' => 'TD.00510', 'description' => '10 - BKP dan JKP tertentu'],
        ];

        if ($faktur_kode === '08') {
            $data = $data_08;
        } else {
            $data = $data_07;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    function getCapFasilitasData() 
    {
        $faktur_kode = $this->input->get('faktur_kode') ?? null;
        $data = [];

        $data_07 = [
            ['value' => 'TD.01101', 'description' => '1 - Pajak Pertambahan Nilai Tidak Dipungut berdasarkan PP Nomor 10 Tahun 2012'],
            ['value' => 'TD.01102', 'description' => '2 - Pajak Pertambahan Nilai atau Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah tidak dipungut'],
            ['value' => 'TD.01103', 'description' => '3 - Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah Tidak Dipungut'],
            ['value' => 'TD.01104', 'description' => '4 - Pajak Pertambahan Nilai Tidak Dipungut Sesuai PP Nomor 71 Tahun'],
            ['value' => 'TD.01105', 'description' => '5 - (Tidak ada Cap)'],
            ['value' => 'TD.01106', 'description' => '6 - PPN dan/atau PPnBM tidak dipungut berdasarkan PMK No. 194/PMK.03/2012'],
            ['value' => 'TD.01107', 'description' => '7 - PPN Tidak Dipungut Berdasarkan PP Nomor 15 Tahun 2015'],
            ['value' => 'TD.01108', 'description' => '8 - PPN Tidak Dipungut Berdasarkan PP Nomor 69 Tahun 2015'],
            ['value' => 'TD.01109', 'description' => '9 - PPN Tidak Dipungut Berdasarkan PP Nomor 96 Tahun 2015'],
            ['value' => 'TD.01110', 'description' => '10 - PPN Tidak Dipungut Berdasarkan PP Nomor 106 Tahun 2015'],
            ['value' => 'TD.01111', 'description' => '11 - PPN Tidak Dipungut Sesuai PP Nomor 50 Tahun 2019'],
            ['value' => 'TD.01112', 'description' => '12 - PPN atau PPN dan PPnBM Tidak Dipungut Sesuai Dengan PP Nomor 27 Tahun 2017'],
            ['value' => 'TD.01113', 'description' => '13 - PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 13 TAHUN 2025'],
            ['value' => 'TD.01114', 'description' => '14 - PPN DITANGGUNG PEMERINTAH EKS PMK 102/PMK.010/2021'],
            ['value' => 'TD.01115', 'description' => '15 - PPN DITANGGUNG PEMERINTAH EKS PMK 239/PMK.03/2020'],
            ['value' => 'TD.01116', 'description' => '16 - Insentif PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 103/PMK.010/2021'],
            ['value' => 'TD.01117', 'description' => '17 - PAJAK PERTAMBAHAN NILAI TIDAK DIPUNGUT BERDASARKAN PP NOMOR 40 TAHUN 2021'],
            ['value' => 'TD.01118', 'description' => '18 - PAJAK PERTAMBAHAN NILAI TIDAK DIPUNGUT BERDASARKAN PP NOMOR 41 TAHUN 2021'],
            ['value' => 'TD.01119', 'description' => '19 - PPN DITANGGUNG PEMERINTAH EKS PMK 6/PMK.010/2022'],
            ['value' => 'TD.01120', 'description' => '20 - PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 226/PMK.03/2021'],
            ['value' => 'TD.01121', 'description' => '21 - PPN ATAU PPN DAN PPnBM TIDAK DIPUNGUT SESUAI DENGAN PP NOMOR 53 TAHUN 2017'],
            ['value' => 'TD.01122', 'description' => '22 - PPN tidak dipungut berdasarkan PP Nomor 70 Tahun 2021'],
            ['value' => 'TD.01123', 'description' => '23 - PPN ditanggung Pemerintah Ex PMK-125/PMK.01/2020'],
            ['value' => 'TD.01124', 'description' => '24 - (Tidak ada Cap)'],
            ['value' => 'TD.01125', 'description' => '25 - PPN tidak dipungut berdasarkan PP Nomor 49 Tahun 2022'],
            ['value' => 'TD.01126', 'description' => '26 - PPN tidak dipungut berdasarkan PP Nomor 12 Tahun 2023'],
            ['value' => 'TD.01127', 'description' => '27 - PPN Ditanggung Pemerintah berdasarkan PMK Nomor 12 Tahun 2025'],
            ['value' => 'TD.01128', 'description' => '28 - PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 60 TAHUN 2025'],
            ['value' => 'TD.01129', 'description' => '29 - PPN DITANGGUNG PEMERINTAH BERDASARKAN PMK NOMOR 61 TAHUN 2025'],
            ['value' => 'TD.01130', 'description' => '30 - PPN DITANGGUNG PEMERINTAH BERDASARKAN PMK NOMOR 44 TAHUN 2025'],
            ['value' => 'TD.01131', 'description' => '31 - PPN DITANGGUNG PEMERINTAH BERDASARKAN PMK NOMOR 90 TAHUN 2025'],
        ];

        $data_08 = [
            ['value' => 'TD.01101', 'description' => '1 - PPN Dibebaskan Sesuai PP Nomor 146 Tahun 2000 Sebagaimana Telah Diubah Dengan PP Nomor 38 Tahun 2003'],
            ['value' => 'TD.01102', 'description' => '2 - PPN Dibebaskan Sesuai PP Nomor 12 Tahun 2001 Sebagaimana Telah Beberapa Kali Diubah Terakhir Dengan PP Nomor 31 Tahun 2007'],
            ['value' => 'TD.01103', 'description' => '3 - PPN dibebaskan berdasarkan Peraturan Pemerintah Nomor 28 Tahun 2009'],
            ['value' => 'TD.01104', 'description' => '4 - (Tidak ada cap)'],
            ['value' => 'TD.01105', 'description' => '5 - PPN Dibebaskan Sesuai Dengan PP Nomor 81 Tahun 2015'],
            ['value' => 'TD.01106', 'description' => '6 - PPN Dibebaskan Berdasarkan PP Nomor 74 Tahun 2015'],
            ['value' => 'TD.01107', 'description' => '7 - (tanpa cap)'],
            ['value' => 'TD.01108', 'description' => '8 - PPN DIBEBASKAN SESUAI PP NOMOR 81 TAHUN 2015 SEBAGAIMANA TELAH DIUBAH DENGAN PP 48 TAHUN 2020'],
            ['value' => 'TD.01109', 'description' => '9 - PPN DIBEBASKAN BERDASARKAN PP NOMOR 47 TAHUN 2020'],
            ['value' => 'TD.01110', 'description' => '10 - PPN Dibebaskan berdasarkan PP Nomor 49 Tahun 2022'],
        ];

        if ($faktur_kode === '07') {
            $data = $data_07;
        } elseif ($faktur_kode === '08') {
            $data = $data_08;
        } else {
            // jika selain 07 dan 08, maka TD.01105 ini (Bu Nina)
            $data = [
                ['value' => 'TD.01105', 'description' => '5 - (Tidak ada Cap)']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
}