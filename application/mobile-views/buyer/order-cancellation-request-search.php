<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($requests as $key => $request) {
    $requests[$key]['statusName'] = array_key_exists('ocrequest_status', $request) ? $OrderCancelRequestStatusArr[$request['ocrequest_status']] : '';
    $requests[$key]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($request['selprod_product_id'], "THUMB", $request['op_selprod_id'], 0, $siteLangId));
}

$data = array(
    'requests' => $requests,
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'OrderCancelRequestStatusArr' => $OrderCancelRequestStatusArr
);

if (empty($requests)) {
    $status = applicationConstants::OFF;
}
