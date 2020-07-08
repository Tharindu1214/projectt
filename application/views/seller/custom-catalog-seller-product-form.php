<div class="tabs tabs--small tabs--scroll clearfix align-items-center">
    <?php require_once(CONF_THEME_PATH.'_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4">
        <div class="row">
            <div class="col-md-12">
                <div class="form__subcontent">
                    <?php
                    $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpCustomSellerProduct(this); return(false);');
                    $frmSellerProduct->setFormTagAttribute('class', 'form form--horizontal');
                    $frmSellerProduct->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                    $frmSellerProduct->developerTags['fld_default_col'] = 4;
                    /* $optionSectionHeading = $frmSellerProduct->getField('optionSectionHeading');
                    $optionSectionHeading->value = '<h2>Set Up Options</h2>';
                    //TODO:: Make, final word from language labels. */
                    /* $submitBtn = $frmSellerProduct->getField('btn_submit');
                    $submitBtn->setFieldTagAttribute('class','btn btn--primary btn--sm');

                    $cancelBtn = $frmSellerProduct->getField('btn_cancel');
                    $cancelBtn->setFieldTagAttribute('class','btn btn--secondary btn--sm'); */

                    $selprod_threshold_stock_levelFld = $frmSellerProduct->getField('selprod_threshold_stock_level');
                    $selprod_threshold_stock_levelFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Alert_stock_level_hint_info', $siteLangId). '</small>';
                    $selprod_threshold_stock_levelFld->setWrapperAttribute('class', 'selprod_threshold_stock_level_fld');
                    $urlFld = $frmSellerProduct->getField('selprod_url_keyword');
                    $urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
                    $urlFld->htmlAfterField = "<small class='text--small'>".Labels::getLabel('LBL_Example:', $siteLangId) . ' ' . CommonHelper::generateFullUrl('yourKeyword').'</small>';
                    /* $selprodCodEnabledFld = $frmSellerProduct->getField('selprod_cod_enabled');
                    $selprodCodEnabledFld->setWrapperAttribute( 'class' , 'selprod_cod_enabled_fld'); */
                    // $frmSellerProduct->getField('selprod_price')->addFieldtagAttribute('placeholder', CommonHelper::getPlaceholderForAmtField($siteLangId));
                    echo $frmSellerProduct->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        var INVENTORY_TRACK = <?php echo Product::INVENTORY_TRACK; ?>;
        var INVENTORY_NOT_TRACK = <?php echo Product::INVENTORY_NOT_TRACK; ?>;

        $("select[name='selprod_track_inventory']").change(function() {
            if ($(this).val() == INVENTORY_TRACK) {
                $("input[name='selprod_threshold_stock_level']").removeAttr("disabled");
            }

            if ($(this).val() == INVENTORY_NOT_TRACK) {
                $("input[name='selprod_threshold_stock_level']").val(0);
                $("input[name='selprod_threshold_stock_level']").attr("disabled", "disabled");
            }
        });

        $("select[name='selprod_track_inventory']").trigger('change');
    });
</script>
