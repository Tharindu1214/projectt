<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('file'=>$file);

if (empty($file)) {
    $status = applicationConstants::OFF;
}
