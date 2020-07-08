<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$submitBtnFld = $frmSellerProduct->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn--primary');

$cancelBtnFld = $frmSellerProduct->getField('btn_cancel');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn--primary-border');
$submitBtnFld->developerTags['col'] = 12;
?>
<div class="tabs tabs--small tabs--scroll clearfix">
    <?php require_once('sellerCatalogProductTop.php');?>
</div>
<div class="cards">
<div class="cards-content pt-3 pl-4 pr-4 ">
    <div class="tabs__content form">
    <div class="row">
    <div class="col-md-12">
    <div class="">
    <div class="tabs tabs-sm tabs--scroll clearfix">
    <ul>
    <li class="is-active"><a href="javascript:void(0)"
    <?php if ($selprod_id > 0) {
        ?> onClick="sellerProductForm(<?php echo $product_id, ',', $selprod_id ?>)" <?php
    }?>><?php echo Labels::getLabel('LBL_Basic', $siteLangId); ?></a></li>
    <?php $inactive = ($selprod_id==0)?'fat-inactive':'';
    foreach ($language as $langId => $langName) { ?>
    <li class="<?php echo $inactive ; ?>"><a href="javascript:void(0)" <?php if ($selprod_id > 0) {
        ?> onClick="sellerProductLangForm (<?php echo $langId; ?>, <?php echo $selprod_id; ?>)" <?php
               } ?>>
    <?php echo $langName;?></a></li>
    <?php } ?>
    <li class="<?php echo $inactive ; ?>"><a href="javascript:void(0)" <?php if($selprod_id>0){?> onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)" <?php }?>><?php echo Labels::getLabel('LBL_Link_Warranty_Policies', $siteLangId); ?></a></li>
    <li class="<?php echo $inactive ; ?>"><a href="javascript:void(0)" <?php if($selprod_id>0){?> onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)" <?php }?>><?php echo Labels::getLabel('LBL_Link_Return_Policies', $siteLangId); ?></a></li>

    </ul>
    </div>
    </div>
    <div class="form__subcontent">
    <?php
    $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpSellerProduct(this); return(false);');
    $frmSellerProduct->setFormTagAttribute('class', 'form form--horizontal');
    $frmSellerProduct->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
        $frmSellerProduct->developerTags['fld_default_col'] = 4;
    /* $optionSectionHeading = $frmSellerProduct->getField('optionSectionHeading');
    $optionSectionHeading->value = '<h2>Set Up Options</h2>'; //TODO:: Make, final word from language labels. */
    /* $submitBtn = $frmSellerProduct->getField('btn_submit');
    $submitBtn->setFieldTagAttribute('class','btn btn--primary btn--sm');

    $cancelBtn = $frmSellerProduct->getField('btn_cancel');
    $cancelBtn->setFieldTagAttribute('class','btn btn--secondary btn--sm'); */


    $selprod_threshold_stock_levelFld = $frmSellerProduct->getField('selprod_threshold_stock_level');
    $selprod_threshold_stock_levelFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Alert_stock_level_hint_info', $siteLangId). '</small>';
    $selprod_threshold_stock_levelFld->setWrapperAttribute('class', 'selprod_threshold_stock_level_fld');
    $urlFld = $frmSellerProduct->getField('selprod_url_keyword');
    $urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
    $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value, $selprod_id, 'post')");
    $urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Products', 'View', array($selprod_id), '/').'</small>';
    $selprodCodEnabledFld = $frmSellerProduct->getField('selprod_cod_enabled');
    $selprodCodEnabledFld->setWrapperAttribute('class', 'selprod_cod_enabled_fld');
    // $frmSellerProduct->getField('selprod_price')->addFieldtagAttribute('placeholder', CommonHelper::getPlaceholderForAmtField($siteLangId));
    echo $frmSellerProduct->getFormHtml(); ?>
    </div>
    </div>
    </div>
    </div>
</div>
</div>
<?php echo FatUtility::createHiddenFormFromData(array('product_id' => $product_id), array('name' => 'frmSearchSellerProducts'));?>
<script type="text/javascript">
    var PERCENTAGE = <?php echo applicationConstants::PERCENTAGE; ?>;
    var FLAT = <?php echo applicationConstants::FLAT; ?>;

$("document").ready(function(){
    var INVENTORY_TRACK = <?php echo Product::INVENTORY_TRACK; ?>;
    var INVENTORY_NOT_TRACK = <?php echo Product::INVENTORY_NOT_TRACK; ?>;

    var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
    var productType = <?php echo $product_type; ?>;
    var shippedBySeller = <?php echo $shippedBySeller; ?>;

    if( productType == PRODUCT_TYPE_DIGITAL || shippedBySeller==0)
    {
    $(".selprod_cod_enabled_fld").hide();
    }

    $("select[name='selprod_track_inventory']").change(function(){
    if( $(this).val() == INVENTORY_TRACK ){
    $("input[name='selprod_threshold_stock_level']").removeAttr("disabled");
    }

    if( $(this).val() == INVENTORY_NOT_TRACK ){
    $("input[name='selprod_threshold_stock_level']").val(0);
    $("input[name='selprod_threshold_stock_level']").attr("disabled", "disabled");
    }
    });

    $("select[name='selprod_track_inventory']").trigger('change');
});
</script>
