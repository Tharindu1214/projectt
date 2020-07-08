<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'messages' => $arr_listing,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'page' => $page,
    'pageSize' => $pageSize,
);

if (empty($arr_listing)) {
    $status = applicationConstants::OFF;
}
