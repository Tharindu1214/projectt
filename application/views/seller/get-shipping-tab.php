<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<span class="shippingTabListing--js">
    <div class="text-right mb-3">
        <button class="btn--block btn btn--primary" style="width:125px;" onclick="optionForm('<?php echo $product_id; ?>')">Import</button>
        <a href="/seller/product-shipping-rate-export/<?php echo $product_id; ?>">
            <button style="width:<?php echo !empty($shipping_rates) && count($shipping_rates) > 0 ? 125:165; ?>px;" type="button" class="btn--block btn btn--primary">Export <?php echo !empty($shipping_rates) && count($shipping_rates) > 0 ? '':'Sample Data'; ?></button>
        </a>
        <br/>
        <small>It will delete all existing record and replace with new record.</small>
    </div>
    <?php
   if (!empty($shipping_rates) && count($shipping_rates) > 0) {
        $shipping_row = 0;
        foreach ($shipping_rates as $shipping) { ?>
            <div class="row align-items-center shippingRow--js" id="shipping-row<?php echo $shipping_row; ?>">
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][city_id]" value="<?php echo $shipping["pship_city"]?>">
                        <input type="text"
                            name="product_shipping[<?php echo $shipping_row; ?>][city_name]"
                            value="<?php echo $shipping["city_name"];?>"
                            placeholder="<?php echo Labels::getLabel('LBL_Ships_To', $siteLangId)?>" >
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["pship_company"]?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo $shipping["scompany_name"]?>" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Company', $siteLangId); ?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo $shipping['pship_duration']?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo ShippingDurations::getShippingDurationTitle($shipping, $siteLangId)?>"
                        placeholder="<?php echo Labels::getLabel('LBL_Processing_Time', $siteLangId)?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo $shipping["pship_charges"]?>" placeholder="<?php echo 'Shipping Cost ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo $shipping["pship_additional_charges"]?>" placeholder="<?php echo 'Each Additional item Cost['.commonHelper::getDefaultCurrencySymbol().']';?>">
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
            
            <div class="col-lg-3">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][pship_id]" value="" />
                    <input type="hidden" name="product_shipping[0][city_id]" value="">
                    <input type="text" name="product_shipping[0][city_name]" placeholder="<?php echo Labels::getLabel('LBL_Ships_To', $siteLangId)?>" />
                </div>
            </div>
            <div class="col-lg-2">
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
                    <input type="text" name="product_shipping[0][cost]" placeholder="<?php echo 'Shipping Cost ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="text" name="product_shipping[0][additional_cost]" placeholder="<?php echo 'Each Additional item Cost['.commonHelper::getDefaultCurrencySymbol().']';?>">
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
    <div class="col-lg-3">
    <?php if(count($shipping_rates) > 5){ ?>
            <button style="width:125px;" type="button" class="btn--block btn btn--primary viewall">View All</button>
    <?php } ?>
    </div>
    <div class="col-lg-2">
     <button style="width:125px;" type="button" class="btn--block btn btn--primary hideall">Hide All</button>
    </div>
    <div class="col-lg-2"></div>
    <div class="col-lg-2"></div>
    <div class="col-lg-1">
        <button type="button" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?>" onclick="addShipping();">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>
<script>
    var rowDisplay = 5;
    var totalRows = '<?php echo count($shipping_rates); ?>';
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
    removeShippingRow = function(shipping_row) {
        $("#shipping-row" + shipping_row).remove();
    }
    $('span.shippingTabListing--js div.shippingRow--js').each(function(index, element) {
        shippingautocomplete(index);
    });

    $(document).ready(function (){
        $(".shippingRow--js").hide();
        $(".hideall").hide();
        displayRows(rowDisplay);
        $(".viewall").click(function (){
            $(".hideall").show();
            rowDisplay += 5;
            displayRows(rowDisplay);
            if(totalRows <= rowDisplay){
                $(".viewall").hide();
            }
        });
        $(".hideall").click(function (){
            $(".shippingRow--js").hide();
            $(".viewall").show();
            $(".hideall").hide();
            rowDisplay = 5;
            displayRows(rowDisplay);
        });
    });

    function displayRows(count){
        for(var i=0; i<count; i++){
            $("#shipping-row"+i).show();
        }
    }
</script>
