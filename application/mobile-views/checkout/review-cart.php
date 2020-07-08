<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($products as $key => $product) {
    $products[$key]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId));
    $products[$key]['total'] = !empty($product['total']) ? CommonHelper::displayMoneyFormat($product['total']) : 0;
    $products[$key]['totalPrice'] = !empty($product['totalPrice']) ? CommonHelper::displayMoneyFormat($product['totalPrice'], false, false, false) : 0;
    $products[$key]['netTotal'] = !empty($product['netTotal']) ? CommonHelper::displayMoneyFormat($product['netTotal']) : 0;
    $products[$key]['shop_free_ship_upto'] = !empty($product['shop_free_ship_upto']) ? CommonHelper::displayMoneyFormat($product['shop_free_ship_upto'], false, false, false) : 0;
    $products[$key]['selectedProductShippingMethod'] = !empty($selectedProductShippingMethod['product'][$product['selprod_id']]) ? $selectedProductShippingMethod['product'][$product['selprod_id']] : (object)array();

    $optionTitle = '';
    if (is_array($product['options']) && count($product['options'])) {
        foreach ($product['options'] as $op) {
            $optionTitle .= $op['option_name'].': '.$op['optionvalue_name'].', ';
        }
    }
    $products[$key]['optionsTitle'] = rtrim($optionTitle, ', ');
}

$data = array(
    'cartHasDigitalProduct' => $cartHasDigitalProduct,
    'cartHasPhysicalProduct' => $cartHasPhysicalProduct,
    'products' => array_values($products),
    'cartSummary' => $cartSummary,
    'billingAddress' => empty($billingAddress) ? (object)array() : $billingAddress,
    'shippingAddress' => empty($shippingAddress) ? (object)array() : $shippingAddress,
);

require_once(CONF_THEME_PATH.'cart/price-detail.php');

if (empty($products)) {
    $status = applicationConstants::OFF;
}
