<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist" id="linked-category">
<?php if(!empty($linkedCategories)){
	$lis= '';
	foreach($linkedCategories as $category){
		$lis .= '<li id="category' . $category['prodcat_id'] . '"><span class="right"><a href="javascript:void(0)" title="Remove" onClick="removeLinkedCategory('.$polling_id.','.$category['prodcat_id'].');"><i class="ion-ios-close" data-category-id="' . $category['prodcat_id'] . '"></i></a></span>';
		$lis .= '<span class="left">' . $category['prodcat_name'].' ('.$category['prodcat_identifier'].')'.'<input type="hidden" value="'.$category['prodcat_id'].'"  name="prodcat_option[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>