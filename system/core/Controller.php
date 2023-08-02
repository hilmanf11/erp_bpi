<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller
{

	/**
	 * Reference to the CI singleton
	 *
	 * @var	object
	 */
	private static $instance;

	/**
	 * CI_Loader
	 *
	 * @var	CI_Loader
	 */
	public $load;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		self::$instance = &$this;

		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CI can run as one big super object.
		foreach (is_loaded() as $var => $class) {
			$this->$var = &load_class($class);
		}

		$this->load = &load_class('Loader', 'core');
		$this->load->initialize();
		log_message('info', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Get the CI singleton
	 *
	 * @static
	 * @return	object
	 */
	public static function &get_instance()
	{
		return self::$instance;
	}

	//Get Menu ID
	public function id_menu()
	{
		return base64_decode($this->uri->segment(4));
	}

	//Ambil Data untuk button access
	public function getbutton($menu)
	{
		$username = $this->session->username;
		$query  = $this->db->query("SELECT a.* FROM setting_users a JOIN users b ON a.users_id = b.username WHERE a.menus_id='$menu' and b.username='$username'");
		$data   = $query->row();

		$html   = '';
		if (@$data->v_add == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add()"><i class="fa fa-plus"></i> <span id="add">Add</span></a>';
		}
		if (@$data->v_edit == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update()"><i class="fa fa-edit"></i> Update</a>';
		}
		if (@$data->v_delete == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted()"><i class="fa fa-trash"></i> Delete</a>';
		}
		if (@$data->v_upload == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="upload()"><i class="fa fa-upload"></i> Upload</a>';
		}
		if (@$data->v_download == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="download_excel()"><i class="fa fa-download"></i> Download Template</a>';
		}
		if (@$data->v_print == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="pdf()"><i class="fa fa-print"></i> Print</a>';
		}
		if (@$data->v_excel == "1") {
			$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel()"><i class="fa fa-file"></i> Export Excel</a>';
		}
		$html .= '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>';

		return $html;
	}

	//Check User Access Menu
	public function checkuserAccess($id_menu)
	{
		if ($id_menu != "") {
			$username = $this->session->username;
			$this->db->select('v_view');
			$this->db->from('setting_users');
			$this->db->where('users_id', $username);
			$this->db->where('menus_id', $id_menu);
			$checkuserAccess = $this->db->get()->result_array();
			return @$checkuserAccess[0]['v_view'];
		} else {
			return 0;
		}
	}

	public function createQrcode($filename, $path, $name = "")
	{
		$config['cacheable']    = true; //boolean, the default is true
		$config['cachedir']     = './assets/'; //string, the default is application/cache/
		$config['errorlog']     = './assets/'; //string, the default is application/logs/
		$config['imagedir']     = $path; //direktori penyimpanan qr code
		$config['quality']      = true; //boolean, the default is true
		$config['size']         = '1024'; //interger, the default is 1024
		$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
		$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
		$this->ciqrcode->initialize($config);

		if($name == ""){
			$image_name = $filename . '.png'; //buat name dari qr code sesuai dengan nim
		}else{
			$image_name = $name . '.png'; //buat name dari qr code sesuai dengan nim
		}
		
		$params['data'] = $filename; //data yang akan di jadikan QR CODE
		$params['level'] = 'H'; //H=High
		$params['size'] = 10;
		$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
		$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
	}
}
