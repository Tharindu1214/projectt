<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($products as $key => $product) {
    $products[$key]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId));
    $products[$key]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
    $products[$key]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
    $products[$key]['total'] = CommonHelper::displayMoneyFormat($product['total'], false, false, false);
    $products[$key]['netTotal'] = CommonHelper::displayMoneyFormat($product['netTotal'], false, false, false);
}

$data = array(
    'products' => array_values($products),
    'cartSummary' => $cartSummary,
    'cartSelectedBillingAddress' => empty($cartSelectedBillingAddress) ? (object)array() : $cartSelectedBillingAddress,
    'cartSelectedShippingAddress' => empty($cartSelectedShippingAddress) ? (object)array() : $cartSelectedShippingAddress,
    'hasPhysicalProduct' => $hasPhysicalProduct,
    'isShippingSameAsBilling' => $isShippingSameAsBilling,
    'selectedBillingAddressId' => $selectedBillingAddressId,
    'selectedShippingAddressId' => $selectedShippingAddressId,
);

require_once(CONF_THEME_PATH.'cart/price-detail.php');

if (empty($products)) {
    $status = applicationConstants::OFF;
}
