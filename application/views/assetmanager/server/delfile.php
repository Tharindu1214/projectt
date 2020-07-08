<?php
include_once(dirname(dirname(__FILE__)) . "/config.php");
$defaultUploadPath = '';
require_once $_SESSION['WYSIWYGFileManagerRequirements'];

$root = WEBSITEROOT_LOCALPATH .$defaultUploadPath ;
$file = $root . $_POST["file"]; 

if(file_exists ($file)) {
	unlink($file);
} else {

}

?>