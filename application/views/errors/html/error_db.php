<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$explode = explode("|", str_replace("</p>","|", str_replace("<p>","",$message)));
echo json_encode(array(
	"title" => $heading,
	"message" => $explode[0],
	"filename" => $explode[1],
	"line" => $explode[2],
	"theme" => "error"  
));
