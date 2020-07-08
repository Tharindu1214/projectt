<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box--scroller">
	<ul class="columlist links--vertical" id="collection-brand">
		<?php
		if( $collectionBrands ){
			$lis = '';
			foreach( $collectionBrands as $brand ){
				$lis .= '<li id="collection-brands' . $brand['brand_id'] . '"><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCollectionBrand('. $collectionId .','.$brand['brand_id'].');"><i class="icon ion-close" data-brand-id="' . $brand['brand_id'] . '"></i></a></span>';
				$lis .= '<span>' . $brand['brand_name'].'<input type="hidden" value="'.$brand['brand_id'].'"  name="collection_brands[]"></span></li>';
			}
			echo $lis;
		} ?>
	</ul>
</div>