<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

array_walk($digitalDownloadLinks, function (&$value, $siteLangId) {
    unset($value['opddl_downloadable_link']);
    $uploadedTime = AttachedFile::setTimeParam($value['product_image_updated_on']);
    $value['product_image_url'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($value['selprod_product_id'], "CLAYOUT3", $value['op_selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
});

$data = array(
    'digitalDownloadLinks'=> $digitalDownloadLinks,
    'page'=> $page,
    'pageCount'=> $pageCount,
    'recordCount'=> $recordCount,
);

if (empty($digitalDownloadLinks)) {
    $status = applicationConstants::OFF;
}
