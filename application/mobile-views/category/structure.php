<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('categories' => $categoriesData);

if (empty($categoriesData)) {
    $status = applicationConstants::OFF;
}
