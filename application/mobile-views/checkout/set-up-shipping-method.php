<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId(true));

$canBeUsed = min(min($rewardPoints, CommonHelper::convertCurrencyToRewardPoint($cartSummary['cartTotal']-$cartSummary["cartDiscounts"]["coupon_discount_total"])), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));

$rewardPointsDetail = array(
    'canBeUsed' => $canBeUsed,
    'balance' => $rewardPoints,
    'convertedValue' => CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($canBeUsed)),
);

$data = array(
    'rewardPointsDetail' => $rewardPointsDetail,
    'cartSummary' => $cartSummary,
    'userWalletBalance' => $userWalletBalance
);

$deliveryCharges = isset($cartSummary['originalShipping']) ? $cartSummary['shippingTotal'] : 0;
$cartTaxTotal = isset($cartSummary['cartTaxTotal']) ? $cartSummary['cartTaxTotal'] : 0;
$cartVolumeDiscount = isset($cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0;
$discount = isset($cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0;
$rewardPointDiscount = isset($cartSummary['cartRewardPoints']) ? $cartSummary['cartRewardPoints'] : 0;
$netPayable = isset($cartSummary['orderNetAmount']) ? $cartSummary['orderNetAmount'] : 0;

$data['priceDetail'] = array(
    array(
        'key' => Labels::getLabel('LBL_Items', $siteLangId),
        'value' => $recordCount
    ),
    array(
        'key' => Labels::getLabel('LBL_Sub_Total', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartSummary['cartTotal'])
    )
);

if ($cartSummary['originalShipping']) {
    $data['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Delivery_Charges', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($deliveryCharges)
    );
}
if (0 < $cartTaxTotal) {
    $data['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Tax', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartTaxTotal)
    );
}
if (0 < $cartVolumeDiscount) {
    $data['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Volume_Discount', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartVolumeDiscount)
    );
}
if (0 < $discount) {
    $data['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Discount', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($discount)
    );
}
if (0 < $rewardPointDiscount) {
    $data['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Discount', $siteLangId),
        'value' => CommonHelper::convertRewardPointToCurrency($rewardPointDiscount)
    );
}

$data['netPayable'] = array(
    'key' => Labels::getLabel('LBL_Net_Payable', $siteLangId),
    'value' => CommonHelper::displayMoneyFormat($netPayable)
);
