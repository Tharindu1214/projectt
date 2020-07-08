<div class="row">
<?php $i=0; foreach( $row['shops'] as $shop ){ ?>
    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
        <div class="featured-item">
         <div class="featured_inner p-2 pt-4 pb-4">
                 <div class="featured_logo mt-2 mb-3"><img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','shopLogo', array($shop['shopData']['shop_id'], $siteLangId, "THUMB", 0, false),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $shop['shopData']['shop_name']; ?>"></div>
                 <div class="featured_detail">
                     <div class="featured_name"><a href="<?php echo (!isset($shop['shopData']['promotion_id'])?CommonHelper::generateUrl('shops','view', array($shop['shopData']['shop_id'])):CommonHelper::generateUrl('shops','track', array($shop['shopData']['promotion_record_id'],Promotion::REDIRECT_SHOP,$shop['shopData']['promotion_record_id'])));?>"><?php echo $shop['shopData']['shop_name'];?></a></div>
                     <div class="featured_location"><?php echo $shop['shopData']['state_name'];?><?php echo ($shop['shopData']['country_name'] && $shop['shopData']['state_name'])?', ':'';?><?php echo $shop['shopData']['country_name'];?></div>
                 </div>

             <div class="featured_footer mt-3">
                 <?php if( round($row['rating'][$shop['shopData']['shop_id']])>0){?>
                 <div class="products__rating"> <i class="icn"><svg class="svg">
                             <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                         </svg></i> <span class="rate"><?php echo  round($row['rating'][$shop['shopData']['shop_id']],1);?><span></span></span>
                 </div>
                 <?php }?>
                 <a href="<?php echo (!isset($shop['shopData']['promotion_id'])?CommonHelper::generateUrl('shops','view', array($shop['shopData']['shop_id'])):CommonHelper::generateUrl('shops','track', array($shop['shopData']['promotion_record_id'],Promotion::REDIRECT_SHOP,$shop['shopData']['promotion_record_id']))); ?>" class="btn btn--primary" tabindex="0"><?php echo Labels::getLabel('LBL_Shop_Now',$siteLangId);?></a>
             </div>
         </div>
        </div>
    </div>
<?php $i++;
isset($shop['shopData']['promotion_id'])?Promotion::updateImpressionData($shop['shopData']['promotion_id']):'';
if($i==Collections::LIMIT_SHOP_LAYOUT1) break; } ?>
</div>
