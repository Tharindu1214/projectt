<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist list-vertical" id="product-option">
<?php if($selectedCats){
	$lis= '';
	foreach($selectedCats as $option){
		if(isset($arr_options[$option['prodcat_id']])){
			$lis .= '<li id="product-option' . $option['prodcat_id'] . '"><a href="javascript:void(0)" title="'. Labels::getLabel('LBL_Remove', $siteLangId) .'" onClick="removeProductCategory('.$product_id.','.$option['prodcat_id'].');"><i class="fa fa-remove" data-option-id="' . $option['prodcat_id'] . '"></i> '.$arr_options[$option['prodcat_id']].' </a><input type="hidden" value="'.$option['prodcat_id'].'"  name="product_option[]">';
		}
	}
	echo $lis;
} ?>
</ul>
