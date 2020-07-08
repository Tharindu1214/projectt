<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

unset($data['frmProductSearch'], $data['postedData']);

if (array_key_exists('products', $data)) {
    foreach ($data['products'] as $index => $product) {
        $uploadedTime = AttachedFile::setTimeParam($product['product_image_updated_on']);
        $data['products'][$index]['product_image_url'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        $data['products'][$index]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
        $data['products'][$index]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
    }
}
if (empty($data)) {
    $status = applicationConstants::OFF;
}
