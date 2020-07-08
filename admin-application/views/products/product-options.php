<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if($productOptions){ ?>
<div class="box--scroller">
<ul class="columlist links--vertical" id="product-option">	
<?php	$lis= '';
	foreach($productOptions as $option){
		$lis .= '<li id="product-option' . $option['option_id'] . '"><span class="left" ><a href="javascript:void(0)" title="Remove" onClick="removeProductOption('.$product_id.','.$option['option_id'].');"><i class="icon ion-close" data-option-id="' . $option['option_id'] . '"></i></a></span>';
		$lis .= '<span class="left">' . $option['option_name'].' ('.$option['option_identifier'].')'.'<input type="hidden" value="'.$option['option_id'].'"  name="product_option[]"></span></li>';
	}
	echo $lis;
?>
</ul>
</div>
<?php } ?>