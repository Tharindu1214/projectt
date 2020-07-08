<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php  if($topCategories){ ?>
	 <div class="accordion_triger"><?php echo Labels::getLabel('LBL_Top_Categories', $siteLangId); ?><span></span></div>
	  <div class="accordion_content">
            <ul class="footerSubCategories">
			<?php $counter = 1; foreach( $topCategories as $category ){ ?>
			<li><a href="<?php echo CommonHelper::generateUrl('Category', 'view', array($category['prodcat_id'])); ?>"><?php echo $category['prodcat_name'];?></a>
			<?php // echo ( count($topCategories) != $counter ) ? ',': ''; ?>
			</li>
			<?php $counter++; } ?>
			<li><a href="<?php echo CommonHelper::generateUrl('category'); ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId)?></a></li>
		</ul>
	</div>
<?php }  ?>
