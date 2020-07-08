<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php if($productTags){ ?>
<div class="box--scroller">
<ul class="columlist links--vertical" id="product-tag">
<?php
	$lis= '';
	foreach($productTags as $tag){
		$lis .= '<li id="product-tag' . $tag['tag_id'] . '"><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeProductTag('.$product_id.','.$tag['tag_id'].');"><i class="icon ion-close" data-tag-id="' . $tag['tag_id'] . '"></i></a></span>';
		$lis .= '<span class="left">' . $tag['tag_name'].' ('.$tag['tag_identifier'].')'.'<input type="hidden" value="'.$tag['tag_id'].'"  name="product_tag[]"></span></li>';
	}
	echo $lis;
?>
</ul>
</div>
<?php } ?>
