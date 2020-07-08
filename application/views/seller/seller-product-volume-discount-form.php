<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="popup__body">
    <h2><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId);?></h2>
    <?php
    $frmSellerProductVolDiscount->setFormTagAttribute('onsubmit', 'setUpSellerProductVolumeDiscount(this); return(false);');
    $frmSellerProductVolDiscount->setFormTagAttribute('class', 'form');
    $frmSellerProductVolDiscount->developerTags['colClassPrefix'] = 'col-md-';
    $frmSellerProductVolDiscount->developerTags['fld_default_col'] = 6;

    $btnCancelFld = $frmSellerProductVolDiscount->getField('btn_cancel');
    $btnCancelFld->setFieldTagAttribute('onClick', 'sellerProductVolumeDiscounts(' . $selprod_id . ');');
    echo $frmSellerProductVolDiscount->getFormHtml(); ?>
</div>
