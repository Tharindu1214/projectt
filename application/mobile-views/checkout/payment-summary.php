<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
foreach ($paymentMethods as $key => $val) {
    $paymentMethods[$key]['image'] = CommonHelper::generateFullUrl('Image', 'paymentMethod', array($val['pmethod_id'],'SMALL'));
}

$data = array(
    'orderId' => $orderId,
    'orderType' => $orderType,
    'paymentMethods' => $paymentMethods,
);

require_once(CONF_THEME_PATH.'cart/price-detail.php');

if (empty($products)) {
    $status = applicationConstants::OFF;
}
