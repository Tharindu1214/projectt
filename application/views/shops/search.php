<?php
if(!empty($allShops)){ $i=0;
foreach($allShops as $shop){ /* CommonHelper::printArray($shop); die; */ ?>

<div class="ftshops row <?php echo ($i%2!=0) ? 'ftshops-rtl' : ''; ?>">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 column">
        <div class="ftshops_item">
          <div class="shop-detail-side">
            <div class="shop-detail-inner">
                <div class="ftshops_item_head_left">
                    <div class="ftshops_logo"><img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','shopLogo', array($shop['shop_id'], $siteLangId, "THUMB", 0, false),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $shop['shop_name']; ?>"></div>
                    <div class="ftshops_detail">
                        <div class="ftshops_name"><a href="<?php echo CommonHelper::generateUrl('shops','view', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name'];?></a></div>
                        <div class="ftshops_location"><?php echo $shop['state_name'];?><?php echo ($shop['country_name'] && $shop['state_name'])?', ':'';?><?php echo $shop['country_name'];?></div>
                    </div>
                </div>
                <div class="ftshops_item_head_right">
                    <?php if(0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && round($shop['shopRating'])>0){?>
                    <div class="products__rating"> <i class="icn"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                            </svg></i> <span class="rate"><?php echo  round($shop['shopRating'],1);?><span></span></span>
                    </div>
                    <?php }?>
                    <a href="<?php echo CommonHelper::generateUrl('shops','view', array($shop['shop_id']));?>" class="btn btn--primary btn--sm ripplelink" tabindex="0"><?php echo Labels::getLabel('LBL_View_Shop',$siteLangId);?></a>
                </div>
            </div>
          </div>
          <div class="product-wrapper">
            <div class="row">
            <?php foreach($shop['products'] as $product){?>
                <div class="col-lg-3 col-md-4  col-sm-3 mb-3 mb-md-0">
                    <?php include(CONF_THEME_PATH.'_partial/collection/product-layout-1-list.php'); ?>
                </div>
                <?php } ?>
            </div>
          </div>
        </div>
      </div>
</div>



	<?php /* <div class="rowrepeated">
		<div class="row">
			<div class="col-md-5 col-sm-5">
				<h5><a target='_blank' href="<?php echo CommonHelper::generateUrl('Shops','view' , array($val['shop_id'])); ?>" target='_new'><?php echo $val['shop_name'];?></a></h5>
				<p><?php echo $val['state_name'].','.$val['country_name'];?></p>
				<div class="item__ratings">
					<ul class="rating">
					<?php for($j=1;$j<=5;$j++){ ?>
					<li class="<?php echo $j<=round($val["shopRating"])?"active":"in-active" ?>">
						<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
						<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($val["shopRating"])?"#ff3a59":"#474747" ?>" /></g></svg>
					</li>
					<?php } ?>
					</ul>
				</div>
				<span class="text--normal"><?php echo round($val["shopRating"],1),' ',Labels::getLabel('Lbl_Out_of',$siteLangId),' ', '5' ?> - <a target='_blank' href="<?php echo CommonHelper::generateUrl('Reviews','shop',array($val['shop_id'])) ?>"><?php echo ($val['shopTotalReviews']) ? $val['shopTotalReviews'] . ' ' . Labels::getLabel('Lbl_Reviews',$siteLangId) .' | ' : ''; ?></a>  </span>

				<?php $showAddToFavorite = true; if(UserAuthentication::isUserLogged() && (!User::isBuyer()) ) $showAddToFavorite = false; ?>
				<?php if($showAddToFavorite) { ?>
					<?php if($val['is_favorite']){ ?>
					<a class="link--normal" href="javascript:void(0);" onClick="unFavoriteShopFavorite(<?php echo $val['shop_id']; ?>,this)"><?php echo Labels::getLabel('LBL_UnFavorite_to_Shop', $siteLangId); ?></a>
					<?php } else {
					?>
					<a class="link--normal" href="javascript:void(0);" onClick="markShopFavorite(<?php echo $val['shop_id']; ?>,this)"><?php echo Labels::getLabel('LBL_Favorite_Shop', $siteLangId); ?></a>
					<?php
				} } ?>

			</div>
			<div class="col-md-7 col-sm-7">
				<div class="scroller--items align--right">
					<ul class="listing--items">
						<?php if(!empty($val['products'])){
							foreach($val['products'] as $product){ ?>
							<li><a class="item__pic" target='_blank' href="<?php echo CommonHelper::generateUrl('Products','View',array($product['selprod_id'])); ?>"><img alt="<?php echo $product['product_name'];?>" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($product['product_id'], "SMALL", $product['selprod_id'], 0, $siteLangId),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"></a></li>
						<?php }
						} ?>
						<li><a target='_blank' href="<?php echo CommonHelper::generateUrl('shops','view',array($val['shop_id']));?>" class="item__link"><span><?php echo str_replace('{n}', $val['totalProducts'], Labels::getLabel('LBL_View_{n}_Product(s)', $siteLangId)); ?></span></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div> */ ?>
<?php $i++; }
} else {
	$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
}

$postedData['page'] = (isset($page))?$page:1;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchShopsPaging'
) );
