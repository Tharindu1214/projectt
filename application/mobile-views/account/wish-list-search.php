<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('wishLists' => $wishLists);

if (empty($wishLists)) {
    $status = applicationConstants::OFF;
}
