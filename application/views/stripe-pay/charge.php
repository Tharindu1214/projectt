<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
if (isset($stripe)) {
    if (isset($stripe['secret_key']) && isset($stripe['publishable_key'])) {
        if (!empty($stripe['secret_key']) && !empty($stripe['publishable_key'])) { ?>
            <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
            <script type="text/javascript">
                var publishable_key = '<?php echo $stripe['publishable_key']; ?>';
            </script>
        <?php }
    }
}
?>
<div class="payment-page">
    <div class="cc-payment">
        <div class="logo-payment"><img src="<?php echo CommonHelper::generateFullUrl('Image', 'paymentPageLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"
                title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" /></div>
        <div class="reff row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <p class=""><?php echo Labels::getLabel('LBL_Payable_Amount', $siteLangId);?> : <strong><?php echo CommonHelper::displayMoneyFormat($paymentAmount)?></strong> </p>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <p class=""><?php echo Labels::getLabel('LBL_Order_Invoice', $siteLangId);?>: <strong><?php echo $orderInfo["invoice"] ; ?></strong></p>
            </div>
        </div>
        <div class="payment-from">
            <?php if (!isset($error)):
            /* $frm->setFormTagAttribute('onsubmit', 'sendPayment(this); return(false);'); */
            $fld = $frm->getField('cc_number');
            $fld->addFieldTagAttribute('class', 'p-cards');
            $fld->addFieldTagAttribute('id', 'cc_number');
            $fld = $frm->getField('cc_owner');
            $fld->addFieldTagAttribute('id', 'cc_owner');
            $fld = $frm->getField('cc_cvv');
            $fld->addFieldTagAttribute('id', 'cc_cvv'); ?>
            <?php echo $frm->getFormTag(); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('cc_number'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('LBL_CARD_HOLDER_NAME', $siteLangId); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('cc_owner'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="caption-wraper">
                        <label class="field_label"> <?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?> </label>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php
                            $fld = $frm->getField('cc_expire_date_month');
                            $fld->addFieldTagAttribute('id', 'cc_expire_date_month');
                            $fld->addFieldTagAttribute('class', 'ccExpMonth  combobox required');
                            echo $fld->getHtml(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php
                        $fld = $frm->getField('cc_expire_date_year');
                        $fld->addFieldTagAttribute('id', 'cc_expire_date_year');
                        $fld->addFieldTagAttribute('class', 'ccExpYear combobox required');
                        echo $fld->getHtml(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE', $siteLangId); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('cc_cvv'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php /* <div class="row">
        <div class="col-md-12">
          <div class="field-set">
            <div class="caption-wraper">
              <label class="field_label"></label>
            </div>
            <div class="field-wraper">
              <div class="field_cover">
                <label class="checkbox">
                  <?php
                    $fld = $frm->getField('cc_save_card');
                    $fld->addFieldTagAttribute('onclick','alert("|SAVE THIS CARD| Not Functional!");return false;');
                    $fldHtml = $fld->getHTML();
                    $fldHtml = str_replace("<label >","",$fldHtml);
                    $fldHtml = str_replace("</label>","",$fldHtml);
                    echo $fldHtml;
                    ?>
            <i class="input-helper"></i> </label>
        </div>
    </div>
</div>
</div>
</div> */ ?>
<div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount)?> <small>(<?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId);?>)</small> </div>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="caption-wraper">
                <label class="field_label"></label>
            </div>
            <div class="field-wraper">
                <div class="field_cover">
                    <?php $frm->getField('btn_submit')->addFieldTagAttribute('data-processing-text', Labels::getLabel('L_Please_Wait..', $siteLangId));
                    echo $frm->getFieldHtml('btn_submit'); ?>
                    <a href="<?php echo $cancelBtnUrl; ?>" class="link link--normal"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId);?></a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<?php echo $frm->getExternalJs(); ?>
<?php else: ?>
<div class="alert alert--danger"><?php echo $error?></div>
<?php endif;?>
<div id="ajax_message"></div>
</div>
</div>
</div>
