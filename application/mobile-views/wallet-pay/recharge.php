<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($paymentMethods as $key => $val) {
    $paymentMethods[$key]['image'] = CommonHelper::generateFullUrl('Image', 'paymentMethod', array($val['pmethod_id'],'SMALL'));
}

$orderNetAmount = (!empty($orderInfo['order_net_amount']) && 0 < $orderInfo['order_net_amount'] ? $orderInfo['order_net_amount'] : 0);
$data = array(
    'paymentMethods' => $paymentMethods,
    // 'orderInfo' => $orderInfo,
    'order_type' => $orderInfo['order_type'],
    'orderNetAmount' => $orderNetAmount
);
$data['netPayable'] = array(
    'key' => Labels::getLabel('LBL_Net_Payable', $siteLangId),
    'value' => CommonHelper::displayMoneyFormat($orderNetAmount)
);

if (empty(array_filter($paymentMethods)) || empty(array_filter($orderInfo))) {
    $status = applicationConstants::OFF;
}
