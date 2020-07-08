<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
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
		<p id="paymentStatus"></p>
		<?php
		if (isset($error))
			echo '<div class="error-wrap error"><p>'.$error.'</p></div>';
		if(isset($success))
			echo '<div class="success-message" style="color:green;"><p>Your payment has been successfull.</p></div>';
		
		if(strlen($orderId) > 0 && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING ){ ?>
			<div class="text-center" style="margin-top:40px;">
				<div id="addressBookWidgetDiv" style="width:400px; height:240px; display:inline-block;"></div>
				<div id="walletWidgetDiv" style="width:400px; height:240px; display:inline-block;"></div>
			</div>
			<div class="gap" ></div>
			<div class="amazon-submit-wrap">
				<a href="javascript:void(0);" class="amazon-submit btn btn--secondary" data-processing-txt='<?php echo Labels::getLabel('MSG_Please_Wait',$siteLangId); ?>' data-ready-txt='<?php echo Labels::getLabel('MSG_Confirm_Payment',$siteLangId); ?>' ><?php echo Labels::getLabel('MSG_Please_Wait',$siteLangId); ?></a>
				<a href="javascript:void(0);" onclick="logout();" class="btn btn--gray"><?php echo Labels::getLabel('LBL_Reset',$siteLangId); ?></a>
			</div>
			<?php 
		}
		?>
    </div>
  </div>
</div>
<?php 
if(isset($amazon) && strlen($orderId) > 0 &&  $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING){
	if( strlen($amazon['merchant_id']) > 0 && strlen($amazon['access_key']) > 0 && strlen($amazon['secret_key']) > 0 && strlen($amazon['client_id']) > 0 && strlen(FatApp::getConfig('CONF_TRANSACTION_MODE',FatUtility::VAR_STRING,'0')) > 0) {
		?>
		<script type="text/javascript">
			var redirectAfterSuccess = '<?php echo CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId), CONF_WEBROOT_URL); ?>';
			var orderId  = '<?php echo $orderId; ?>';
			window.onAmazonLoginReady = function () {
				amazon.Login.setClientId('<?php echo $amazon['client_id']; ?>');
				amazon.Login.setUseCookie(true);
			};
		</script>
		<script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js'></script>
		<script type="text/javascript">
			function logout(){
				amazon.Login.logout();
				document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
				window.location = '<?php echo CommonHelper::generateUrl('AmazonPay', 'charge', array($orderId), CONF_WEBROOT_URL)?>';
			}
			
			var orderRefId = false;
			new OffAmazonPayments.Widgets.AddressBook({
				sellerId: "<?php echo $amazon['merchant_id']; ?>",
				onOrderReferenceCreate: function (orderReference) {
				   var access_token = "";
					$.post("<?php echo CommonHelper::generateUrl('AmazonPay', 'get_details', array($orderId), CONF_WEBROOT_URL)?>", {
						orderReferenceId: orderReference.getAmazonOrderReferenceId(),
						addressConsentToken: access_token,
					}).done(function (data) {
						try{
							
							var jsonObj = $.parseJSON(data);
							if(jsonObj.hasOwnProperty('status') && jsonObj.hasOwnProperty('msg')){
								if(jsonObj.status == 0 && jsonObj.msg != ''){
									
									var jsonNewObj = $.parseJSON(jsonObj.msg);
									if(jsonNewObj.hasOwnProperty('Error')){
										
										if(jsonNewObj.Error.hasOwnProperty('Message')){
											
											if($('.payment-from').find('.error-wrap.error').length > 0){
												$('.payment-from .error-wrap.error').html('<p>'+jsonNewObj.Error.Message+'</p>');
											}else{
												$('.payment-from').prepend('<div class="error-wrap error"><p>'+jsonNewObj.Error.Message+'</p></div>');
											}
											
										}
										
									}
									
									logout();
									
								}else if(jsonObj.status == 1 && jsonObj.msg != ''){
									
									var jsonNewObj = $.parseJSON(jsonObj.msg);
									if(jsonNewObj.ResponseStatus == 200){
										
										orderRefId = jsonNewObj.GetOrderReferenceDetailsResult.OrderReferenceDetails.AmazonOrderReferenceId;
										
										if($('.payment-from').find('.amazon-submit-wrap').length  > 0){
											if($('.payment-from .amazon-submit-wrap').find('.amazon-submit').length  > 0){
												$('.payment-from .amazon-submit-wrap .amazon-submit').text('Confirm Payment');
											}
										}
										
									}
									
								}
							}else{
								console.log(data);
								logout();
							}
						} catch(e) {
							console.log(e.message);
							console.log(data);
							logout();
						}
					});
					
				},
				onAddressSelect: function (orderReference) {
					
				},
				design: {
					designMode: 'responsive'
				},
				onError: function (error) {
					console.log(error);
					logout();
				}
			}).bind("addressBookWidgetDiv");
			new OffAmazonPayments.Widgets.Wallet({
				sellerId: "<?php echo $amazon['merchant_id']; ?>",
				onPaymentSelect: function (orderReference) {
				},
				design: {
					designMode: 'responsive'
				},
				onError: function (error) {
					console.log(error);
					logout();
				}
			}).bind("walletWidgetDiv");
		</script>
		<?php 
		
	}
}