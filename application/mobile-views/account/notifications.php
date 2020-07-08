<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($notifications as &$value) {
    $value['unotification_data'] = !empty($value['unotification_data']) ? json_decode($value['unotification_data'], true) : (object)array();
}

$data = array(
    'notifications' => $notifications,
    'total_pages' => $total_pages,
    'total_records' => $total_records,
);

if (empty($notifications)) {
    $status = applicationConstants::OFF;
}
