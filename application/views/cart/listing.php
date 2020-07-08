<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="row">
    <div class="col-xl-9 col-lg-8">
        <div class="box box--white box--radius box--space">
            <table class="table cart--full js-scrollable ">
                <thead>
                    <tr>
                        <th colspan="2"><?php echo Labels::getLabel('LBL_Item(s)_in_cart', $siteLangId).'
    '.count($products); ?></th>
                        <th><?php echo Labels::getLabel('LBL_Quantity', $siteLangId); ?></th>
                        <th width="12%"><?php echo Labels::getLabel('LBL_Price', $siteLangId); ?></th>
                        <th width="10%"><?php echo Labels::getLabel('LBL_SubTotal', $siteLangId); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products)) {
                        foreach ($products as $product) {
                            $productUrl = CommonHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                            $shopUrl = CommonHelper::generateUrl('Shops', 'View', array($product['shop_id']));
                            $imageUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>

                    <tr class="<?php echo md5($product['key']); ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
                        <td>
                            <div class="product-img"><a href="<?php echo $productUrl; ?>"><img src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>"></a></div>
                        </td>
                        <td>
                            <div class="item-yk-head">
                                <div class="item-yk-head-category"><?php echo Labels::getLabel('LBL_Brand', $siteLangId).': '; ?><span class="text--dark"><?php echo $product['brand_name']; ?></div>
                                <div class="item-yk-head-title"><a title="<?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?>"
                                        href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                                <div class="item-yk-head-specification">
                                <?php
                                if (isset($product['options']) && count($product['options'])) {
                                    foreach ($product['options'] as $key => $option) {
                                        if (0 < $key){
                                            echo ' | ';
                                        }
                                        echo $option['option_name'].':'; ?> <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                    <?php }
                                }

                                $showAddToFavorite = true;
                                if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                                    $showAddToFavorite = false;
                                }

                                if ($showAddToFavorite) { ?>
                                    <br>
                                    <?php if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                                        if (empty($product['ufp_id'])) {  ?>
                                            <a href="javascript:void(0)" class="btn btn--sm btn--primary-border ripplelink" onClick="addToFavourite( '<?php echo md5($product['key']); ?>',<?php echo $product['selprod_id']; ?> );"
                                        title="<?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_Move_to_favourites', $siteLangId); ?></a>
                                        <?php } else {
                                            echo Labels::getLabel('LBL_Already_marked_as_favourites.', $siteLangId);
                                        }
                                    } else {
                                        if (empty($product['is_in_any_wishlist'])) { ?>
                                            <a href="javascript:void(0)" class="btn btn--sm btn--primary-border ripplelink" onClick="moveToWishlist( <?php echo $product['selprod_id']; ?>, event, '<?php echo md5($product['key']); ?>' );"
                                        title="<?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_Move_to_wishlist', $siteLangId); ?></a>
                                        <?php  } else {

                                            echo Labels::getLabel('LBL_Already_added_to_your_wishlist.', $siteLangId);
                                        }
                                    }
                                } ?>
                                </div>
                            </div>
                        </div>
                </td>
            <td>

            <div class="qty-wrapper">
                <div class="quantity" data-stock="<?php echo $product['selprod_stock']; ?>">
                    <span class="decrease decrease-js <?php echo ($product['quantity']==1) ? 'not-allowed' : '' ;?>">-</span>
                    <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                        <input name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" class="qty-input cartQtyTextBox productQty-js" value="<?php echo $product['quantity']; ?>" type="text"/>
                    </div>
                    <span class="increase increase-js <?php echo ($product['selprod_stock'] <= $product['quantity']) ? 'not-allowed' : '';?>">+</span>
                </div>
                <!-- <a class="refresh" title="<?php echo Labels::getLabel("LBL_Update_Quantity", $siteLangId); ?>" href="javascript:void(0)" onclick="cart.update('<?php echo md5($product['key']); ?>')"><i class="fa fa-refresh"></i></a> -->
                <?php
                /* $stockText = ($product['in_stock']) ? Labels::getLabel('LBL_In_Stock',$siteLangId) : Labels::getLabel('LBL_Out_of_Stock',$siteLangId);
                $stockTextClass = ($product['in_stock']) ? 'text--normal-primary' : 'text--normal-secondary'; */
                ?>
            </div>
        </td>
        <td>
            <span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?></span>
        </td>
        <td> <span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['total']); ?> </span>
        </td>
        <td>
            <a href="javascript:void(0)" class="icons-wrapper" onclick="cart.remove('<?php echo md5($product['key']); ?>','cart')" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>"><i class="icn shop"><svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                    </svg>
                </i>
			</a>
        </td>
        </tr>
        <?php }
            } ?>
        </tbody>
        </table>
        <?php /* if (!empty($cartSummary['cartDiscounts']['coupon_code'])) {
                            ?>
        <div class="alert alert--success">
            <a href="javascript:void(0)" class="close" onClick="removePromoCode()"></a>
            <p><?php echo Labels::getLabel('LBL_Promo_Code', $siteLangId); ?> <strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong> <?php echo Labels::getLabel('LBL_Successfully_Applied', $siteLangId); ?></p>
        </div>
        <?php
    }*/?>

    </div>


</div>
<div class="col-xl-3 col-lg-4">
    <div class="box box--white box--radius box--space cart-footer">
        <?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
        <div class="applied-coupon">
            <span><?php echo Labels::getLabel("LBL_Coupon", $siteLangId); ?> "<strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong>" <?php echo Labels::getLabel("LBL_Applied", $siteLangId); ?></span> <a href="javascript:void(0)"
                onClick="removePromoCode()" class="btn btn--primary btn--sm"><?php echo Labels::getLabel("LBL_Remove", $siteLangId); ?></a></div>
        <?php } else { ?>
        <div class="coupon">
            <a class="coupon-input btn btn--primary btn--block" href="javascript:void(0)" onclick="getPromoCode()"><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></a>
        </div>
        <?php }?>

        <div class="cartdetail__footer">
            <table class="table--justify">
                <tbody>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
                    </tr>
                    <?php if ($cartSummary['cartVolumeDiscount']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></td>
                    </tr>
                    <?php  } ?>
                    <?php if (!empty($cartSummary['cartDiscounts'])) { ?>
                        <tr>
                            <td class="text-left"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></td>
                            <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></td>
                        </tr>
                    <?php }?>
                    <?php $netChargeAmt = $cartSummary['cartTotal'] + $cartSummary['cartTaxTotal'] - ((0 < $cartSummary['cartVolumeDiscount'])?$cartSummary['cartVolumeDiscount']:0);?>
                    <?php $netChargeAmt = $netChargeAmt - ((0 < $cartSummary['cartDiscounts']['coupon_discount_total'])?$cartSummary['cartDiscounts']['coupon_discount_total']:0);?>
                    <?php if ($cartSummary['cartTaxTotal']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTaxTotal']); ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="text-left hightlighted"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></td>
                        <td class="text-right hightlighted"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">

                            <div class="buy-group">
                                <a class="btn btn--primary" href="<?php echo CommonHelper::generateUrl(); ?>"><?php echo Labels::getLabel('LBL_Shop_More', $siteLangId); ?></a>
                                <a class="btn btn--primary-border" href="javascript:void(0)" onclick="goToCheckout()"><?php echo Labels::getLabel('LBL_Checkout', $siteLangId); ?></a>

                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
        <div class="summary__row">
            <p class="note align--right"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $cartSummary['orderNetAmount']); ?> </p>
        </div>
        <?php } ?>
        <div class="cart-advices">
            <div class="row">
                <div class="col-lg-6 mb-sm-2">
                    <div class="advices-icons"><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/icn-safe.svg"></i>
                        <h6> <?php echo Labels::getLabel('LBL_Safe_&_Secure', $siteLangId);?></h6>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="advices-icons"><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/icn-protection.svg"></i>
                        <h6><?php echo Labels::getLabel('LBL_Payment_Protection', $siteLangId);?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
