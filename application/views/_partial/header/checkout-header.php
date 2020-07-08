<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
	$this->includeTemplate('restore-system/top-header.php');
    $this->includeTemplate('restore-system/page-content.php');
} ?>
<div class="wrapper">
    <header id="header-checkout" class="header-checkout" role="header-checkout">
        <?php /*
        <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-xs-6 d-none d-xl-block d-lg-block hide--mobile">
                        <div class="slogan">Instant Multi Vendor Ecommerce System Builder</div>
                    </div>
                    <div class="col-lg-8 col-xs-12">
                    </div>
                </div>
            </div>
        </div>
        */ ?>
        <div class="container">
            <div class="header-checkout-inner">
                <div class="logo"><a
                        href="<?php echo CommonHelper::generateUrl(); ?>"
                        class=""><img
                            src="<?php echo CommonHelper::generateFullUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>"
                            alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"
                            title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"></a>
                </div>


                <?php $backUrl = CommonHelper::generateUrl('Home');
                if ($controllerName == 'subscriptioncheckout') {
                    $backUrl = CommonHelper::generateUrl('Seller', 'Packages');
                } elseif ($controllerName == 'walletpay') {
                    $backUrl = CommonHelper::generateUrl('Account', 'Credits');
                } ?>

                <a href="<?php echo $backUrl; ?>"
                    class="btn btn--primary-border btn--sm back-store"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
                <?php if ($controllerName == 'checkout' || $controllerName == 'subscriptioncheckout') {   ?>
                <div class="checkout-flow">
                    <ul>
                        <?php if ($controllerName == 'checkout') {  ?>
                        <li class="pending checkoutNav-js billing-js" data-count="1"><span><?php echo Labels::getLabel('LBL_Billing', $siteLangId); ?></span>
                        </li>
                        <li class="pending checkoutNav-js shipping-js" data-count="2"><span><?php echo Labels::getLabel('LBL_Shipping', $siteLangId); ?></span>
                        </li>
                        <li class="pending checkoutNav-js payment-js" data-count="3"><span><?php echo Labels::getLabel('LBL_Payment', $siteLangId); ?></span>
                        </li>
                        <?php /*?><li class="pending checkoutNav-js order-complete-js" data-count="4"><span><?php echo Labels::getLabel('LBL_Order_Completed', $siteLangId); ?></span></li><?php */?>
                        <?php } else { ?>
                        <li class="pending checkoutNav-js billing-js" data-count="1"><span><?php echo Labels::getLabel('LBL_Billing', $siteLangId); ?></span>
                        </li>
                        <li class="pending checkoutNav-js payment-js" data-count="2"><span><?php echo Labels::getLabel('LBL_Payment', $siteLangId); ?></span>
                        </li>
                        <?php /*?><li class="pending checkoutNav-js order-complete-js" data-count="3"><span><?php echo Labels::getLabel('LBL_Order_Completed', $siteLangId); ?></span></li><?php */?>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
    </header>
    <div class="after-checkout-header"></div>
    <?php  /* echo FatUtility::decodeHtmlEntities( $headerData['epage_content'] ); */
