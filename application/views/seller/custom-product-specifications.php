<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<div class="tabs tabs--small tabs--scroll clearfix">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row row justify-content-between">
                <div class="col-md-auto">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Product_Specifications', $siteLangId); ?></h5>
                </div>
                <div class="col-md-auto">
                    <div class="action">
                        <div class="">
                            <?php if (is_array($prodSpec) && !empty($prodSpec)) { ?>
                            <a onclick="addProdSpec(<?php echo $product_id;?>)" href="javascript:void(0)" class="btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_Add_Specification', $siteLangId);?></a>
                            <?php }?>
                            <a href="<?php echo CommonHelper::generateUrl('Seller', 'sellerProductForm', array($product_id))?>" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Add_to_Store', $siteLangId);?></a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class=" " id="product_specifications_list"> </div>
                </div>
            </div>
        </div>
    </div>
</div>
