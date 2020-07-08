<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
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
            <?php  if (!isset($error)) { ?>
                <p>
                    <?php echo Labels::getLabel('MSG_We_are_redirecting_to_payment_page', $siteLangId); ?>
                </p>
				<div class="row form">
					<div class="col-3">
						<div class="field-set">
							<div class="caption-wraper">
								<label class="field_label"></label>
							</div>
							<div class="field-wraper">
								<div class="field_cover">
									<input id="payhere-payment" type="submit"  value="Confirm Payment">
								</div>
							</div>
						</div>
					</div>
				</div>
           <?php } else { ?>
                <div class="alert alert--danger"> <?php echo $error; ?></div>
            <?php } ?>
        </div>
    </div>
</div>
<?php

/* echo "<pre>";
print_r($requestParams);
print_r($orderInfo); */
?>
<style>
#ph-iframe {
	width:100% !important;
}
</style>
<script>
    // Called when user completed the payment. It can be a successful payment or failure
    payhere.onCompleted = function onCompleted(orderId) {
        console.log("Payment completed. OrderID:" + orderId);
		setTimeout(function(){ window.location.href = '<?php echo $requestParams["return_url"] ; ?>'; }, 1000);
		
        //Note: validate the payment and show success or failure page to the customer
    };

    // Called when user closes the payment without completing
    payhere.onDismissed = function onDismissed() {
        //Note: Prompt user to pay again or show an error page
        console.log("Payment dismissed");
		$('#payhere-payment').attr('disabled',false);
		$("#payhere-payment").val("Confirm Payment");
    };

    // Called when error happens when initializing payment such as invalid parameters
    payhere.onError = function onError(error) {
        // Note: show an error page
        console.log("Error:"  + error);
		<?php //Message::addErrorMessage("Sorry, your payment failed. No charges were made. Please try Again."); ?>
		location.reload();
    };

    // Put the payment variables here
    var payment = {
        "sandbox": false,
        "merchant_id": "<?php echo $requestParams["merchant_id"] ; ?>",       // Replace your Merchant ID
        "return_url": "<?php echo $requestParams["return_url"] ; ?>",
        "cancel_url": "",
        "notify_url": "<?php echo str_replace("do-payment","notify-payment",$requestParams["return_url"]); ?>",
        "order_id": "<?php echo $orderInfo["invoice"] ; ?>",
        "items": "Order Invoice: <?php echo $orderInfo["invoice"] ; ?>",
        "amount": "<?php echo $requestParams["amount"] ; ?>",
        "currency": "<?php echo $requestParams["currency"] ; ?>",
        "first_name": "<?php echo $orderInfo["customer_name"] ; ?>",
        "last_name": "",
        "email": "<?php echo $orderInfo["customer_email"] ; ?>",
        "phone": "<?php echo $orderInfo["customer_phone"] ; ?>",
        "address": "<?php echo $orderInfo["customer_billing_address_1"] ; ?>",
        "city": "<?php echo $orderInfo["customer_billing_city"] ; ?>",
        "country": "<?php echo $orderInfo["customer_billing_country"] ; ?>",
        "delivery_address": "<?php echo $orderInfo["customer_billing_address_1"] ; ?>",
        "delivery_city": "<?php echo $orderInfo["customer_billing_city"] ; ?>",
        "delivery_country": "<?php echo $orderInfo["customer_billing_country"] ; ?>"
    };

    // Show the payhere.js popup, when "PayHere Pay" is clicked
	
	$(document).ready(function(){
		$(document).on('click','#payhere-payment',function(){
			$('#payhere-payment').attr('disabled',true);
			payhere.startPayment(payment);
			$("#payhere-payment").val("Please wait");
		});
	});
</script>
