<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
<?php if ($shops) { ?>
    <?php foreach ($shops as $shop) { ?>
    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
        <div class="featured-item">
         <div class="featured_inner p-2 pt-4 pb-4">
            <div class="favourite-wrapper ">
                <div class="favourite heart-wrapper is-active">
                    <a href="javascript:void(0);" onclick="toggleShopFavorite2(<?php echo $shop['shop_id']; ?>)"  title="<?php echo Labels::getLabel('LBL_Unfavorite_Shop', $siteLangId); ?>">
                         <div class="ring"></div>
                         <div class="circles"></div>
                    </a>
                </div>
            </div>
            <div class="featured_logo mt-2 mb-3"><img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','shopLogo', array($shop['shop_id'], $siteLangId, "THUMB", 0, false),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $shop['shop_name']; ?>"></div>
            <div class="featured_detail">
                 <div class="featured_name"><a href="<?php echo CommonHelper::generateUrl('shops','view', array($shop['shop_id']));?>"><?php echo $shop['shop_name'];?></a></div>
                 <div class="featured_location"><?php echo $shop['state_name'];?><?php echo ($shop['country_name'] && $shop['state_name'])?', ':'';?><?php echo $shop['country_name'];?></div>
            </div>
             <div class="featured_footer mt-3">
                 <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && round($shop['shopRating'])>0) {?>
                 <div class="products__rating"> <i class="icn"><svg class="svg">
                             <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                         </svg></i> <span class="rate"><?php echo  round($shop['shopRating'], 1);?><span></span></span>
                 </div>
                 <?php } ?>
                 <a href="<?php echo CommonHelper::generateUrl('shops','view', array($shop['shop_id'])); ?>" class="btn btn--primary" tabindex="0"><?php echo Labels::getLabel('LBL_Shop_Now',$siteLangId);?></a>
             </div>
         </div>
        </div>
    </div>
    <?php } ?>
</div>
<?php } else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmFavShopSearchPaging'));

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'callBackJsFunc' => 'goToFavoriteShopSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
