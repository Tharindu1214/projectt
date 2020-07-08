<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box--scroller">
<ul class="columlist links--vertical" id="collection-selprod">
<?php if($collectionSelprods){
	$lis= '';
	foreach($collectionSelprods as $selprod){
		$lis .= '<li id="collection-selprod' . $selprod['selprod_id'] . '"><span class="left	"><a href="javascript:void(0)" title="Remove" onClick="removeCollectionSelprod('.$collection_id.','.$selprod['selprod_id'].');"><i class=" icon ion-close" data-selprod-id="' . $selprod['selprod_id'] . '"></i></a></span>';
		$lis .= '<span>' . $selprod['selprod_title'].'<input type="hidden" value="'.$selprod['selprod_id'].'"  name="collection_selprod[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>
</div>