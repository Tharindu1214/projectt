<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<?php if (!empty($rootCategoriesArr) && (!empty($childCategoriesArr)) ){?>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 categories-devider">
		<div class="box-border box-categories scrollbar">
		<?php if (!empty($rootCategoriesArr) ){ ?>
				<ul>
				<?php foreach ($rootCategoriesArr as $id => $name){ ?>
					<li><a class="selectSearchCategory" href="javascript:void(0)" id="<?php echo $id; ?>"><?php echo $name; ?></a></li>
				<?php }?>
				</ul>
		<?php } ?>
		</div>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 categories-devider">
		<div class="box-border box-categories scrollbar">
		<?php if (!empty($childCategoriesArr) ){ ?>
				<ul>
				<?php foreach ($childCategoriesArr as $id => $name){ $lastCategory = explode("&raquo;&raquo;&nbsp;&nbsp;",$name); end($lastCategory);?>
					<li><a class="selectSearchCategory" href="javascript:void(0)" id="<?php echo $id; ?>"><?php print_r(end($lastCategory));?></a></li>
					<li><?php echo $name; ?></li>
				<?php }?>
				</ul>
		<?php } ?>
		</div>
	</div>
	<?php } else {
		$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
	} ?>
</div>
<script>
if($(window).width()>1050){
	$('.scrollbar').enscroll({
		verticalTrackClass: 'scroll__track',
		verticalHandleClass: 'scroll__handle'
	});
}
</script>
