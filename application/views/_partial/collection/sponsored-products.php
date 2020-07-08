<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( isset($products) && count($products) ) { ?>
<section class="section pb-0">
	<div class="container">
		<div class="section-head">
			<div class="section__heading">
				<h2><?php echo FatApp::getConfig('CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION_'.$siteLangId,FatUtility::VAR_STRING,Labels::getLabel('LBL_SPONSORED_PRODUCTS',$siteLangId)); ?></h2>
			</div>
		</div>
		<div class="row trending-corner product-listing" dir="<?php echo CommonHelper::getLayoutDirection();?>">
			<?php foreach( $products as $product ){ ?>
			<div class="col-xl-2 col-md-4 col-6 column">
				<?php include('product-layout-1-list.php'); Promotion::updateImpressionData($product['promotion_id']);?>
			</div>
			<?php } ?>
		</div>
	</div>
</section>
<?php } ?>
