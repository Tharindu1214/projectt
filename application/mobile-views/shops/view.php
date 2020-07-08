<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (array_key_exists('products', $data)) {
    foreach ($data['products'] as $index => $product) {
        $uploadedTime = AttachedFile::setTimeParam($product['product_image_updated_on']);
        $data['products'][$index]['product_image_url'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        $data['products'][$index]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
        $data['products'][$index]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
    }
}
if (!empty($data['shop'])) {
    if (!empty(array_filter((array)$data['shop']['shop_payment_policy']))) {
        $data['shop']['policies'][] = $data['shop']['shop_payment_policy'];
    }
    if (!empty(array_filter((array)$data['shop']['shop_delivery_policy']))) {
        $data['shop']['policies'][] = $data['shop']['shop_delivery_policy'];
    }
    if (!empty(array_filter((array)$data['shop']['shop_refund_policy']))) {
        $data['shop']['policies'][] = $data['shop']['shop_refund_policy'];
    }
    if (!empty(array_filter((array)$data['shop']['shop_additional_info']))) {
        $data['shop']['policies'][] = $data['shop']['shop_additional_info'];
    }
    if (!empty(array_filter((array)$data['shop']['shop_seller_info']))) {
        $data['shop']['policies'][] = $data['shop']['shop_seller_info'];
    }

    $data['shop']['policies'] = !empty($data['shop']['policies']) ? $data['shop']['policies'] : [];

    unset($data['shop']['shop_payment_policy'], $data['shop']['shop_delivery_policy'], $data['shop']['shop_refund_policy'], $data['shop']['shop_additional_info'], $data['shop']['shop_seller_info']);
}

$data['shop'] = !empty($data['shop']) ? $data['shop'] : (object)array();

if (empty($data['products'])) {
    $status = applicationConstants::OFF;
}
