<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($paymentMethods as $key => $val) {
    $paymentMethods[$key]['image'] = CommonHelper::generateFullUrl('Image', 'paymentMethod', array($val['pmethod_id'],'SMALL'));
}

$data = array(
    'orderId' => $order_id,
    'orderType' => $orderType,
    'paymentMethods' => $paymentMethods,
);
