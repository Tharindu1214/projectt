<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row select-categories-slider select-categories-slider-js" dir="<?php echo CommonHelper::getLayoutDirection();?>">
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js">
		<div class="box-border box-categories scrollbar" id="child-category">
			<?php if (!empty($rootCategoriesArr) ){ ?>
				<ul>
				<?php foreach ($rootCategoriesArr as $category){
					$hasChild = 0;
					if(!empty($category['children'])) { $hasChild = 1;} ?>
					<li><a class="selectCategory" data-level="1" data-has-child="<?php echo $hasChild; ?>" href="javascript:void(0)" id="<?php echo $category['prodcat_id']; ?>"><?php echo $category['prodcat_name']; ?><?php if($hasChild>0) {?>
						<i class="fa  fa-long-arrow-right"></i></li>
					<?php }?></a>
				<?php }?>
				</ul>
			<?php } ?>
		</div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js">
		<div class="box-border box-categories scrollbar" id="child-category-js-1"></div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js">
		<div class="box-border box-categories scrollbar" id="child-category-js-2"></div>
	</div>
	<div class="slider-item col-lg-4 col-md-4 col-sm-3 col-xs-12 slider-item-js">
		<div class="box-border box-categories scrollbar" id="child-category-js-3"></div>
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