<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<span class="shippingTabListing--js">
    <?php
    if (!empty($shipping_rates) && count($shipping_rates) > 0) {
        $shipping_row = 0;
        foreach ($shipping_rates as $shipping) { ?>
            <div class="row align-items-center shippingRow--js" id="shipping-row<?php echo $shipping_row; ?>">
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>">
                        <input type="text"
                            name="product_shipping[<?php echo $shipping_row; ?>][country_name]"
                            value="<?php echo $shipping["pship_country"] != "1" ? $shipping["country_name"] : "&#8594;".Labels::getLabel('LBL_EveryWhere_Else', $siteLangId);?>"
                            placeholder="<?php echo Labels::getLabel('LBL_Ships_To', $siteLangId)?>" >
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["pship_company"]?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo isset($shipping["scompany_name"]) ? $shipping["scompany_name"] : ''?>" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Company', $siteLangId); ?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo isset($shipping['pship_duration']) ? $shipping['pship_duration']: ''?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo isset($shipping['sduration_days_or_weeks']) ? ShippingDurations::getShippingDurationTitle($shipping, $siteLangId) : ''?>"
                        placeholder="<?php echo Labels::getLabel('LBL_Processing_Time', $siteLangId)?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo isset($shipping["pship_charges"]) ? $shipping["pship_charges"] : '';?>" placeholder="<?php echo Labels::getLabel('LBL_Cost', $siteLangId) .' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo isset($shipping["pship_additional_charges"]) ? $shipping["pship_additional_charges"] : '';?>" placeholder="<?php echo Labels::getLabel('LBL_Each_Additional_Item', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                    </div>
                </div>
                <div class="col-lg-1">
                    <div class="field-set">
                        <button type="button" onclick="removeShippingRow('<?php echo $shipping_row; ?>');" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId)?>">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php $shipping_row++;
        }
    } else { ?>
        <div class="row align-items-center shippingRow--js" id="shipping-row1">
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][pship_id]" value="" />
                    <input type="hidden" name="product_shipping[0][country_id]">
                    <input type="text" name="product_shipping[0][country_name]" placeholder="<?php echo Labels::getLabel('LBL_Ships_To', $siteLangId)?>" />
                </div>
            </div>
            <div class="col-lg-3">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][company_id]">
                    <input type="text" name="product_shipping[0][company_name]" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Company', $siteLangId)?>">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][processing_time_id]">
                    <input type="text" name="product_shipping[0][processing_time]" placeholder="<?php echo Labels::getLabel('LBL_Processing_Time', $siteLangId)?>">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="text" name="product_shipping[0][cost]" placeholder="<?php echo Labels::getLabel('LBL_Cost', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="text" name="product_shipping[0][additional_cost]" placeholder="<?php echo Labels::getLabel('LBL_Each_Additional_Item', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                </div>
            </div>
            <div class="col-lg-1">
                <div class="field-set">
                    <button type="button" onclick="removeShippingRow('1')" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId)?>">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php } ?>
</span>
<div class="row align-items-center">
    <div class="col-lg-2"></div>
    <div class="col-lg-3"></div>
    <div class="col-lg-2"></div>
    <div class="col-lg-2"></div>
    <div class="col-lg-2"></div>
    <div class="col-lg-1">
        <button type="button" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?>" onclick="addShipping();">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>

<script>
    addShipping = function() {
        var shipping_row = parseInt($("span.shippingTabListing--js div.shippingRow--js").length);
        $("span.shippingTabListing--js div.shippingRow--js:last").clone().appendTo('span.shippingTabListing--js');
        $("span.shippingTabListing--js div.shippingRow--js:last input").each(function(){
            $(this).val("");
            var name = $(this).attr("name");
            var newName = name.replace("["+(shipping_row-1)+"]", "["+(shipping_row)+"]");
            $(this).attr("name", newName);
        });
        shippingautocomplete(shipping_row);
    }
    $('span.shippingTabListing--js div.shippingRow--js').each(function(index, element) {
        shippingautocomplete(index);
    });
    removeShippingRow = function(shipping_row) {
        $("#shipping-row" + shipping_row).remove();
    }
</script>
