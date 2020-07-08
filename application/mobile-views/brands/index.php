<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'layoutDirection'=>$layoutDirection,
    'allBrands'=>$allBrands,
);

if (empty($allBrands)) {
    $status = applicationConstants::OFF;
}
