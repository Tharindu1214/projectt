<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="section-head">
    <div class="section__heading">
        <h2><?php echo Labels::getLabel('LBL_Review_Order', $siteLangId); ?></h2>
    </div>
</div>
<div class="box box--white box--radius p-4">
    <div class="review-wrapper">
        <?php if ($cartHasDigitalProduct && $cartHasPhysicalProduct) { ?>
        <div class="">
            <div class="tabs tabs--small tabs--scroll clearfix setactive-js">
                <ul>
                    <li class="is-active "><a rel="physical_product_tab" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Tab_Physical_Product', $siteLangId); ?></a></li>
                    <li class="digitalProdTab-js"><a rel="digital_product_tab" href="javascript:void(0)" class=""><?php echo Labels::getLabel('LBL_Tab_Digital_Product', $siteLangId); ?></a></li>
                </ul>
            </div>
        </div>
        <?php }?>
        <div class="short-detail">
            <table class="table cart--full js-scrollable scroll-hint">
                <tbody>
                    <?php
        if (count($products)) {
            foreach ($products as $product) {
                $productUrl = !$isAppUser?CommonHelper::generateUrl('Products', 'View', array($product['selprod_id'])):'javascript:void(0)';
                $shopUrl = !$isAppUser?CommonHelper::generateUrl('Shops', 'View', array($product['shop_id'])):'javascript:void(0)';
                $imageUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                    <tr class="<?php echo (!$product['in_stock']) ? 'disabled' : '';
                echo ($product['is_digital_product'])?'digital_product_tab-js':'physical_product_tab-js'; ?>">
                        <td>
                            <figure class="item__pic"><a href="<?php echo $productUrl; ?>"><img src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>"></a></figure>
                        </td>
                        <td>
                            <div class="item__description">
                                <div class="item__category"><?php echo Labels::getLabel('LBL_Shop', $siteLangId) ?>: <span class="text--dark"><?php echo $product['shop_name']; ?></span></div>
                                <div class="item__title"><a title="<?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?>"
                                        href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                                <div class="item__specification">
                                    <?php
                if (isset($product['options']) && count($product['options'])) {
                    foreach ($product['options'] as $option) { ?>
                                    <?php echo ' | ' . $option['option_name'].':'; ?>
                                    <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                    <?php
                    }
                } ?>
                                    | <?php echo Labels::getLabel('LBL_Quantity', $siteLangId) ?> <?php echo $product['quantity']; ?>
                                    <?php if (($product['shop_eligible_for_free_shipping'] > 0 || ($product['shop_free_ship_upto'] > 0 && $product['shop_free_ship_upto'] > $product['totalPrice']))  && $product['psbs_user_id'] == 0 && $product['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) { ?>
                                    <div class="item-yk-head-specification note-messages">
                                        <?php echo Labels::getLabel('LBL_free_shipping_is_not_eligible_for_this_product', $siteLangId);    ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']*$product['quantity']); ?> </span>
                            <?php if ($product['special_price_found']) { ?>
                            <span class="text--normal text--normal-secondary"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                            <?php } ?>
                        </td>
                        <td class="text-right">
                            <a href="javascript:void(0)" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout')" class="icons-wrapper"><i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                                    </svg></i></a>
                        </td>


                    </tr>
                    <?php
            }
        } else {
            echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId);
        }
         ?>
                </tbody>
            </table>
        </div>
        <div class="cartdetail__footer">
            <table>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
                </tr>
                <?php if ($cartSummary['shippingTotal']) { ?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['shippingTotal']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTaxTotal']); ?></td>
                </tr>
                <?php if (!empty($cartSummary['cartRewardPoints'])) {
             $appliedRewardPointsDiscount = CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']); ?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Reward_point_discount', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($appliedRewardPointsDiscount); ?></td>
                </tr>
                <?php
         } ?>
                <?php if (!empty($cartSummary['cartDiscounts'])) {?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="text-left hightlighted"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></td>
                    <td class="text-right hightlighted"><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-right"><a href="javascript:void(0)" onClick="loadPaymentSummary();" class="btn btn--primary-border ripplelink block-on-mobile"><?php echo Labels::getLabel('LBL_Proceed_To_Pay', $siteLangId); ?> </a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<!-- <a class="btn btn--primary btn--h-large" onClick="loadPaymentSummary();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a> -->
<script type="text/javascript">
    $("document").ready(function() {
        <?php if ($cartHasPhysicalProduct) { ?>
        $('.digital_product_tab-js').hide();
        <?php }?>
        $(document).on("click", '.setactive-js li a', function() {
            var rel = $(this).attr('rel');
            if (rel == 'digital_product_tab') {
                $('.physical_product_tab-js').hide();
                $('.digital_product_tab-js').show();
            } else {
                $('.digital_product_tab-js').hide();
                $('.physical_product_tab-js').show();
            }
        });
    });
</script>
