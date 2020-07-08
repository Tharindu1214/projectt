<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$frmShippingApi->developerTags['colClassPrefix'] = 'col-md-';
$frmShippingApi->developerTags['fld_default_col'] = 12;

$frmShippingApi->setFormTagAttribute('onSubmit', 'setUpShippingApi(this); return false;');

$shippingapi_idFld = $frmShippingApi->getField('shippingapi_id');
$shippingapi_idFld->developerTags['col'] = 6;
//$btnSubmit->setFieldTagAttribute('class','btn btn--primary btn--h-large');
?>
<div class="section-head">
    <div class="section__heading">
        <h2><?php echo Labels::getLabel('LBL_Shipping_Summary', $siteLangId); ?></h2>
    </div>
</div>
<div class="box box--white box--radius p-4">
    <section id="shipping-summary" class="section-checkout">
        <div class="review-wrapper step__body">

            <?php usort($products, function ($a, $b) {
    return $a['shop_id'] - $b['shop_id'];
});

                        $prevShopId = 0;
                    $productsInShop = array_count_values(array_column($products, 'shop_id'));
                    if (count($products)) {
                        $productCount = 0;
                        foreach ($products as $product) {
                            $productCount++;
                            if ($product['shop_id'] != $prevShopId) { ?>
            <div class="short-detail">
                <div class="shipping-seller">
                    <div class="row  justify-content-between">
                        <div class="col-auto">
                            <div class="shipping-seller-title"><?php echo $product['shop_name']; ?></div>
                        </div>
                        <div class="col-auto">
                            <?php
                                if ($product['shop_eligible_for_free_shipping'] > 0) {
                                    echo '<div class="note-messages">'.Labels::getLabel('LBL_free_shipping_is_available_for_this_shop', $siteLangId).'</div>' ;
                                } elseif ($product['shop_free_ship_upto'] > 0 && $product['shop_free_ship_upto'] > $product['totalPrice']) {
                                    $str = Labels::getLabel('LBL_Free_shipping_available_on_orders_above_{amount}_from_this_shop', $siteLangId);
                                    $str = str_replace('{amount}', $product['shop_free_ship_upto'], $str);
                                    echo '<div class="note-messages">'.$str.'</div>';
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <table class="cart-summary table cart--full js-scrollable scroll-hint">
                    <tbody>
                        <?php }
                            $newShippingMethods = $shippingMethods;
                            $productUrl = !$isAppUser?CommonHelper::generateUrl('Products', 'View', array($product['selprod_id'])):'javascript:void(0)';
                            $shopUrl = !$isAppUser?CommonHelper::generateUrl('Shops', 'View', array($product['shop_id'])):'javascript:void(0)';
                            $imageUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                        <tr class="<?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
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
                                            <?php echo Labels::getLabel('LBL_This_product_is_not_eligible_for_free_shipping', $siteLangId);    ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                            $selectedShippingType = "";
                            $displayManualOptions = "style='display:none'";
                            $displayShipStationOption = "style='display:none'";
                            $shipping_options = array();
                            $shipping_options[$product['product_id']][0] = Labels::getLabel("LBL_Select_Shipping", $siteLangId);
                            //print_r($product["shipping_rates"]);

                            if (count($product["shipping_rates"])) {
                                foreach ($product["shipping_rates"] as $skey => $sval):
                                    $country_code = empty($sval["city_name"]) ? "" : " (" . $sval["city_name"] . ")";
                                    $product["shipping_free_availbilty"];
                                    if ($product['shop_eligible_for_free_shipping'] > 0 && $product['psbs_user_id'] > 0) {
                                        $shipping_charges = Labels::getLabel('LBL_Free_Shipping', $siteLangId);
                                    } else {
                                        $shipping_charges = $product["shipping_free_availbilty"] == 0 ? "+" . CommonHelper::displayMoneyFormat($sval['pship_charges']) : 0;
                                    }
                                    $shippingDurationTitle = ShippingDurations::getShippingDurationTitle($sval, $siteLangId);
                                    // $shipping_options[$product['product_id']][$sval['pship_id']] =  $sval["scompany_name"] ." - " . $shippingDurationTitle . $country_code . " (" . $shipping_charges . ")";
                                    $shipping_options[$product['product_id']][$sval['pship_id']] =  $sval["scompany_name"] ." - " . $shippingDurationTitle . $country_code;
                                endforeach;
                                if ($product['is_shipping_selected']== ShippingMethods::MANUAL_SHIPPING) {
                                    $selectedShippingType = ShippingMethods::MANUAL_SHIPPING;
                                    $displayManualOptions = "style='display:block'";
                                }
                            }

                            $servicesList = array();
                            $cartObj = new Cart();

                            if (array_key_exists(ShippingMethods::SHIPSTATION_SHIPPING, $shippingMethods)) {
                                $carrierCode = "";
                                $selectedService ='';
                                if ($product['is_shipping_selected'] == ShippingMethods::SHIPSTATION_SHIPPING) {
                                    $service_code = str_replace("_", " ", $product['selected_shipping_option']['mshipapi_key']);
                                    $shippingCodes = explode(" ", $service_code);
                                    $carrierCode = $shippingCodes[0];
                                    $servicesList = $cartObj->getCarrierShipmentServicesList(md5($product['key']), $carrierCode, $siteLangId);
                                    $selectedShippingType = ShippingMethods::SHIPSTATION_SHIPPING;
                                    $displayShipStationOption = "style='display:block'";
                                    foreach ($servicesList as $key => $value) {
                                        if ($key == $product['selected_shipping_option']['mshipapi_key']) {
                                            $selectedService = $key;
                                        }
                                    }
                                }
                                $courierProviders = CommonHelper::createDropDownFromArray('data[' . md5($product['key']) . ']['."shipping_carrier".']', $shipStationCarrierList, $carrierCode, 'class="courier_carriers" onChange="loadShippingCarriers(this);"  data-product-key=\'' . md5($product['key']) . '\'', '');
                                $serviceProviders = CommonHelper::createDropDownFromArray('data[' . md5($product['key']) . ']['."shipping_services".']', $servicesList, $selectedService, 'class="courier_services "  ', '');
                            }
                            if(count($shipping_options[$product['product_id']]) > 2){
                                $select_shipping_options = CommonHelper:: createDropDownFromArray('data[' . md5($product['key']) . ']['."shipping_locations".']', $shipping_options[$product['product_id']], isset($product["pship_id"])?$product["pship_id"]:'', '', '');
                            }else if(count($shipping_options[$product['product_id']]) == 2){
                                $key = array_keys($shipping_options[$product['product_id']])[1];
                                $select_shipping_options = CommonHelper:: createDropDownFromArray('data[' . md5($product['key']) . ']['."shipping_locations".']', $shipping_options[$product['product_id']], $key, '', '');
                            }
                             
                            ?>
                                <?php Labels::getLabel('M_Select_Shipping', $siteLangId) ?>
                                <ul class="shipping-selectors">
                                    <?php
	
                            if (sizeof($shipping_options[$product['product_id']]) < 2) {                   
                                unset($newShippingMethods[SHIPPINGMETHODS::MANUAL_SHIPPING]);  
								echo '<li class="info-message">Delivery unavailable to '.$cityName.' city</li>';
                            }
                            if (!$product['is_physical_product'] && $product['is_digital_product']) {
                                echo $shippingOptions = CommonHelper::displayNotApplicable($siteLangId, '');
                            } else {
                                if (sizeof($newShippingMethods) > 0) {
                                     echo '<li style="display:none">'. CommonHelper::createDropDownFromArray('data[' . md5($product['key']) . ']['."shipping_type".']', $newShippingMethods, 1, 'class="shipping_method"  data-product-key="' . md5($product['key']) . '" ', Labels::getLabel('LBL_Select_Shipping_Method', $siteLangId)) .'</li>';
                                    if($product['shipping_free_availbilty'] == 0){
                                        $displayManualOptions = "style='display:block'";
                                    }else{
                                        echo '<li class="info-message">FREE SHIPPING ON THIS ORDER</li>';
                                    }   
                                } else {
                                   // echo '<li class="info-message">'.Labels::getLabel('MSG_Product_is_not_available_for_shipping', $siteLangId).'</li>';
                                    echo '<li class="info-message">Delivery unavailable to '.$cityName.' city</li>';
                                } ?>
                                    <li class='manual_shipping' <?php echo $displayManualOptions ?>>
                                        <?php /* <p><?php  Labels::getLabel('M_Select_Shipping_Provider', $siteLangId) ?></p> */ ?>
                                        <?php echo $select_shipping_options ?>
                                    </li>

                                    <?php if (array_key_exists(ShippingMethods::SHIPSTATION_SHIPPING, $shippingMethods)) { ?>


                                    <li class='shipstation_selectbox' <?php echo $displayShipStationOption;?>>
                                        <p><?php echo Labels::getLabel('M_Select_Shipping_Provider', $siteLangId) ?></p>
                                        <?php echo $courierProviders ?>
                                    </li>
                                    <li class='shipstation_selectbox' <?php echo $displayShipStationOption; ?>>
                                        <div class="services_loader"></div>
                                        <div class="api_shipping_rates_not_found-html-div-js"></div>
                                        <div class="api_shipping_rates_found-js">
                                            <?php echo Labels::getLabel('M_Select_Shipping_Carrier', $siteLangId) ?><?php echo $serviceProviders ?>
                                        </div>
                                    </li>


                                    <?php } ?>
                                </ul>
                                <?php
                            } ?>
                            </td>
                            <td><span class="item__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']*$product['quantity']); ?> </span>
                                <?php if ($product['special_price_found']) { ?>
                                <span class="text--normal text--normal-secondary text-nowrap"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                                <?php } ?>
                            </td>
                            <td class="text-right">
                                <a href="javascript:void(0)" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout')" class="icons-wrapper"><i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                                        </svg></i></a>
                            </td>
                        </tr>
                        <?php if ($productCount == $productsInShop[$product['shop_id']]) {
                                $productCount = 0; ?>
                    </tbody>
                </table>
            </div>
            <?php
                            }
                            $prevShopId = $product['shop_id']; ?>
            <?php
                        }
                    } else {
                        echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId);
                    } ?>

    </section>
    <div class="row align-items-center justify-content-between mt-4">
        <div class="col"><a class="btn btn--primary-border" onclick="showAddressList();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a></div>
        <div class="col-auto">
            <a class="btn btn--primary " onClick="setUpShippingMethod();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
        </div>
    </div>
</div>

<script>
    $('.shipping_method').on("change", function() {

        if ($(this).val() == "0") {
            $(this).parent().parent().find('.shipstation_selectbox').hide();
            $(this).parent().parent().find('.manual_shipping').hide();
        } else if ($(this).val() == "1") {

            $(this).parent().parent().find('.shipstation_selectbox').hide();
            $(this).parent().parent().find('.manual_shipping').show();
        } else if ($(this).val() == "2") {
            /*      resetShipstationSelectBox(this); */
            $(this).parent().parent().find('.shipstation_selectbox').show();
            $(this).parent().parent().find('.manual_shipping').hide();
        }
    });

    function resetShipstationSelectBox(obj) {
        $('.courier_carriers').val(0);
        loadShippingCarriers(obj);
        return true;
    }

    function loadShippingCarriers(obj) {
        $(obj).parent().next().find('.services_loader').html(fcom.getLoader());
        $(obj).parent().next().find('.courier_services ').hide();
        $(obj).parent().next().find('.api_shipping_rates_found-js').hide();
        $(obj).parent().next().find('.api_shipping_rates_not_found-html-div-js').html('');
        /* $(".shipstation_selectbox").LoadingOverlay("show",{'image':''}); */
        var carrier_id = $(obj).val();
        var product_key = $(obj).attr('data-product-key');

        var href = fcom.makeUrl('checkout', 'getCarrierServicesList', [product_key, carrier_id]);

        fcom.updateWithAjax(href, '', function(res) {
            $.mbsmessage.close();
            $(obj).parent().next().find('.services_loader').html('');
            if (res.isCarriersFound == 1) {
                $(obj).parent().next().find('.courier_services ').show();
                $(obj).parent().next().find('.courier_services').html(res.html);

                $(obj).parent().next().find('.api_shipping_rates_found-js').show();
                $(obj).parent().next().find('.api_shipping_rates_not_found-html-div-js').html('');

            } else {
                $(obj).parent().next().find('.api_shipping_rates_found-js').hide();
                $(obj).parent().next().find('.api_shipping_rates_not_found-html-div-js').html(res.html);
            }
        });
    }
</script>
