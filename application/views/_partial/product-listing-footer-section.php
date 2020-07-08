<div class="products__footer">
	<?php /* if(round($product['prod_rating'])>0 && FatApp::getConfig("CONF_ALLOW_REVIEWS",FatUtility::VAR_INT,0)){ ?>
	<?php if(round($product['prod_rating'])>0 ){ ?>
	<div class="products__rating"> <i class="icn"><svg class="svg">
		<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
		</svg></i> <?php if(round($product['prod_rating'])>0 ){ ?>
	  <span class="rate"><?php echo round($product['prod_rating'],1);?></span>
	  <?php } ?>
		  <?php if(isset($firstToReview) && $firstToReview){ ?>
		  <?php if(round($product['prod_rating'])==0 ){  ?>
		  <span class="be-first"> <a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Be_the_first_to_review_this_product', $siteLangId); ?> </a> </span>
		  <?php } ?>
	  <?php }?>
	</div>
	<?php } ?>
	<?php } */  ?>
	<div class="products__category"><a href="<?php echo CommonHelper::generateUrl('Category','View',array($product['prodcat_id']));?>"><?php echo $product['prodcat_name'];?> </a></div>
	<div class="products__title"><a title="<?php echo $product['selprod_title'];?>" href="<?php echo CommonHelper::generateUrl('Products','View',array($product['selprod_id']));?>"><?php echo (mb_strlen($product['selprod_title']) > 50) ? mb_substr($product['selprod_title'],0,50)."..." : $product['selprod_title'];?> </a></div>
	<?php include(CONF_THEME_PATH.'_partial/collection/product-price.php');?>
</div>
