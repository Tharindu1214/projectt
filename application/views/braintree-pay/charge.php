<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
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
			<div class="waiting_message"><?php echo Labels::getLabel('LBL_Loading_Payment_options...',$siteLangId);?>
			<p><a onclick="loadLibrary();"><?php echo Labels::getLabel('LBL_Click_here',$siteLangId);?></a> <?php echo Labels::getLabel('LBL_if_loading_is_taking_more_than_15_seconds',$siteLangId);?></p>
			</div>

			<div id="dropin-container"></div>
			<?php echo $frm->getFormTag(); ?>
			<div class="row">
				<div class="col-md-12">
					<div class="field-set">
						<div class="caption-wraper">
							<label class="field_label"></label>
						</div>
						<div class="field-wraper">
							<div class="field_cover">
								<?php
									$payNowButton = $frm->getField('btn_submit');
									echo $payNowButton->getHTML();
								?>
								<a href="<?php echo $cancelBtnUrl; ?>" id="cancelLink" class="link link--normal"><?php echo Labels::getLabel('LBL_Cancel',$siteLangId);?></a>
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
<?php
if(isset($clientToken)){
	?>
	<script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>
	<script type="text/javascript">
		var paymentAmount = "<?php echo $paymentAmount;?>";
		var currencyCode = "<?php echo $currencyCode;?>";
		var clientToken = "<?php echo $clientToken;?>";
		loadLibrary( clientToken, paymentAmount, currencyCode );
	</script>
	<?php
}
?>
