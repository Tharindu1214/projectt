<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php  if( $topBrands ){ ?>
	<div class="accordion_triger"><?php echo Labels::getLabel('LBL_Top_Brands', $siteLangId); ?><span></span></div>
	<div class="accordion_content">
        <ul class="footerSubCategories">
			<?php $counter = 1;
			foreach( $topBrands as $brand ){ ?>
			<li><a href="<?php echo CommonHelper::generateUrl('Brands', 'view', array($brand['brand_id'])); ?>"><?php echo $brand['brand_name'];?></a>
			<?php // echo ( count($topBrands) != $counter ) ? ',': ''; ?>
			</li>
			<?php $counter++; } ?>
			<li><a href="<?php echo CommonHelper::generateUrl('brands'); ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId)?></a></li>
		</ul>
	</div>
<?php }  ?>
