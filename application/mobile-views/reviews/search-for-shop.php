<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$data = array(
    'reviewsList' => $reviewsList,
    'page' => $page,
    'pageCount' => $pageCount,
    'postedData' => $postedData,
    'startRecord' => $startRecord,
    'totalRecords' => $totalRecords,
);

if (empty($reviewsList)) {
    $status = applicationConstants::OFF;
}
