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
		
		
<?php if($paymentType == 'HOSTED'){  /* Hosted Checkout */ ?>

		<div class="payment-from">
			<?php if (!isset($error)): ?>
			   <p><?php echo Labels::getLabel('LBL_We_are_redirecting_payment_page',$siteLangId)?>:</p>
			<?php echo  $frm->getFormHtml(); ?>
			<?php else: ?>
				<div class="alert alert--danger"><?php echo $error; ?></div>
			<?php endif;?>
		</div>
		<script type="text/javascript">
			$(function(){
				setTimeout(function(){ $('form[name="frmTwoCheckout"]').submit() }, 5000);
			});
		</script>

<?php } else { /* API Checkout */ ?>

		<div class="payment-from">
			<?php if (!isset($error)):
			$frm->setFormTagAttribute('onsubmit','sendPayment(this);return false;');
			$frm->getField('ccNo')->setFieldTagAttribute('class','p-cards');
			$frm->getField('ccNo')->setFieldTagAttribute('id','ccNo');
			
			$frm->getField('cvv')->addFieldTagAttribute('id','cvv');
			$frm->getField('expMonth')->addFieldTagAttribute('id','expMonth');
			$frm->getField('expYear')->addFieldTagAttribute('id','expYear');
			echo $frm->getFormTag(); ?>
			<?php echo $frm->getFieldHtml('token'); ?>
			  <div class="row">
				<div class="col-md-12">
				  <div class="field-set">
					<div class="caption-wraper">
					  <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER',$siteLangId); ?></label>
					</div>
					<div class="field-wraper">
					  <div class="field_cover"> <?php echo $frm->getFieldHtml('ccNo'); ?> </div>
					</div>
				  </div>
				</div>
			  </div>
			  <div class="row">
				<div class="col-md-6">
				  <div class="caption-wraper">
					<label class="field_label"> <?php echo Labels::getLabel('LBL_CREDIT_CARD_EXPIRY',$siteLangId); ?> </label>
				  </div>
				  <div class="row">
					<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					  <div class="field-set">
						<div class="field-wraper">
						  <div class="field_cover">
							<?php
								$fld = $frm->getField('expMonth');
								echo $fld->getHtml();
							?>
						  </div>
						</div>
					  </div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					  <div class="field-set">
						<div class="field-wraper">
						  <div class="field_cover">
							<?php
								$fld = $frm->getField('expYear');
								echo $fld->getHtml();
							?>
						  </div>
						</div>
					  </div>
					</div>
				  </div>
				</div>
				<div class="col-md-6">
				  <div class="field-set">
					<div class="caption-wraper">
					  <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE',$siteLangId); ?></label>
					</div>
					<div class="field-wraper">
					  <div class="field_cover"> <?php echo $frm->getFieldHtml('cvv'); ?> </div>
					</div>
				  </div>
				</div>
			  </div>
			  <div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount)?> <small>(<?php echo Labels::getLabel('LBL_Total_Payable',$siteLangId);?>)</small> </div>
			  <div class="row">
				<div class="col-md-12">
				  <div class="field-set">
					<div class="caption-wraper">
					  <label class="field_label"></label>
					</div>
					<div class="field-wraper">
					  <div class="field_cover"> <?php echo $frm->getFieldHtml('btn_submit'); ?> <a href="<?php echo $cancelBtnUrl; ?>" class="link link--normal"><?php echo Labels::getLabel('LBL_Cancel',$siteLangId);?></a> </div>
					</div>
				  </div>
				</div>
			  </div>
			  </form>
			  <?php echo $frm->getExternalJs(); ?>
			<?php else: ?>
				  <div class="alert alert--danger"><?php echo $error; ?></div>
			<?php endif;?>
			<div id="ajax_message"></div>
		</div>
		<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
		<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
		<script type="text/javascript">
			$("#ccNo" ).keydown(function() {
				var obj = $(this);
				var cc = obj.val();
				obj.attr('class','p-cards');
				if(cc != ''){
					var data="cc="+cc;
					fcom.ajax(fcom.makeUrl('AuthorizeAimPay', 'checkCardType'), data, function(t){
						var ans = $.parseJSON(t);
						var card_type = ans.card_type.toLowerCase();
						obj.addClass('type-bg p-cards ' + card_type );
					});
				}
			});
			
			var frmApiCheckout = '';
			
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
						
						if (json['error']) {
							$('#ajax_message').html('<div class="alert alert--danger">'+json['error']+'</div>');
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
					frmApiCheckout.data('requestRunning', false);
					$('#ajax_message').html('<div class="alert alert--danger">'+data.errorMsg+'</div>');
				}
			};

			var tokenRequest = function() {
			// Setup token request arguments
				var args = {
					sellerId: "<?php echo $sellerId; ?>",
					publishableKey: "<?php echo $publishableKey; ?>",
					ccNo: $("#ccNo").val(),
					cvv: $("#cvv").val(),
					expMonth: $("#expMonth").val(),
					expYear: $("#expYear").val()
				};
				// Make the token request
				TCO.requestToken(successCallback, errorCallback, args);
			};

			// Pull in the public encryption key for our environment
			TCO.loadPubKey("<?php echo $transaction_mode; ?>");
			
			var sendPayment = function(frm){
				if (!$(frm).validate()) return;
				frmApiCheckout = $(frm);
				$(frm).data('requestRunning', false);
				tokenRequest();
				return false;
			};
		</script>
<?php } ?>
	</div>
</div>