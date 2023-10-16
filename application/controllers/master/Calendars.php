<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Calendars extends CI_Controller
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

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/calendars');
        } else {
            redirect('error_access');
        }
    }

    public function hkw()
    {
        $bulan = $this->input->post('month');
        $tahun = $this->input->post('year');

        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }

        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        $hkw = 0;
        for ($d = 1; $d <= $jumlahhari; $d++) {
            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('deleted', 0);
            $this->db->where('working_date', $tanggal);
            $data = $this->db->get()->result_array();

            $hkw += 1;

            if (@$data[0]['remarks'] != "") {
                $hkw -= 1;
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $hkw -= 1;
            }
        }

        echo $hkw;
    }

    public function calendars()
    {
        $bulan = $this->input->post('month');
        $tahun = $this->input->post('year');

        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }


        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));

        $html = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }

                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 10px;
                    }

                    #customers td,
                    #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                        height:50px;
                    }

                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }

                    #customers tr:hover {
                        background-color: #ddd;
                    }

                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }
                </style>
                <table id="customers" style="width: 100%;">
                    <tr>
                        <td align=center width="200">
                            <font color="#FF0000">Sunday</font>
                        </td>
                        <td align=center width="200">Monday</td>
                        <td align=center width="200">Tuesday</td>
                        <td align=center width="180">Wednesday</td>
                        <td align=center width="200">Thursday</td>
                        <td align=center width="200">Friday</td>
                        <td align=center width="200">Saturday</td>
                    </tr>';
        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        for ($ds = 1; $ds <= $s; $ds++) {
            $html .= "<td></td>";
        }

        for ($d = 1; $d <= $jumlahhari; $d++) {
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 0) {
                $html .= "<tr>";
            }

            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('deleted', 0);
            $this->db->where('working_date', $tanggal);
            $data = $this->db->get()->result_array();

            //Jika Hari Minggu
            $style = "background:white !important;";
            $checkbox = "<input hidden checked class='checked' type='checkbox' value='" . $d . "' name='days[]' style='float: left; width: 20px;'/>";
            $note = "<textarea rows='2' name='remarks[]'>" . @$data[0]['remarks'] . "</textarea>";

            if (@$data[0]['remarks'] != "") {
                $style = "background:#FFDADA !important;";
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $style = "background:#FFDADA !important;";
                $note = "<textarea rows='2' hidden name='remarks[]'></textarea>";
            }

            $html .= "  <td align=center style='" . $style . "' valign=middle>
                            $checkbox
                            <b style='font-size: 20px;'>$d</b><br>
                            $note
                        </td>";

            //Jika Sudah seminggu
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 6) {
                $html .= "</tr>";
            }
        }
        $html .= '</table>';

        echo $html;
    }

    public function create()
    {
        if ($this->input->post()) {
            $month = $this->input->post('filter_month');
            $year = $this->input->post('filter_year');
            $days = $this->input->post('days');
            $remarks = $this->input->post('remarks');

            for ($i = 0; $i < count($days); $i++) {
                $date = $year . "-" . $month . "-" . $days[$i];
                $remark = @$remarks[$i];


                $this->db->select('*');
                $this->db->from('calendars');
                $this->db->where('deleted', 0);
                $this->db->where('working_date', $date);
                $records = $this->db->get()->num_rows();

                if ($remark != "") {
                    if ($records > 0) {
                        $this->db->where('working_date', $date);
                        $this->db->update('calendars', ["remarks" => $remark]);
                    } else {
                        $send = $this->crud->create('calendars', ["working_date" => $date, "remarks" => $remark], "WORK", "WORK");
                    }
                } else {
                    if ($records > 0) {
                        $this->db->delete('calendars', ['working_date' => $date]);
                    }
                }
            }

            echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
        } else {
            show_error("Cannot Process your request");
        }
    }
}
