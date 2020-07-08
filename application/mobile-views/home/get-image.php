<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('image_url' => $image_url);

if (empty($image_url)) {
    $status = applicationConstants::OFF;
}
