<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'bankInfoFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-lg-3 col-md-';
$frm->developerTags['fld_default_col'] = 3;
$frm->setFormTagAttribute('onsubmit', 'setUpAffiliatePaymentInfo(this); return(false);');

$radioFld = $frm->getField('uextra_payment_method');
$radioFld->setWrapperAttribute('class', 'col-lg-12');
$radioFld->developerTags['col'] = 12;

$checkPayeeNameFld = $frm->getField('uextra_cheque_payee_name');
$checkPayeeNameFld->setWrapperAttribute('class', 'cheque_payment_method_fld');

$bankNameFld = $frm->getField('ub_bank_name');
$bankNameFld->setWrapperAttribute('class', 'bank_payment_method_fld');

$bankAccountNameFld = $frm->getField('ub_account_holder_name');
$bankAccountNameFld->setWrapperAttribute('class', 'bank_payment_method_fld');

$bankAccountNumberFld = $frm->getField('ub_account_number');
$bankAccountNumberFld->setWrapperAttribute('class', 'bank_payment_method_fld');

$bankSwiftCodeFld = $frm->getField('ub_ifsc_swift_code');
$bankSwiftCodeFld->setWrapperAttribute('class', 'bank_payment_method_fld');

$bankSwiftCodeFld = $frm->getField('ub_bank_address');
$bankSwiftCodeFld->setWrapperAttribute('class', 'bank_payment_method_fld');

$PayPalEmailIdFld = $frm->getField('uextra_paypal_email_id');
$PayPalEmailIdFld->setWrapperAttribute('class', 'paypal_payment_method_fld');

$submitFld = $frm->getField('btn_submit');
$submitFld->developerTags['noCaptionTag'] = true; ?>

<div class="row">
    <div class="col-md-12">
        <?php echo $frm->getFormHtml();?>
    </div>
</div>

<script type="text/javascript">
$("document").ready( function(){
    var AFFILIATE_PAYMENT_METHOD_CHEQUE = '<?php echo User::AFFILIATE_PAYMENT_METHOD_CHEQUE; ?>';
    var AFFILIATE_PAYMENT_METHOD_BANK = '<?php echo User::AFFILIATE_PAYMENT_METHOD_BANK; ?>';
    var AFFILIATE_PAYMENT_METHOD_PAYPAL = '<?php echo User::AFFILIATE_PAYMENT_METHOD_PAYPAL; ?>';

    var uextra_payment_method = '<?php echo $userExtraData['uextra_payment_method']; ?>';

    $("input[name='uextra_payment_method']").change(function(){
        if( $(this).val() == AFFILIATE_PAYMENT_METHOD_CHEQUE ){
            callChequePaymentMethod();
        }

        if( $(this).val() == AFFILIATE_PAYMENT_METHOD_BANK ){
            callBankPaymentMethod();
        }

        if( $(this).val() == AFFILIATE_PAYMENT_METHOD_PAYPAL ){
            callPayPalPaymentMethod();
        }
    });


    if( uextra_payment_method == AFFILIATE_PAYMENT_METHOD_CHEQUE ){
        callChequePaymentMethod();
    }
    if( uextra_payment_method == AFFILIATE_PAYMENT_METHOD_BANK ){
        callBankPaymentMethod();
    }
    if( uextra_payment_method == AFFILIATE_PAYMENT_METHOD_PAYPAL ){
        callPayPalPaymentMethod();
    }

} );

function callChequePaymentMethod(){
    $(".cheque_payment_method_fld").show();
    $(".bank_payment_method_fld").hide();
    $(".paypal_payment_method_fld").hide();
}

function callBankPaymentMethod(){
    $(".cheque_payment_method_fld").hide();
    $(".bank_payment_method_fld").show();
    $(".paypal_payment_method_fld").hide();
}

function callPayPalPaymentMethod(){
    $(".cheque_payment_method_fld").hide();
    $(".bank_payment_method_fld").hide();
    $(".paypal_payment_method_fld").show();
}
</script>
