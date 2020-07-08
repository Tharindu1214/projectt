<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'threads' => array_values($arrListing),
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'page' => $page,
    'pageSize' => $pageSize,
);

if (empty($arrListing)) {
    $status = applicationConstants::OFF;
}
