<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row select-categories-slider select-categories-slider-js" dir="<?php echo CommonHelper::getLayoutDirection();?>">
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12">
		<div class="box-border box-categories scrollbar  ">
			<?php if (!empty($rootCategoriesArr) ){ ?>
				<ul>
				<?php foreach ($rootCategoriesArr as $category){ ?>
					<li><a class="selectCategory" href="javascript:void(0)" id="<?php echo $category['prodcat_id']; ?>"><?php echo $category['prodcat_name']; ?></a>
					<?php if(!empty($category['children'])) {?>
						<i class="fa  fa-long-arrow-right"></i></li>
					<?php }?>
				<?php }?>
				</ul>
			<?php } ?>
		</div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12">
		<div class="box-border box-categories scrollbar ">

		</div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12">
		<div class="box-border box-categories scrollbar"></div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12">
		<div class="box-border box-categories scrollbar"></div>
	</div>
</div>
<script>
if($(window).width()>1050){
	$('.scrollbar').enscroll({
		verticalTrackClass: 'scroll__track',
		verticalHandleClass: 'scroll__handle'
	});
}
</script>
