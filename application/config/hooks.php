<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_controller'][] = array(
	'class'    => 'Security_hook',
	'function' => 'validate_uploads',
	'filename' => 'Security_hook.php',
	'filepath' => 'hooks'
);
