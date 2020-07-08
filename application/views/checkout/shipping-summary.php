<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="check-login-wrapper">
    <?php
    $frmShippingApi->developerTags['colClassPrefix'] = 'col-md-';
    $frmShippingApi->developerTags['fld_default_col'] = 12;

    $frmShippingApi->setFormTagAttribute('onSubmit', 'setUpShippingApi(this); return false;');

    $shippingapi_idFld = $frmShippingApi->getField('shippingapi_id');
    $shippingapi_idFld->developerTags['col'] = 6;

    $btnSubmit = $frmShippingApi->getField('btn_submit');
    $btnSubmit->setFieldTagAttribute('class','btn btn--primary btn--sm btn--h-large');
    echo $frmShippingApi->getFormHtml(); ?>
</div>
<div id="shipping-summary-inner"></div>
