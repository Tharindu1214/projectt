<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$statusArr = array(
    'status'=> 1,
    'msg' => !empty($msg) ? $msg : Labels::getLabel('MSG_Success', $siteLangId)
);

foreach ($orders as $index => $orderProduct) {
    $orders[$index]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($orderProduct['selprod_product_id'], "THUMB", $orderProduct['op_selprod_id'], 0, $siteLangId));
}
$data = array(
    'orders' => $orders,
    'page' => $page,
    'pageCount' => $pageCount,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount
);
if (1 > count((array)$orders)) {
    $statusArr['status'] = 0;
    $statusArr['msg'] = Labels::getLabel('MSG_No_record_found', $siteLangId);
}
