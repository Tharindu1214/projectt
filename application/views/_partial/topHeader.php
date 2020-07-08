<div class="outdated">
	<div class="outdated-inner">
		<div class="outdated-messages">
			<h2>The browser you are using is not supported. Some critical security features are not available for your browser version.</h2>
			<p>We want you to have the best possible experience with FATbit. For this you'll need to use a supported browser and upgrade to the latest version. </p>
			<ul class="list-browser">
				<li><a href="https://www.google.com/chrome" target="_blank"><i class="icn chrome"></i>
						<p><strong>Chrome</strong><br>
							<span>Get the latest version</span></p>
					</a></li>
				<li><a href="https://getfirefox.com" target="_blank"><i class="icn firefox"></i>
						<p><strong>Firefox</strong><br>
							<span>Get the latest version</span></p>
					</a></li>
				<li><a href="http://support.apple.com/downloads/#safari" target="_blank"><i class="icn safari"></i>
						<p><strong>Safari</strong><br>
							<span>Get the latest version</span></p>
					</a></li>
				<li><a href="http://getie.com" target="_blank"><i class="icn internetexplorer"></i>
						<p><strong>Internet Explorer</strong><br>
							<span>Get the latest version</span></p>
					</a></li>
			</ul>
		</div>
	</div>
</div>
<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
	$this->includeTemplate('restore-system/top-header.php');
    $this->includeTemplate('restore-system/page-content.php');
} ?>
<div class="wrapper">
	<div id="loader-wrapper">
		<div class="yokart-loader"><img src="<?php echo CONF_WEBROOT_URL;?>images/retina/yokart-loader.svg"></div>
		<div class="loader-section section-left"></div>
		<div class="loader-section section-right"></div>
	</div>
	<!--header start here-->
	<header id="header" class="header no-print" role="site-header">
		<div class="top-bar">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6 col-xs-6 d-none d-xl-block d-lg-block hide--mobile">
						<div class="slogan"><?php echo Labels::getLabel('LBL_Multi-vendor_Ecommerce_Marketplace_Solution', $siteLangId); ?></div>
					</div>
					<div class="col-lg-6 col-xs-12">
						<div class="short-links">
							<ul>
								<?php $this->includeTemplate('_partial/headerTopNavigation.php'); ?> <?php $this->includeTemplate('_partial/headerLanguageArea.php'); ?> <?php $this->includeTemplate('_partial/headerUserArea.php'); ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="main-bar">
			<div class="container">
				<a class="navs_toggle" href="javascript:void(0)"><span></span></a>
				<div class="logo-bar">
					<?php
                    if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
                        $logoUrl = CommonHelper::generateUrl('home', 'index');
                    } else {
                        $logoUrl = CommonHelper::generateUrl();
                    }
                    ?>
					<div class="logo">
						<a href="<?php echo $logoUrl; ?>">
							<img data-ratio="16:9" src="<?php echo CommonHelper::generateFullUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId, FatUtility::VAR_STRING, '') ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId, FatUtility::VAR_STRING, '') ?>">
						</a>
					</div>
					<?php $this->includeTemplate('_partial/headerSearchFormArea.php'); ?>
					<div class="cart dropdown" id="cartSummary">
						<?php $this->includeTemplate('_partial/headerWishListAndCartSummary.php'); ?>
					</div>
				</div>
			</div>
		</div> <?php $this->includeTemplate('_partial/headerNavigation.php'); ?>
	</header>
	<div class="after-header no-print"></div>
	<!--header end here-->
	<!--body start here-->
