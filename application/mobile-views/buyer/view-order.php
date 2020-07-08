<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (1 > $opId) {
    $childOrderDetail = array_values($childOrderDetail);
}

$orderDetail['charges'] = !empty($orderDetail['charges']) ? $orderDetail['charges'] : (object)array();
$orderDetail['billingAddress'] = !empty($orderDetail['billingAddress']) ? $orderDetail['billingAddress'] : (object)array();
$orderDetail['shippingAddress'] = !empty($orderDetail['shippingAddress']) ? $orderDetail['shippingAddress'] : (object)array();
$orderDetail['order_net_amount'] = !empty($orderDetail['order_net_amount']) ? CommonHelper::displayMoneyFormat($orderDetail['order_net_amount'], false, false, false) : 0;

if (!empty($orderDetail['charges'])) {
    $charges = array();
    $i = 0;
    foreach ($orderDetail['charges'] as $key => $value) {
        $charges[$key] = array_values($value);
        $i++;
    }
    $orderDetail['charges'] = $charges;
}
// echo $primaryOrder; die;
if ($primaryOrder) {
    $childArr[] = $childOrderDetail;
} else {
    $childArr = $childOrderDetail;
}
$cartTotal = 0;
$shippingCharges = 0;

$defaultOrderStatus = FatApp::getConfig('CONF_DEFAULT_REVIEW_STATUS', FatUtility::VAR_INT, 0);
$reviewAllowed = FatApp::getConfig('CONF_ALLOW_REVIEWS', FatUtility::VAR_INT, 0);

$canCancelOrder = true;
$canReturnRefund = true;

foreach ($childArr as $index => $childOrder) {
    $rating = isset($childArr[$index]['prod_rating']) ? $childArr[$index]['prod_rating'] : 0;
    $childArr[$index]['prod_rating'] =  (1 == $defaultOrderStatus || (isset($childArr[$index]['spreview_status']) && $childArr[$index]['spreview_status'] == 1 )) ? $rating : 0;
    $childArr[$index]['reviewsAllowed'] =  $reviewAllowed;
    $childArr[$index]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($childOrder['selprod_product_id'], "THUMB", $childOrder['op_selprod_id'], 0, $siteLangId));

    if ($childOrder['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
        $canCancelOrder = (in_array($childOrder["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true))) ? 1 : 0;
        $canReturnRefund = (in_array($childOrder["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses(true))) ? 1 : 0;
    } else {
        $canCancelOrder = (in_array($childOrder["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses())) ? 1 : 0;
        $canReturnRefund = (in_array($childOrder["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses())) ? 1 : 0;
    }

    $childArr[$index]['canCancelOrder'] = ($canCancelOrder && false === OrderCancelRequest::getCancelRequestById($childOrder['op_id']) ? 1 : 0);

    $childArr[$index]['canReturnOrder'] = ($canReturnRefund && $childOrder['return_request'] == 0 && $childOrder['cancel_request'] == 0 ? 1 : 0);



    $canSubmitFeedback = Orders::canSubmitFeedback($childOrder['order_user_id'], $childOrder['order_id'], $childOrder['op_selprod_id']);
    $isValidForReview = in_array($childOrder["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses());

    $childArr[$index]['canSubmitFeedback'] = ($canSubmitFeedback && $isValidForReview) ? 1 : 0;

    $cartTotal = $cartTotal + CommonHelper::orderProductAmount($childOrder, 'cart_total');
    $shippingCharges = $shippingCharges + CommonHelper::orderProductAmount($childOrder, 'shipping');
    $volumeDiscount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
    $rewardPointDiscount = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
    $orderDiscountTotal = CommonHelper::orderProductAmount($childOrder, 'DISCOUNT');

    $childArr[$index]['priceDetail'] = array(
        array(
            'key' => Labels::getLabel('LBL_Ordered_Quantity', $siteLangId),
            'value' => $childOrder['op_qty'],
        ),
        array(
            'key' => Labels::getLabel('LBL_Price', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat($childOrder['op_unit_price']),
        ),
        array(
            'key' => Labels::getLabel('LBL_Shipping_Charges', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'shipping')),
        ),
        array(
            'key' => Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat($volumeDiscount),
        ),
        array(
            'key' => Labels::getLabel('LBL_Tax_Charges', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'tax')),
        ),
        array(
            'key' => Labels::getLabel('LBL_Discount', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat($orderDiscountTotal),
        ),
        array(
            'key' => Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat($rewardPointDiscount),
        ),
    );
    $childArr[$index]['totalAmount'] = array(
        'key' => Labels::getLabel('LBL_Total', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder)),
    );

    $paymentMethodName = $childOrder['pmethod_name']?:$childOrder['pmethod_identifier'];
    if (0 < $childOrder['order_pmethod_id'] && 0 < $childOrder['order_is_wallet_selected']) {
        $paymentMethodName .= ' + ';
    }
    if (0 < $childOrder['order_is_wallet_selected']) {
        $paymentMethodName .= Labels::getLabel("LBL_Wallet", $siteLangId);
    }
    $childArr[$index]['pmethod_name'] = $paymentMethodName;

    $orderObj = new Orders($childOrder['order_id']);
    if ($childOrder['pmethod_code'] == 'CashOnDelivery') {
        $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(true);
    } else {
        $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, $childOrder['op_product_type']);
    }
}

$data = array(
    'orderDetail' => $orderDetail,
    'childOrderDetail' => $childArr,
    'orderStatuses' => !empty($orderStatuses) ? $orderStatuses : (object)array(),
    'primaryOrder' => $primaryOrder,
    'digitalDownloads' => !empty($digitalDownloads) ? $digitalDownloads : (object)array(),
    'digitalDownloadLinks' => !empty($digitalDownloadLinks) ? $digitalDownloadLinks : (object)array(),
    'languages' => !empty($languages) ? $languages : (object)array(),
    'yesNoArr' => $yesNoArr,
);
if (empty($orderDetail)) {
    $status = applicationConstants::OFF;
}
