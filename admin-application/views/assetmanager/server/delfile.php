<?php
include_once(dirname(dirname(__FILE__)) . "/config.php");
$path_for_images = '';
require_once $_SESSION['WYSIWYGFileManagerRequirements'];

$root = WEBSITEROOT_LOCALPATH .$path_for_images ;
$file = $root . $_POST["file"]; 

if(file_exists ($file)) {
	unlink($file);
} else {

}

?>