<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
	<div class="cc-payment">
		<div class="logo-payment">
			<img src="<?php echo CommonHelper::generateFullUrl('Image','paymentPageLogo',array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" />
		</div>
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
				<p><?php echo Labels::getLabel('L_We_are_redirecting_payment_page',$siteLangId); ?>:</p>
			<?php echo $frm->getFormHtml() ?>
			<?php else: ?>
				<div class="alert alert--danger"><?php echo $error?></div>
			<?php endif;?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	setTimeout(function(){ $('form[name="frmPayuIndia"]').submit() }, 5000);
});
</script>