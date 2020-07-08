<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="box box--white box--radius order-summary">
    <?php
	if(!empty($defaultAddress)) {?>
    <div class="p-4">
        <div class="section-head">
            <div class="section__heading">
                <h6>
                <?php if ($hasPhysicalProd) {
                    echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                } else {
                    echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                } ?>
                </h6>
            </div>
            <div class="section__action"><a href="#" class="btn btn--primary-border btn--sm" onClick="showAddressList()"><?php echo Labels::getLabel('LBL_Change_Address', $siteLangId); ?></a> </div>
        </div>
        <div class="shipping-address">
            <?php echo $defaultAddress['ua_identifier']; ?><br>
            <?php echo $defaultAddress['ua_name']; ?><br>
            <?php echo $defaultAddress['ua_address1'];?><br>
            <?php echo $defaultAddress['ua_city'];?>,  <?php echo $defaultAddress['state_name'];?>, <?php /*echo (strlen($defaultAddress['ua_zip'])>0) ? Labels::getLabel('LBL_Zip:', $siteLangId).' '.$defaultAddress['ua_zip'].'<br>':'';*/ ?>
            <?php echo (strlen($defaultAddress['ua_phone'])>0) ? Labels::getLabel('LBL_Phone:', $siteLangId).' '.$defaultAddress['ua_phone'].'<br>':'';?>
        </div>
    </div>
    <div class="divider"></div>
    <?php }?>
    <div class="p-4">
        <div class="section-head">
            <div class="section__heading">
                <h6><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?> - <?php echo count($products); ?> <?php echo Labels::getLabel('LBL_item(s)', $siteLangId); ?></h6>
            </div>
            <div class="section__action js-editCart" style="display:block;"><a href="javascript:void(0);" onClick="editCart()" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Edit_Cart', $siteLangId);?></a> </div>
        </div>
        <div class="scrollbar-order-list" data-simplebar>
            <table class="cart-summary  table--justify order-table">
                <tbody>
                    <?php foreach ($products as $product) { ?>
                    <tr class="physical_product_tab-js">
                        <td>
                            <?php $productUrl = CommonHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>
                            <div class="item__pic"><a href="<?php echo $productUrl;?>"><img src=<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>"></a></div>
                        </td>
                        <td>
                            <div class="item__description">
                                <div class="item__title"><a title="<?php echo $product['product_name']?>" href="<?php echo $productUrl; ?>"><?php echo $product['product_name']?></a></div>
                                <div class="item__title"><a title="<?php echo $product['product_name']?>" href="<?php echo $productUrl; ?>"><?php echo $product['selprod_title']?></a></div>
                                <div class="item__specification  js-editCart" style="display:block;">
                                    <?php if (isset($product['options']) && count($product['options'])) {
                                        foreach ($product['options'] as $key => $option) {
                                            /*if (0 < $key){
                                                echo ' | ';
                                            }*/
                                            echo $option['option_name'].':'; ?> <span class="text--dark"><?php echo $option['optionvalue_name']; ?> |</span>
                                        <?php }
                                    } ?>  <?php echo Labels::getLabel('LBL_Quantity', $siteLangId); ?>: <?php echo $product['quantity']; ?> </div>
                                <div class="product_qty js-editCart" style="display:none;">
                                    <div class="qty-wrapper">
                                        <div class="quantity" data-stock="<?php echo $product['selprod_stock']; ?>">
                                            <span class="decrease decrease-js">-</span>
                                            <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                                                <input name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" data-page="checkout" class="qty-input cartQtyTextBox productQty-js"
                                                    value="<?php echo $product['quantity']; ?>" type="text" />
                                            </div>
                                            <span class="increase increase-js">+</span>
                                        </div>
                                    </div>
                                    <ul class="actions js-editCart" style="display:none;">
                                        <li><a href="javascript:void(0)" class="icons-wrapper" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout','')" title="<?php echo Labels::getLabel("LBL_Remove", $siteLangId); ?>"
                                                class="icons-wrapper"><i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                                                    </svg></i></a>
                                        </li>
                                    </ul>

                                    <!--<a class="refresh" title="<?php /* echo Labels::getLabel("LBL_Update_Quantity", $siteLangId); */ ?>" href="javascript:void(0)" onclick="cart.update('<?php /* echo md5($product['key']); */ ?>','loadFinancialSummary')"><i class="fa fa-refresh"></i></a>-->

                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="product_price"><span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']*$product['quantity']); ?></span>

                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
    <div class="divider"></div>
    <div class="p-4">
        <?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
        <div class="applied-coupon">
            <span><?php echo Labels::getLabel("LBL_Coupon", $siteLangId); ?> "<strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong>" <?php echo Labels::getLabel("LBL_Applied", $siteLangId); ?></span> <a
                href="javascript:void(0)" onClick="removePromoCode()" class="btn btn--primary btn--sm"><?php echo Labels::getLabel("LBL_Remove", $siteLangId); ?></a></div>
        <?php } else { ?>
        <div class="coupon"> <a class="coupon-input btn btn--primary btn--block" href="javascript:void(0)" onclick="getPromoCode()"><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></a> </div>

        <?php } ?>
    </div>
    <div class="divider"></div>
    <div class="p-4">
        <div class="cartdetail__footer">
            <table>
                <tbody>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></td>
                        <td class="text-right"> <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
                    </tr>
                    <?php if ($cartSummary['cartVolumeDiscount']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Loyalty/Volume_Discount', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($cartSummary['cartTaxTotal']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTaxTotal']); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (!empty($cartSummary['cartDiscounts'])) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></td>
                    </tr>
                    <?php } ?>                    
                   
                    <?php if ($cartSummary['originalShipping']) { ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['shippingTotal']); ?></td>
                    </tr>
                    <?php  } ?>
                    <?php if (!empty($cartSummary['cartRewardPoints'])) {
                        $appliedRewardPointsDiscount = CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']); ?>
                    <tr>
                        <td class="text-left"><?php echo Labels::getLabel('LBL_Reward_point_discount', $siteLangId); ?></td>
                        <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($appliedRewardPointsDiscount); ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="text-left hightlighted"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></td>
                        <td class="text-right hightlighted"><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /* if (count($products) > 2) {  ?>
<script>
    new SimpleBar(document.getElementById('simplebar'), {
        autoHide: false
    });
</script>
<?php } */ ?>
