<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="col-lg-12 col-md-12">
    <div class="content-header justify-content-between row mb-4">
        <div class="content-header-left col-md-auto"><h5 class="cards-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5></div>
        <div class="content-header-right col-auto">
            <div class="form__group">
                <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-12 col-md-12">
    <div class="">
        <div class="tabs tabs-sm tabs--scroll clearfix">
            <ul>
                <li ><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId);?></a></li>
                <?php
                foreach ($language as $lang_id => $langName) {?>
                <li class=""><a href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
                    <?php echo $langName;?></a></li>
                <?php } ?>
                <li class="is-active">
                    <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_LINK', $siteLangId);?> </a>
                </li>
                <li class=""><a
                <?php if ($scollection_id > 0) {?>
                    onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);"
                <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId);?></a></li>
            </ul>
        </div>
    </div>
    <div class="form__subcontent">
        <div class="col-lg-6 col-md-6">
            <?php
            $sellerCollectionproductLinkFrm->setFormTagAttribute('onsubmit', 'setUpSellerCollectionProductLinks(this); return(false);');
            $sellerCollectionproductLinkFrm->setFormTagAttribute('class', 'form form--horizontal');
            $sellerCollectionproductLinkFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md- ';
            $sellerCollectionproductLinkFrm->developerTags['fld_default_col'] = 12;

            echo $sellerCollectionproductLinkFrm->getFormHtml(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
$("document").ready(function(){

    $('#selprod-products').on('click', '.remove_link', function() {

        $(this).parent().remove();
    });

});

    <?php
    if (isset($products) && !empty($products)) {
        foreach ($products as $key => $val) { ?>
        $('#selprod-products ul').append("<li id=\"selprod-products<?php echo $val['selprod_id'];?>\"><i class=\"remove_param fa fa-remove remove_link\"></i> <?php echo $val['product_name'];?>[<?php echo $val['product_identifier'];?>]<input type=\"hidden\"  name=\"product_ids[]\" value=\"<?php echo $val['selprod_id'];?>\" /></li>");
        <?php }
    } ?>


</script>
