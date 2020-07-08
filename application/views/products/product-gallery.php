<div class="slider-for" dir="<?php echo CommonHelper::getLayoutDirection();?>" id="quickView-slider-for">
  <?php if( $productImagesArr ){ ?>
  <?php 
  
  
	foreach( $productImagesArr as $afile_id => $image ){
		$mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'] ) ), CONF_IMG_CACHE_TIME, '.jpg');
		$thumbImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'] ) ), CONF_IMG_CACHE_TIME, '.jpg');
		?>
  <div class="item__main">
  <?php if(isset($imageGallery) && $imageGallery){ ?>
  <a href="<?php echo $mainImgUrl; ?>"  class="gallery" rel="gallery">
  <?php } ?>
  <img src="<?php echo $mainImgUrl;	 ?>">
  <?php if(isset($imageGallery) && $imageGallery){ ?>
  </a>
  <?php }?>
  </div>
  <?php } ?>
  <?php } else { $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array(0, 'MEDIUM', 0 ) ), CONF_IMG_CACHE_TIME, '.jpg'); ?>
  <div class="item__main"><img src="<?php echo $mainImgUrl; ?>"></div>
  <?php } ?>
</div>
<?php if( $productImagesArr ){ ?>
<div class="slider slider-nav" dir="<?php echo CommonHelper::getLayoutDirection();?>" id="quickView-slider-nav">
  <?php foreach( $productImagesArr as $afile_id => $image ){ 
		$mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'] ) ), CONF_IMG_CACHE_TIME, '.jpg');
		$thumbImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id']) ), CONF_IMG_CACHE_TIME, '.jpg');
	 ?>
  <div class="thumb "><img main-src = "<?php echo $mainImgUrl; ?>" src="<?php echo $thumbImgUrl; ?>"></div>
  <?php } ?>
</div>
<?php } ?>
