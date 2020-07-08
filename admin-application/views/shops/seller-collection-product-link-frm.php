<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($scollection_id) && $scollection_id >0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
} ?>
<ul class="tabs_nav tabs_nav--internal">
    <li>
        <a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);" href="javascript:void(0)">
            <?php echo Labels::getLabel('TXT_GENERAL_link', $adminLangId);?>
        </a>
    </li>
    <?php foreach ($language as $lang_id => $langName) { ?>
    <li class="">
        <a href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $shop_id;?>, <?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
            <?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?>
        </a>
    </li>
    <?php } ?>
    <li>
        <a class="active" onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>,<?php echo $shop_id; ?>);" href="javascript:void(0);">
            <?php echo Labels::getLabel('TXT_LINK', $adminLangId);?>
        </a>
    </li>
    <li> 
        <a onclick="collectionMediaForm(<?php echo $shop_id ?>, <?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId);?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <div class="form__subcontent">
        <?php
        $sellerCollectionproductLinkFrm->setFormTagAttribute('onsubmit', 'setUpSellerCollectionProductLinks(this); return(false);');
        $sellerCollectionproductLinkFrm->setFormTagAttribute('class', 'form form_horizontal web_form');
         $sellerCollectionproductLinkFrm->developerTags['colClassPrefix'] = 'col-md-';
        $sellerCollectionproductLinkFrm->developerTags['fld_default_col'] = 12;
        $sellerCollectionproductLinkFrm->addHiddenField('', 'shop_id', $shop_id);
        echo $sellerCollectionproductLinkFrm->getFormHtml(); ?>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        $('#selprod-products ul').on('click', '.remove_buyTogether', function() {
        /* $('#selprod-products ul').delegate('.remove_buyTogether', 'click', function() { */
            $(this).parent().remove();
        });
    });
    <?php
    if (isset($products) && !empty($products)) {
        foreach ($products as $key => $val) { ?>
    $('#selprod-products ul').append("<li id=\"selprod-products<?php echo $val['selprod_id'];?>\"><i class=\" remove_buyTogether icon ion-close\"></i><?php echo $val['product_name'];?>[<?php echo $val['product_identifier'];?>]<input type=\"hidden\" name=\"product_ids[]\" value=\"<?php echo $val['selprod_id'];?>\" /></li>");
        <?php }
    }?>
</script>
