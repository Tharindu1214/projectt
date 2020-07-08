<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="cards-header pb-3">
    <h5><?php echo Labels::getLabel('LBL_Products_That_I_Love',$siteLangId);?></h5>
	<a class="btn btn--primary btn--sm btn--positioned" onClick="searchWishList();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
</div>
<div id="favListItems" class="row"></div>
<div id="loadMoreBtnDiv"></div>

<script type="text/javascript">
$("document").ready( function(){
	searchFavouriteListItems();
});
</script>

