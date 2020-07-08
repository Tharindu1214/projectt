<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row justify-content-between align-items-center mb-4">
<div class="col-auto">
    <h5 class="cards-title mb-3">
        <?php echo ($wishListRow['uwlist_default']==1) ? Labels::getLabel('LBL_Default_list', $siteLangId) : $wishListRow['uwlist_title']; ?>
        <input type="hidden" name="uwlist_id" value="<?php echo $wishListRow['uwlist_id']; ?>" />
    </h5>
	</div>
	<div class="col text-right">
	
    <div class="action action--favs btn-group-scroll">
        <label class="checkbox checkbox-inline">
            <input type="checkbox" class='selectAll-js' onclick="selectAll($(this));"><i class="input-helper"></i>Select all
        </label>
        <a title='<?php echo Labels::getLabel('LBL_Move_to_other_wishlist', $siteLangId); ?>' class="btn btn--primary btn--sm formActionBtn-js formActions-css" onclick="viewWishList(0,this,event, <?php echo !empty($wishListRow['uwlist_id']) ? $wishListRow['uwlist_id']: 0; ?>);" href="javascript:void(0)">
            <i class="fa fa-heart"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Move', $siteLangId); ?>
        </a>
        <a title='<?php echo Labels::getLabel('LBL_Move_to_cart', $siteLangId); ?>' class="btn btn--primary btn--sm formActionBtn-js formActions-css" onClick="addSelectedToCart(event);" href="javascript:void(0)">
            <i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Cart', $siteLangId); ?>
        </a>
        <a title='<?php echo Labels::getLabel('LBL_Move_to_trash', $siteLangId); ?>' class="btn btn--primary btn--sm formActionBtn-js formActions-css" onClick="removeSelectedFromWishlist( <?php echo $wishListRow['uwlist_id']; ?>, event );" href="javascript:void(0)">
            <i class="fa fa-trash"></i>&nbsp;&nbsp;<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>
        </a>
        <a class="btn btn--primary btn--sm" onClick="searchWishList();" href="javascript:void(0)">
            <?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>
        </a>
    </div>
	</div>
</div>
<form method="post" name="wishlistForm" id="wishlistForm" >
    <input type="hidden" name="uwlist_id" value="<?php echo $wishListRow['uwlist_id']; ?>" />
    <div id="wishListItems" class="row"></div>
</form>

<div id="loadMoreBtnDiv"></div>
<!--<a href="javascript:void(0)" onClick="goToWishListItemSearchPage(2);" class="loadmore loadmore--gray text--uppercase">Load More</a>-->

<script type="text/javascript">
$("document").ready( function(){
    searchWishListItems(<?php echo $wishListRow['uwlist_id']; ?>);
});
</script>
