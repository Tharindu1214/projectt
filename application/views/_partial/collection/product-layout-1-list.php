<!--product tile-->
<div class="products <?php echo (isset($layoutClass)) ? $layoutClass : ''; ?> <?php if($product['selprod_stock']<=0){ ?> item--sold  <?php } ?>">
<?php if($product['selprod_stock']<=0){ ?>
	<span class="tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId);?></span>
<?php  } ?>
    <div class="products__quickview">
        <a onClick='quickDetail(<?php echo $product['selprod_id']; ?>)' class="modaal-inline-content">
            <span class="svg-icon">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view"></use>
                </svg>
            </span><?php echo Labels::getLabel('LBL_Quick_View', $siteLangId); ?>
        </a>
    </div>
	<div class="products__body">
		<?php include(CONF_THEME_PATH.'_partial/collection-ui.php'); ?>
        <?php $uploadedTime = AttachedFile::setTimeParam($product['product_image_updated_on']);?>
		<div class="products__img">
			<a title="<?php echo $product['selprod_title'];?>" href="<?php echo !isset($product['promotion_id'])?CommonHelper::generateUrl('Products','View',array($product['selprod_id'])):CommonHelper::generateUrl('Products','track',array($product['promotion_record_id']));?>"><img data-ratio="1:1 (500x500)" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i==1)) ? $prodImgSize : "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['prodcat_name'];?>"> </a>
		</div>
	</div>
	<?php $selprod_condition=true; include(CONF_THEME_PATH.'_partial/product-listing-footer-section.php');?>
</div>
<!--/product tile-->
