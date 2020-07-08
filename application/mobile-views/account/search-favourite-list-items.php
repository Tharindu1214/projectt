<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'products' => $products,
    'showProductShortDescription' => $showProductShortDescription,
    'showProductReturnPolicy' => $showProductReturnPolicy,
    'page' => $page,
    'recordCount' => $recordCount,
    'pageCount' => $pageCount,
    'postedData' => $postedData,
    'startRecord' => $startRecord,
    'endRecord' => $endRecord,
);

if (1 > $recordCount) {
    $status = applicationConstants::OFF;
}
