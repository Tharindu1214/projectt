<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'allShops' => array_values($allShops),
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'page' => $page,
    'pageSize' => $pageSize,
);

if (1 > count((array)$allShops)) {
    $status = applicationConstants::OFF;
}
