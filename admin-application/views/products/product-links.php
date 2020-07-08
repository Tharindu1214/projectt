<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php

if($selectedCats){ ?>
<div class="box--scroller">
<ul class="columlist links--vertical" id="product-option">
<?php	$lis= '';
	foreach($selectedCats as $option){
	if(!isset($arr_options[$option['prodcat_id']])){ continue; }
		$lis .= '<li id="product-option' . $option['prodcat_id'] . '"><span class="right"><a href="javascript:void(0)" title="Remove" onClick="removeProductCategory('.$product_id.','.$option['prodcat_id'].');"><i class="ion-ios-close" data-option-id="' . $option['prodcat_id'] . '"></i></a></span>';
		$lis .= '<span class="left">' . $arr_options[$option['prodcat_id']].'<input type="hidden" value="'.$option['prodcat_id'].'"  name="product_category[]"></span></li>';
	}
	echo $lis;
	?>
</ul>
</div>
<?php } ?>