<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $shopLangFrm->setFormTagAttribute('onsubmit', 'setupShopLang(this); return(false);'); */
$shopLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);

$shopLangFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
$shopLangFrm->developerTags['fld_default_col'] = 4;

$paymentPolicyfld = $shopLangFrm->getField('shop_payment_policy');
$paymentPolicyfld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Shop_payment_terms_comments', $formLangId).'</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_delivery_policy');
$paymentPolicyfld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Shop_delivery_policy_comments', $formLangId).'</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_refund_policy');
$paymentPolicyfld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Shop_refund_policy_comments', $formLangId).'</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_additional_info');
$paymentPolicyfld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Shop_additional_info_comments', $formLangId).'</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_seller_info');
$paymentPolicyfld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Shop_seller_info_comments', $formLangId).'</small>';

?>

<?php     $variables= array('formLangId'=>$formLangId, 'language'=>$language,'siteLangId'=>$siteLangId,'shop_id'=>$shop_id,'action'=>$action);

$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row ">
                <div class="col-lg-12 col-md-12" id="shopFormBlock">
                    <?php echo $shopLangFrm->getFormTag();
                    echo $shopLangFrm->getFormHtml(false);
                    echo '</form>'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
