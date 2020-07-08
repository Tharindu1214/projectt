<div id="tabUl" class="tabs tabs--flat-js justify-content-md-center">
	<ul>
	<?php foreach( $row['categories'] as $key => $category ){?>
		<li class=""><a href="#tb-<?php echo $key; ?>"><?php echo $category['catData']['prodcat_name']; ?></a></li>
	<?php }?>
	</ul>
</div>
<?php foreach( $row['categories'] as $key => $category ){?>
	<div id="tb-<?php echo $key; ?>" class="tabs-content tabs-content-js" style="display: block;">
		<div class="ft-pro-wrapper">
		<?php $i=1; foreach( $category['products'] as $key => $product ){ ?>
			<div class="ft-pro ft-pro-<?php echo $i; ?>">
				<?php $prodImgSize = 'MEDIUM'; ?>

                <!--product tile-->
                <div class="products <?php echo (isset($layoutClass)) ? $layoutClass : ''; ?> <?php if($product['selprod_stock']<=0){ ?> item--sold  <?php } ?>">
                <?php if($product['selprod_stock']<=0){ ?>
                    <span class="tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId);?></span>
                <?php  } ?>

                    <div class="products__body">
                        <?php include(CONF_THEME_PATH.'_partial/collection-ui.php'); ?>
						<?php $uploadedTime = AttachedFile::setTimeParam($product['product_image_updated_on']);?>
                        <div class="products__img">
                            <a title="<?php echo $product['selprod_title'];?>" href="<?php echo !isset($product['promotion_id'])?CommonHelper::generateUrl('Products','View',array($product['selprod_id'])):CommonHelper::generateUrl('Products','track',array($product['promotion_record_id']));?>"><img data-ratio="1:1 (500x500)" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i==1)) ? $prodImgSize : "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['prodcat_name'];?>"> </a>
                        </div>
                    </div>
										<div class="content-overlay"></div>
<div class="content-details">
	<div class="">

											<div class="products__title"><a title="<?php echo $product['selprod_title'];?>" href="<?php echo CommonHelper::generateUrl('Products','View',array($product['selprod_id']));?>"><?php echo $product['selprod_title'];?> </a></div>
											<?php include(CONF_THEME_PATH.'_partial/collection/product-price.php');?>
											</div>

										</div>
                </div>
                <!--/product tile-->

			</div>
		<?php $i++; } ?>
		</div>

	</div>
<?php }?>
