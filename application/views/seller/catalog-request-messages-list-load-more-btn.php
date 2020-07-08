<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$nextPage = $page + 1;
if( $nextPage <= $pageCount ){ ?>
	<a id="loadMoreBtn" href="javascript:void(0)" onClick="goToCatalogRequestMessageSearchPage(<?php echo $nextPage; ?>);" class="loadmore">Load Previous Messages</a>
<?php
}
?>
