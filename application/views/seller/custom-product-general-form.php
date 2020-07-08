<div class="tabs tabs--small tabs--scroll clearfix">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row row">
                <div class="col-md-12">
                    <div class="tabs tabs-sm tabs--scroll clearfix">
                        <ul>
                            <li class="is-active"><a <?php echo ($product_id) ? "onclick='customProductForm( ".$product_id." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>

                            <?php foreach ($languages as $langId => $langName) {?>
                            <li class="<?php echo (!$product_id) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($product_id) ? "onclick='customProductLangForm( ".$product_id.",".$langId." );'" : ""; ?>><?php echo $langName;?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="form__subcontent">
                        <?php
                        $customProductFrm->setFormTagAttribute('class', 'form form--horizontal');
                        $customProductFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                        $customProductFrm->developerTags['fld_default_col'] = 4;
                        $customProductFrm->setFormTagAttribute('onsubmit', 'setupCustomProduct(this); return(false);');

                        $shippingCountryFld = $customProductFrm->getField('shipping_country');
                        $shippingCountryFld->setWrapperAttribute('class', 'not-digital-js');

                        $shipFreeFld = $customProductFrm->getField('ps_free');
                        $shipFreeFld->setWrapperAttribute('class', 'not-digital-js');

                        if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
                            $lengthFld = $customProductFrm->getField('product_length');
                            $lengthFld->setWrapperAttribute('class', 'product_length_fld');
                            //$lengthFld->htmlAfterField = Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId);

                            $widthFld = $customProductFrm->getField('product_width');
                            $widthFld->setWrapperAttribute('class', 'product_width_fld');
                            //$widthFld->htmlAfterField = Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId) ;

                            $heightFld = $customProductFrm->getField('product_height');
                            $heightFld->setWrapperAttribute('class', 'product_height_fld');
                            //$heightFld->htmlAfterField = Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId);

                            $dimensionUnitFld = $customProductFrm->getField('product_dimension_unit');
                            $dimensionUnitFld->setWrapperAttribute('class', 'product_dimension_unit_fld');

                            $weightFld = $customProductFrm->getField('product_weight');
                            $weightFld->setWrapperAttribute('class', 'product_weight_fld');

                            $weightUnitFld = $customProductFrm->getField('product_weight_unit');
                            $weightUnitFld->setWrapperAttribute('class', 'product_weight_unit_fld');
                        }

                        $productCodEnabledFld = $customProductFrm->getField('product_cod_enabled');
                        $productCodEnabledFld->setWrapperAttribute('class', 'product_cod_enabled_fld');

                        $shippingInfoFld = $customProductFrm->getField('shipping_info_html');
                        $shippingInfoFld->setWrapperAttribute('class', 'col-lg-12');
                        $shippingInfoFld->developerTags['col'] = 12;

                        /* $productShippedByMeFld = $customProductFrm->getField('product_shipped_by_me');
                        $productShippedByMeFld->setWrapperAttribute( 'class' , 'product_shipped_by_me_fld'); */

                        /* $lengthFld = $customProductFrm->getField('product_length')->fieldWrapper = array('<div class="s">','</div>');
                        $widthFld = $customProductFrm->getField('product_width')->fieldWrapper = array('<div class="f">','</div>');
                        $heightFld = $customProductFrm->getField('product_height')->fieldWrapper = array('<div class="a">','</div>');

                        $customProductFrm->getField('product_weight')->fieldWrapper = array('<div class="c">','</div>');
                        $customProductFrm->getField('product_weight_unit')->fieldWrapper = array('<div class="g">','</div>'); */

                        //$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');
                        echo $customProductFrm->getFormHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var productOptions = [];
    var productId = <?php echo $product_id;?>;
    var productCatId = <?php echo $prodcat_id;?>;
    var prodTypeDigital = <?php echo Product::PRODUCT_TYPE_DIGITAL;?>;
    var dv = $("#listing");

    var PRODUCT_TYPE_PHYSICAL = <?php echo Product::PRODUCT_TYPE_PHYSICAL; ?>;
    var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
    $(document).ready(function() {
        addShippingTab(productId);
        $("select[name='product_type']").change(function() {
            if ($(this).val() == PRODUCT_TYPE_PHYSICAL) {
                $(".product_length_fld").show();
                $(".product_width_fld").show();
                $(".product_height_fld").show();
                $(".product_dimension_unit_fld").show();
                $(".product_weight_fld").show();
                $(".product_weight_unit_fld").show();
                $(".product_cod_enabled_fld").show();
                /* $(".product_shipped_by_me_fld").show(); */
                $('.not-digital-js').show();
                $('#tab_shipping').show();
                addShippingTab(productId);
            }

            if ($(this).val() == PRODUCT_TYPE_DIGITAL) {
                $(".product_length_fld").hide();
                $(".product_width_fld").hide();
                $(".product_height_fld").hide();
                $(".product_dimension_unit_fld").hide();
                $(".product_weight_fld").hide();
                $(".product_weight_unit_fld").hide();
                $(".product_cod_enabled_fld").hide();
                /* $(".product_shipped_by_me_fld").hide(); */
                $('.not-digital-js').hide();
                $('#tab_shipping').hide();
            }
        });

        $("select[name='product_type']").trigger('change');

        /* $("select[name='product_shipped_by_me']").change(function(){
        if( $(this).val() == 1 && $("select[name='product_type']").val() == PRODUCT_TYPE_PHYSICAL){
        $('.not-digital-js').show();
        $('#tab_shipping').show();
        }else{
        if( $(this).val() == 0 ){
        $('.not-digital-js').hide();
        $('#tab_shipping').hide();
        }
        }
        });
        $("select[name='product_shipped_by_me']").trigger('change'); */

        /* Shipping Information */
        $('input[name=\'shipping_country\']').autocomplete({
            'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('seller', 'countries_autocomplete'),
                    data: {
                        keyword: request,
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name=\'shipping_country\']').val(item.label);
                $('input[name=\'ps_from_country_id\']').val(item.value);
            }
        });

        $('input[name=\'shipping_country\']').keyup(function() {
            $('input[name=\'ps_from_country_id\']').val('');
        });

        /* $('select[name=\'product_type\']').change(function(){
        addShippingTab(productId,prodTypeDigital);
        });
        addShippingTab(productId,prodTypeDigital); */

        $('input[name=\'brand_name\']').autocomplete({
            'source': function(request, response) {
                /* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
                response($.map(json, function(item) {
                return { label: item['name'], value: item['id'] };
                }));
                }); */
                $.ajax({
                    url: fcom.makeUrl('brands', 'autoComplete'),
                    data: {
                        keyword: request,
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name=\'brand_name\']').val(item['label']);
                $('input[name=\'product_brand_id\']').val(item['value']);
            }
        });

        $('input[name=\'brand_name\']').keyup(function() {
            $('input[name=\'product_brand_id\']').val('');
        });

    });
</script>
