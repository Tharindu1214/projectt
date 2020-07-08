<?php if (!empty($childCategoriesArr) ){ ?>
<ul>
	<?php foreach ($childCategoriesArr as $category){
		$hasChild = 0;
		if(!empty($category['children'])) { $hasChild = 1;} ?>
		<li><a class="selectCategory" data-level = <?php echo $level;?> data-has-child="<?php echo $hasChild; ?>" href="javascript:void(0)" id="<?php echo $category['prodcat_id']; ?>"><?php echo $category['prodcat_name']; ?><?php if($hasChild>0) {?>
			<i class="fa fa-long-arrow-right"></i></li>
		<?php }?></a>
	<?php }?>
</ul>
<?php }  ?>