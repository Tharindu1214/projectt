<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if($paymentType == 'HOSTED'){ ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo CommonHelper::generateFullUrl('Image','paymentPageLogo',array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" /></div>
    <div class="reff row">
      <div class="col-lg-6 col-md-6 col-sm-12">
        <p class=""><?php echo Labels::getLabel('LBL_Payable_Amount',$siteLangId);?> : <strong><?php echo CommonHelper::displayMoneyFormat($paymentAmount)?></strong> </p>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-12">
        <p class=""><?php echo Labels::getLabel('LBL_Order_Invoice',$siteLangId);?>: <strong><?php echo $orderInfo["invoice"] ; ?></strong></p>
      </div>
    </div>
    <div class="payment-from">
		<?php if (!isset($error)): ?>
		   <p><?=Labels::getLabel('LBL_We_are_redirecting_payment_page',$siteLangId)?>:</p>
		<?php echo  $frm->getFormHtml(); ?>
		<?php else: ?>
			<div class="alert alert--danger"><?php echo $error; ?></div>
		<?php endif;?>
    </div>
  </div>
</div>
<script type="text/javascript">
    $(function(){
		setTimeout(function(){ $('form[name="frmTwoCheckout"]').submit() }, 5000);
	});
</script>	
<?php } else { ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo CommonHelper::generateFullUrl('Image','paymentPageLogo',array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" /></div>
    <div class="reff row">
      <div class="col-lg-6 col-md-6 col-sm-12">
        <p class=""><?php echo Labels::getLabel('LBL_Payable_Amount',$siteLangId);?> : <strong><?php echo CommonHelper::displayMoneyFormat($paymentAmount)?></strong> </p>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-12">
        <p class=""><?php echo Labels::getLabel('LBL_Order_Invoice',$siteLangId);?>: <strong><?php echo $orderInfo["invoice"] ; ?></strong></p>
      </div>
    </div>
    <div class="payment-from">
		<?php if (!isset($error)):
			/* $frm->setFormTagAttribute('onsubmit', 'sendPayment(this); return(false);'); */
			$frm->getField('ccNo')->setFieldTagAttribute('class','type-bg');
			$frm->getField('ccNo')->setFieldTagAttribute('id','ccNo');
			$frm->getField('cvv')->addFieldTagAttribute('class','type-bg');
			$frm->getField('cvv')->addFieldTagAttribute('id','cvv');
			$frm->getField('expMonth')->addFieldTagAttribute('id','expMonth');
			$frm->getField('expYear')->addFieldTagAttribute('id','expYear');
			echo $frm->getFormHtml(); ?>
		<?php else: ?>
			  <div class="alert alert--danger"><?php echo $error; ?></div>
		<?php endif;?>
		<div id="ajax_message"></div>
    </div>
  </div>
</div>	
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
<script>
var jq = $.noConflict();
var me = '';
var elmAjaxMsg = jq('#ajax_message');

// Called when token created successfully.
var successCallback = function(cdata) {
	var myForm = document.getElementById('frmTwoCheckout');
	// Set the token as the value for the token input
	myForm.token.value = cdata.response.token.token;
	// IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
	
	var data = fcom.frmData(myForm);
	data += '&outmode=json&is_ajax_request=yes';

	var action = $(myForm).attr('action');
	fcom.ajax(action, data, function(response) {
		try{
			var json = $.parseJSON(response);
			var elmAjaxMsg = $('#ajax_message');
			if (json['error']) {
				elmAjaxMsg.html('<div class="alert alert--danger">'+json['error']+'<div>');
			}
			if (json['redirect']) {
				$(location).attr("href",json['redirect']);
			}
		}catch(exc){
			console.log(t);
		}
	});

};

// Called when token creation fails.
var errorCallback = function(data) {
	// Retry the token request if ajax call fails
	if (data.errorCode === 200) {
	   // This error code indicates that the ajax call failed. We recommend that you retry the token request.
		tokenRequest();
	} else {	  
		me.data('requestRunning', false);
		elmAjaxMsg.html('<div class="alert alert--danger">'+data.errorMsg+'<div>');
	}
};

var tokenRequest = function() {
// Setup token request arguments
	var args = {
		sellerId: "<?php echo $sellerId; ?>",
		publishableKey: "<?php echo $publishableKey; ?>",
		ccNo: jq("#ccNo").val(),
		cvv: jq("#cvv").val(),
		expMonth: jq("#expMonth").val(),
		expYear: jq("#expYear").val()
	};
	// Make the token request
	TCO.requestToken(successCallback, errorCallback, args);
};

jq(function() {
	// Pull in the public encryption key for our environment
	TCO.loadPubKey("<?php echo $transaction_mode; ?>");
	jq("#frmTwoCheckout").submit(function(event) {
		me=jq(this);
		/* if (!me.validate()) return; */
		tokenRequest();
		return false;
	});
});
</script>
<?php
}