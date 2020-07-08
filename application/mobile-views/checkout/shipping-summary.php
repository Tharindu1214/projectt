<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (!empty($cartSummary) && array_key_exists('cartDiscounts', $cartSummary)) {
    $cartSummary['cartDiscounts'] = !empty($cartSummary['cartDiscounts']) ? $cartSummary['cartDiscounts'] : (object)array();
}

usort($products, function ($a, $b) {
    return $a['shop_id'] - $b['shop_id'];
});

foreach ($products as $index => $product) {
    $products[$index]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId));
    $products[$index]['total'] = !empty($product['total']) ? CommonHelper::displayMoneyFormat($product['total']) : 0;
    $products[$index]['totalPrice'] = !empty($product['totalPrice']) ? CommonHelper::displayMoneyFormat($product['totalPrice'], false, false, false) : 0;
    $products[$index]['netTotal'] = !empty($product['netTotal']) ? CommonHelper::displayMoneyFormat($product['netTotal']) : 0;
    // $products[$index]['shop_free_ship_upto'] = !empty($product['shop_free_ship_upto']) ? CommonHelper::displayMoneyFormat($product['shop_free_ship_upto'], false, false, false) : 0;
    $products[$index]['productKey'] = md5($products[$index]['key']);
    $shipping_options = array(
        array(
            'title' => Labels::getLabel("LBL_Select_Shipping", $siteLangId),
            'value' => 0,
        )
    );
    if (count($product["shipping_rates"])) {
        $i = 1;
        foreach ($product["shipping_rates"] as $skey => $sval) {
            $country_code = empty($sval["country_code"]) ? "" : " (" . $sval["country_code"] . ")";
            $product["shipping_free_availbilty"];
            if ($product['shop_eligible_for_free_shipping'] > 0 && $product['psbs_user_id'] > 0) {
                $shipping_charges = Labels::getLabel('LBL_Free_Shipping', $siteLangId);
            } else {
                $shipping_charges = $product["shipping_free_availbilty"] == 0 ? "+" . CommonHelper::displayMoneyFormat($sval['pship_charges']) : 0;
            }
            $shippingDurationTitle = ShippingDurations::getShippingDurationTitle($sval, $siteLangId);
            $shipping_options[$i]['title'] =  $sval["scompany_name"] ." - " . $shippingDurationTitle . $country_code . " (" . $shipping_charges . ")";
            $shipping_options[$i]['value'] =  $sval['pship_id'];
            $i++;
        }
    }

    $shipStation = array();
    if (!empty($shipStationCarrierList)) {
        $i = 0;
        foreach ($shipStationCarrierList as $key => $value) {
            $shipStation[$i]['title'] = $value;
            $shipStation[$i]['value'] = $key;
            $i++;
        }
    }
    $newShippingMethods = $shippingMethods;
    if (2 > sizeof($shipping_options)) {
        unset($newShippingMethods[SHIPPINGMETHODS::MANUAL_SHIPPING]);
    }

    $products[$index]['shippingMethods'][] = [
        'title' => Labels::getLabel('LBL_Select_Shipping_Method', $siteLangId),
        'value' => 0,
        'rates' => []
    ];
    foreach ($newShippingMethods as $shippingMethodType => $shipingMethodtitle) {
        $shippinhMethodArr = [
            'title' => $shipingMethodtitle,
            'value' => $shippingMethodType
        ];
        switch ($shippingMethodType) {
            case ShippingMethods::MANUAL_SHIPPING:
                $shippinhMethodArr['rates'] = $shipping_options;
                break;
            case ShippingMethods::SHIPSTATION_SHIPPING:
                $shippinhMethodArr['rates'] = $shipStation;
                break;
        }
        $products[$index]['shippingMethods'][] = $shippinhMethodArr;
    }
}

$data = array(
    'products' => !empty(array_filter($products)) ? array_values($products) : array(),
    'cartSummary' => $cartSummary,
    'shippingAddressDetail' => !empty($shippingAddressDetail) && !empty(array_filter($shippingAddressDetail)) ? $shippingAddressDetail : (object)array(),
);


if (empty($products)) {
    $status = applicationConstants::OFF;
}
