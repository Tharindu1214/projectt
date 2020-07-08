<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = empty($data) ? array() : $data;
$data = array_merge($commonData, $data);

if (applicationConstants::ON != $status) {
    $msg = Labels::getLabel('MSG_No_record_found', $siteLangId);
    $status = applicationConstants::OFF;
}

$response = array(
    'status'=> $status,
    'msg' => !empty($msg) ? $msg : Labels::getLabel('MSG_Success', $siteLangId),
    'data' => $data
);

CommonHelper::jsonEncodeUnicode($response, true);
