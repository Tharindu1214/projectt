<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$nextPage = $page + 1;
if( $nextPage <= $pageCount ){ ?>
	<a id="loadMoreBtn" href="javascript:void(0)" onClick="goToCatalogRequestMessageSearchPage(<?php echo $nextPage; ?>);" class="loadmore themebtn btn-default btn-sm"><?php echo Labels::getLabel('LBL_Load_Previous_Messages',$adminLangId); ?></a>
<?php
}
?>
