<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('languages' => $languages);

if (empty($languages)) {
    $status = applicationConstants::OFF;
}
