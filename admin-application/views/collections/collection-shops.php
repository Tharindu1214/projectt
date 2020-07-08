<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box--scroller">
<ul class="columlist links--vertical" id="collection-shop">
<?php if( $collectionshops ){

	$lis = '';
	foreach( $collectionshops as $shop ){
		$lis .= '<li id="collection-prodcat' . $shop['shop_id'] . '"><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCollectionShop('. $collection_id .','.$shop['shop_id'].');"><i class="icon ion-close" data-prodcat-id="' . $shop['shop_id'] . '"></i></a></span>';
		$lis .= '<span>' . $shop['shop_name'].'<input type="hidden" value="'.$shop['shop_id'].'"  name="collection_shops[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>
</div>