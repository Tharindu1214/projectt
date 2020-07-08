<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$data = array(
    'messagesList' => array_values($messagesList),
    'page' => $page,
    'pageCount' => $pageCount,
    'totalRecords' => $totalRecords,
);

if (empty($messagesList)) {
    $status = applicationConstants::OFF;
}
