<?php
$forPage = !empty($forPage) ? $forPage : '';
$staticCollectionClass='';
if ($controllerName='Products' && isset($action) && $action=='view') {
    $staticCollectionClass='static--collection';
} ?> <?php if (!isset($showAddToFavorite)) {
    $showAddToFavorite = true;
    if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
        $showAddToFavorite = false;
    }
}



if ($showAddToFavorite) { ?>
<div class="favourite-wrapper <?php /* echo $staticCollectionClass; */ ?>">
    <?php if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) { ?>
        <div class="favourite heart-wrapper heart-wrapper-Js <?php echo($product['ufp_id'])?'is-active':''; ?>" data-id="<?php echo $product['selprod_id']; ?>">
            <a href="javascript:void(0)" <?php echo($product['ufp_id'])? Labels::getLabel('LBL_Remove_product_from_favourite_list', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_favourite_list', $siteLangId); ?>>
                <div class="ring"></div>
                <div class="circles"></div>
            </a>
        </div>
    <?php } else { ?>
            <?php $showFavtBtn = true;
            if (Labels::getLabel('LBL_Wishlist', $siteLangId) ==  $forPage) { ?>
                <div class="container wishlist_items--css">
                    <ul class="actions mt-2">
                        <?php if ($product['in_stock']) { ?>
                        <li>
                            <a title='<?php echo Labels::getLabel('LBL_Select_Item', $siteLangId); ?>' href="javascript:void(0)" class="icn-highlighted">
                                <label class="checkbox">
                                    <input type="checkbox" name='selprod_id[]' class="selectItem--js" value="<?php echo $product['selprod_id']; ?>"/>
                                    <i class="input-helper"></i>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a onClick="addToCart( $(this), event );" href="javascript:void(0)" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Move_to_cart', $siteLangId); ?>" data-id='<?php echo $product['selprod_id']; ?>'><i class="fa fa-shopping-cart"></i></a>
                        </li>
                        <?php } ?>
                        <li>
                            <a  title='<?php echo Labels::getLabel('LBL_Move_to_trash', $siteLangId); ?>' onclick="removeFromWishlist(<?php echo $product['selprod_id']; ?>, <?php echo $product['uwlp_uwlist_id']; ?>, event);" href="javascript:void(0)" class="icn-highlighted">
                               <i class="fa fa-trash"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php $showFavtBtn = false;
            }
            if ($showFavtBtn) { ?>
                <div class="favourite heart-wrapper heart-wrapper-Js wishListLink-Js <?php echo($product['is_in_any_wishlist'])?'is-active':''; ?>" id="listDisplayDiv_<?php echo $product['selprod_id']; ?>" data-id="<?php echo $product['selprod_id']; ?>">
                    <a href="javascript:void(0)" onClick="viewWishList(<?php echo $product['selprod_id']; ?>,this,event);"
                        title="<?php echo($product['is_in_any_wishlist'])? Labels::getLabel('LBL_Remove_product_from_your_wishlist', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_your_wishlist', $siteLangId); ?>">
                        <div class="ring"></div>
                        <div class="circles"></div>
                    </a>
                </div>
            <?php }
    }
    if (isset($productView) && true == $productView) { ?>
        <div class="share-button">
            <a href="#" class="social-toggle"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#share" href="/yokartv8/images/retina/sprite.svg#share"></use>
                    </svg>
                </i></a>
            <div class="social-networks">
                <ul>
                    <li class="social-twitter">
                        <a href="https://www.twitter.com"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#tw" href="/yokartv8/images/retina/sprite.svg#tw"></use>
                    </svg>
                </i></a>
                    </li>
                    <li class="social-facebook">
                        <a href="https://www.facebook.com"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#fb" href="/yokartv8/images/retina/sprite.svg#fb"></use>
                    </svg>
                </i></a>
                    </li>
                    <li class="social-gplus">
                        <a href="http://www.gplus.com"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#gp" href="/yokartv8/images/retina/sprite.svg#gp"></use>
                    </svg>
                </i></a>
                    </li>
					<li class="social-pintrest">
                        <a href="http://www.gplus.com"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#pt" href="/yokartv8/images/retina/sprite.svg#pt"></use>
                    </svg>
                </i></a>
                    </li>

					<li class="social-email">
                        <a href="http://www.gplus.com"><i class="icn">
                    <svg class="svg">
                        <use xlink:href="/yokartv8/images/retina/sprite.svg#envelope" href="/yokartv8/images/retina/sprite.svg#envelope"></use>
                    </svg>
                </i></a>
                    </li>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
<?php }
