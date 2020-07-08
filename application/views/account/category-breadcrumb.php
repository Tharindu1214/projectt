<?php if(isset($breadcrumbArr) && !empty($breadcrumbArr)) { ?>
<ul>
	<li><?php echo Labels::getLabel('LBL_All_Product_Categories',$siteLangId); ?></li>
	<?php foreach($breadcrumbArr as $node) { ?>
		<li><?php echo $node['title'];?></li>
	<?php }?>
</ul>
<?php }?>