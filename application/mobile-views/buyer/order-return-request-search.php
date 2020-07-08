<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($requests as $key => $request) {
    $requests[$key]['statusName'] = array_key_exists('orrequest_status', $request) ? $OrderReturnRequestStatusArr[$request['orrequest_status']] : '';
    $requests[$key]['orrequestTypeTitle'] = array_key_exists('orrequest_type', $request) ? $returnRequestTypeArr[$request['orrequest_type']] : '';
    $requests[$key]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($request['selprod_product_id'], "THUMB", $request['op_selprod_id'], 0, $siteLangId));
}
$data = array(
    'requests' => $requests,
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'returnRequestTypeArr' => $returnRequestTypeArr,
    'OrderReturnRequestStatusArr' => $OrderReturnRequestStatusArr,
);
if (empty($requests)) {
    $status = applicationConstants::OFF;
}
