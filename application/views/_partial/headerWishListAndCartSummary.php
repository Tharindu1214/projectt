<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$user_is_buyer = 0;
if (UserAuthentication::isUserLogged()) {
    $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
}
if ($user_is_buyer > 0 || (!UserAuthentication::isUserLogged())) { ?>
    <a href="javascript:void(0)">
        <span class="icn"><svg class="svg">
         <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#main-cart" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#main-cart"></use>
        </svg></span>
        <span class="icn-txt"><strong><?php echo Labels::getLabel("LBL_Cart", $siteLangId); ?></strong>
            <span class="cartQuantity"><?php echo $totalCartItems.' '; ?></span>
            <?php /* if (0 < $cartSummary['cartTotal']) { */ ?>
                <span class="cartValue"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
            <?php /* } */ ?>
        </span>
    </a>
    <div class="dropsection cart-detail">
        <a href="javascript:void(0)" id="close-cart-js" class="close-layer"></a>
        <?php if ($totalCartItems>0) { ?>
        <div class="cartdetail__body" data-simplebar>
            <div class="short-detail">
                <table class="cart-summary">
                    <tbody>
                        <?php
                        if (count($products)) {
                            foreach ($products as $product) {
                                $productUrl = CommonHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                                $shopUrl = CommonHelper::generateUrl('Shops', 'View', array($product['shop_id']));
                                $imageUrl =  FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], "EXTRA-SMALL", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?> <tr class="<?php echo (!$product['in_stock']) ? 'disabled' : '';
                                echo ($product['is_digital_product'])?'digital_product_tab-js':'physical_product_tab-js'; ?>">
                            <td>
                                <div class="item__pic"><a href="<?php echo $productUrl; ?>"><img src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>"></a></div>
                            </td>
                            <td>
                                <div class="item__description">
                                    <div class="item__category"><a href="<?php echo $shopUrl; ?>"><?php echo $product['shop_name']; ?> </a></div>
                                    <div class="item__title"><a title="<?php echo $product['product_name']; ?>" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                                    <div class="item__specification"> <?php
                                    if (isset($product['options']) && count($product['options'])) {
                                        $count = 0;
                                        foreach ($product['options'] as $option) {
                                            ?> <?php echo ($count > 0) ? ' | ' : '' ;
                                            echo $option['option_name'].':'; ?> <?php echo $option['optionvalue_name']; ?> <?php $count++;
                                        }
                                    } ?> | <?php echo Labels::getLabel('LBL_Quantity:', $siteLangId) ?> <?php echo $product['quantity']; ?> </div>
                                </div>
                            </td>
                            <td>
                                <div class="product_price"><span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']*$product['quantity']); ?> </span>
                                    <?php if ($product['special_price_found']) { ?>
                                        <span class="text--normal text--normal-secondary text-nowrap"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                                    <?php } ?>
                                </div>
                            </td>
                            <td class="">
                                <a href="javascript:void(0)" class="icons-wrapper" onclick="cart.remove('<?php echo md5($product['key']); ?>')" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                    <i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                                        </svg>
                                    </i>
                                </a>
                            </td>
                        </tr> <?php
                            }
                        } else {
                            echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId);
                        } ?> </tbody>
                </table>
            </div>
        </div>
        <div class="cartdetail__footer">
            <table class="table--justify">
                <tr>
                    <td class=""><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></td>
                    <td class=""><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
                </tr>
                <tr>
                    <td class=""><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></td>
                    <td class=""><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTaxTotal']); ?></td>
                </tr>
                <?php if (0 < $cartSummary['cartVolumeDiscount']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></td>
                    </tr>
                <?php }
                $netChargeAmt = $cartSummary['cartTotal']+$cartSummary['cartTaxTotal'] - ((0 < $cartSummary['cartVolumeDiscount'])?$cartSummary['cartVolumeDiscount']:0); ?>
                <tr>
                    <td class="hightlighted"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></td>
                    <td class="hightlighted"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></td>
                </tr>
                <tr>
                    <td class=""><a href="<?php echo CommonHelper::generateUrl('cart'); ?>" class="btn btn--primary ripplelink"><?php echo Labels::getLabel('LBL_View_Bag', $siteLangId); ?> </a></td>
                    <td class=""><a class="btn btn--primary-border ripplelink" href="<?php echo CommonHelper::generateUrl('Checkout'); ?>"><?php echo Labels::getLabel('LBL_Proceed_To_Pay', $siteLangId); ?></a></td>
                </tr>
            </table>
        </div>
        <?php } else { ?>
            <div class="block--empty align--center"> <img class="block__img" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty_cart.svg" alt="<?php echo Labels::getLabel('LBL_No_Record_Found', $siteLangId); ?>" width="80">
                <h4><?php echo Labels::getLabel('LBL_Your_Shopping_Bag_is_Empty', $siteLangId); ?></h4>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<script>
    $("document").ready(function() {
        $('#close-cart-js').click(function() {
            if ($('html').hasClass('cart-is-active')) {
                $('html').removeClass('cart-is-active');
                $('.cart').toggleClass("cart-is-active");
            }
        });
    });
</script>
