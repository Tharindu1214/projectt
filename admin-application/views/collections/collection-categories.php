<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box--scroller">
	<ul class="columlist links--vertical" id="collection-category">
	<?php if( $collectioncategories ){

		$lis = '';
		foreach( $collectioncategories as $category ){
			$lis .= '<li id="collection-prodcat' . $category['prodcat_id'] . '"><span class="right"><a href="javascript:void(0)" title="Remove" onClick="removeCollectionCategory('. $collection_id .','.$category['prodcat_id'].');"><i class="ion-ios-close" data-prodcat-id="' . $category['prodcat_id'] . '"></i></a></span>';
			$lis .= '<span>' . $category['prodcat_name'].'<input type="hidden" value="'.$category['prodcat_id'].'"  name="collection_categories[]"></span></li>';
		}
		echo $lis;
	} ?>
	</ul>
</div>