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
 * Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model
{

	/**
	 * Class constructor
	 *
	 * @link	https://github.com/bcit-ci/CodeIgniter/issues/5332
	 * @return	void
	 */
	public function __construct()
	{
	}

	/**
	 * __get magic
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string	$key
	 */
	public function __get($key)
	{
		// Debugging note:
		//	If you're here because you're getting an error message
		//	saying 'Undefined Property: system/core/Model.php', it's
		//	most likely a typo in your model code.
		return get_instance()->$key;
	}

	public function autonumber($table, $string, $autonumber)
	{
		if ($autonumber == "") {
			$sqlGetID	= $this->db->query("SELECT max(id) as kode FROM $table");
			$rowID      = $sqlGetID->row();
			$kode 		= $rowID->kode;

			if ($kode == NULL) {
				$autoID		= sprintf("%04s", $kode + 1);
			} else {
				$urutan 	= (int) substr($kode, -4);
				$urutan++;
				$autoID		= sprintf("%04s", $urutan);
			}

			return $string . "-" . $autoID;
		} elseif ($autonumber == "DAY") {
			$date 		= date("Ymd");
			$sqlGetID	= $this->db->query("SELECT max(id) as kode FROM $table WHERE id like '%$date%'");
			$rowID      = $sqlGetID->row();
			$kode 		= $rowID->kode;

			if ($kode == NULL) {
				$autoID		= $date . sprintf("%04s", $kode + 1);
			} else {
				$urutan 	= (int) substr($kode, -12);
				$autoID		= sprintf("%04s", $urutan + 1);
			}

			return $string . "-" . $autoID;
		} elseif ($autonumber == "JUTA") {
			$date 		= date("Ymd");
			$autoID  	= 0;
			$sqlGetID	= $this->db->query("SELECT max(id) as kode FROM $table WHERE id like '%$date%'");
			$rowID      = $sqlGetID->row();
			$kode 		= $rowID->kode;

			if ($kode == NULL) {
				$autoID		+= 1;
			} else {
				$urutan 	= (int) substr($kode, 8);
				$autoID		+= $urutan + 1;
			}

			return $date . $autoID;
		} else {
			$date 		= date("Ym");
			$sqlGetID	= $this->db->query("SELECT max(id) as kode FROM $table WHERE id like '%$date%'");
			$rowID      = $sqlGetID->row();
			$kode 		= $rowID->kode;

			if ($kode == NULL) {
				$autoID		= $date . sprintf("%04s", $kode + 1);
			} else {
				$urutan 	= (int) substr($kode, -10);
				$autoID		= sprintf("%04s", $urutan + 1);
			}

			return $string . "-" . $autoID;
		}
	}

	public function api_key($api)
	{
		$sql = $this->db->query("SELECT username, api_key FROM user WHERE api_key = '$api' and deleted_is = 0");
		$dataRows = $sql->result_array();
		$totalRows = $sql->num_rows();

		if ($totalRows > 0) {
			return $dataRows;
			return true;
		} else {
			show_error("Your API Keys is not valid");
			exit;
		}
	}
}
