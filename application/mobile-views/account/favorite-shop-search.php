<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$data = array(
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'shops' => !empty(array_filter($shops)) ? $shops : array()
);

if (empty(array_filter($shops))) {
    $status = applicationConstants::OFF;
}
