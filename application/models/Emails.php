<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Emails extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    function index()
    {
        show_404();
    }

    public function emailRegistration($email, $name = "USER", $username, $password)
    {
        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'no-reply@aeconsys.com',
            'smtp_pass' => 'Hilman1196',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        $this->email->initialize($config);
        $this->email->from('no-reply@aeconsys.com', 'BANSHU ELECTRIC INDONESIA');
        $this->email->to($email);
        $this->email->subject('Registration ERP System');
        $this->email->message('<!doctype html>
        <html>
        
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>Simple Transactional Email</title>
            <style>
                body {
                    background: #EFEFEF;
                    font-family: Montserrat, Helvetica, Arial, serif;
                    font-size: 12px;
                }
            </style>
        </head>
        
        <body>
            <div style="background: #EFEFEF; width: 100%;">
                <div style="width: 100%; padding-top:100px; padding-bottom:100px;">
                    <center>
                        <h1>ERP System</h1>
            
                        <div style="width: 60%; background: white; border-radius: 20px; padding:50px; text-align: left;">
                            <h3>Hi<br><span style="color:#FF6060"> ' . $name . ' </span></h3>
                            <h3>Your email for signing up!</h3>
                            <p>You have been registered as a new user in the enterprise resource planning application at Banshu Electric Indonesia. please use the following account to enter the application</p>
                            <table>
                                <tr>
                                    <td width="50">Username</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . $username . '</b></td>
                                </tr>
                                <tr>
                                    <td width="50">Password</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . $password . '</b></td>
                                </tr>
                            </table>
                            <p>If you are having username and password problems, please contact your IT Dept</p>
                            <br><br><br>
                            <br><br><br><br><br><br>
            
                            <p>Regards,</p>
                            <p><b>Banshu Electric Indonesia</b></p>
                        </div>
                    </center>
                </div>
            </div>
        </body>
        
        </html>');
        $this->email->send();
    }
}
