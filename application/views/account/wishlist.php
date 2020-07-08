<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Wishlist/Favorites', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                
                <div class="cards-content p-4">
                    <div class="box__body">
                        <div class="tabs tabs--small tabs--scroll clearfix">
                            <ul>
                                <li class="is-active" id="tab-wishlist"><a onClick="searchWishList();" href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Wishlist', $siteLangId); ?></a></li>
                                <li id="tab-fav-shop"><a onClick="searchFavoriteShop();" href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Shops', $siteLangId); ?></a></li>
                            </ul>
                        </div>
                        <div id="listingDiv" class="account-fav-listing"></div>
                    </div>
                    <div class="gap"></div>
                    <div id="loadMoreBtnDiv"></div>
                </div>
            </div>
        </div>
    </div>
</main>
