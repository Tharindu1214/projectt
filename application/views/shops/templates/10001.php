<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<?php $catBannerArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BANNER, $shop['shop_id'], '', $siteLangId);
$desktop_url = '';
$tablet_url = '';
$mobile_url = '';
$defaultImgUrl = '';
foreach ($catBannerArr as $slideScreen) {
    $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
    switch ($slideScreen['afile_screen']) {
        case applicationConstants::SCREEN_MOBILE:
            $mobile_url = '<736:' .FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
            break;
        case applicationConstants::SCREEN_IPAD:
            $tablet_url = ' >768:' .FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'TABLET', 0, applicationConstants::SCREEN_IPAD)).$uploadedTime).",";
            break;
        case applicationConstants::SCREEN_DESKTOP:
            $defaultImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'DESKTOP', 0, applicationConstants::SCREEN_DESKTOP)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $desktop_url = ' >1025:' .$defaultImgUrl.",";
            break;
    }
} ?>

<?php if (!empty($catBannerArr)) { ?>
<section class="bg-shop">
   <div class="shop-banner">
       <img data-ratio="4:1" data-src-base="" data-src-base2x="" data-src="<?php echo $mobile_url . $tablet_url  . $desktop_url; ?>" src="<?php echo $defaultImgUrl; ?>">
   </div>
</section>
<?php } ?>
<section class="bg--second">
    <div class="container">
        <div class="shop-nav">
            <?php
            $variables= array('template_id'=>$template_id, 'shop_id'=>$shop['shop_id'], 'shop_user_id'=>$shop['shop_user_id'], 'collectionData'=>$collectionData,'action'=>$action,'siteLangId'=>$siteLangId);
            $this->includeTemplate('shops/shop-layout-navigation.php', $variables, false); ?>
        </div>
    </div>
</section>
