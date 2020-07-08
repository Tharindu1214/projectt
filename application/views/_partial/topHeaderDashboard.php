<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
	$this->includeTemplate('restore-system/top-header.php');
    $this->includeTemplate('restore-system/page-content.php');
} ?>
<div class="wrapper">
    <header id="header-dashboard" class="header-dashboard no-print" role="header-dashboard">
        <?php if ((User::canViewSupplierTab() && User::canViewBuyerTab()) || (User::canViewSupplierTab() && User::canViewAdvertiserTab()) || (User::canViewBuyerTab() && User::canViewAdvertiserTab())) { ?>
        <div class="dropdown dropdown--arrow user-type">
            <a href="javascript:void(0)" class="dropdown__trigger dropdown__trigger-js">
                <span><?php echo ($activeTab == 'S') ? Labels::getLabel('Lbl_Seller', $siteLangId) : (($activeTab == 'B') ? Labels::getLabel('Lbl_Buyer', $siteLangId) : (($activeTab == 'Ad') ? Labels::getLabel('Lbl_Advertiser', $siteLangId) : '')) ?></span><i class="chevron"></i></a>
            <div class="dropdown__target dropdown__target-lang dropdown__target-js">
                <div class="dropdown__target-space">
                    <!--<span class="expand-heading">User</span>-->
                    <ul class="list-vertical list-vertical--tick">
                        <?php if (User::canViewSupplierTab()) { ?>
                        <li <?php if ($activeTab == 'S') {
                             echo 'class="is-active"';
                            } ?>>
                            <a href="<?php echo CommonHelper::generateUrl('Seller'); ?>"><?php echo Labels::getLabel('Lbl_Seller', $siteLangId);?></a></li>
                        <?php }?>
                        <?php if (User::canViewBuyerTab()) { ?>
                        <li <?php if ($activeTab == 'B') {
                            echo 'class="is-active"';
                            } ?>>
                            <a href="<?php echo CommonHelper::generateUrl('Buyer'); ?>"><?php echo Labels::getLabel('Lbl_Buyer', $siteLangId);?></a></li>
                        <?php }?>
                        <?php if (User::canViewAdvertiserTab()) { ?>
                        <li <?php if ($activeTab == 'Ad') {
                            echo 'class="is-active"';
                            } ?>>
                            <a href="<?php echo CommonHelper::generateUrl('Advertiser'); ?>"><?php echo Labels::getLabel('Lbl_Advertiser', $siteLangId);?></a></li>
                        <?php }?>
                    </ul>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="header-icons-group">
            <?php $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false; ?>
            <ul class="c-header-links">
                <li class="<?php /* echo (($controllerName == 'Seller' || $controllerName == 'Buyer' || $controllerName == 'Advertiser' || $controllerName == 'Affiliate') && $action == 'index') ? 'is-active' : ''; */ ?>"><a title="<?php echo Labels::getLabel('LBL_Dashboard', $siteLangId);?>" data-org-url="<?php echo CommonHelper::generateUrl('home', 'index', array(), '', null, false, $getOrgUrl); ?>" href="<?php echo CommonHelper::generateUrl($controllerName); ?>"><i class="icn icn--dashboard">
                <svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#dashboard" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#dashboard"></use></svg></i></a></li>
                <li><a title="<?php echo Labels::getLabel('LBL_Home', $siteLangId);?>" target="_blank" href="<?php echo CommonHelper::generateUrl('Home'); ?>"><i class="icn icn--home">
                <svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#back-home" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#back-home"></use></svg></i></a></li>
                <?php if ($isShopActive && $shop_id > 0 && $activeTab == 'S') { ?>
                <li><a title="<?php echo Labels::getLabel('LBL_Shop', $siteLangId);?>" data-org-url="<?php echo CommonHelper::generateUrl('Shops', 'view', array($shop_id), '', null, false, $getOrgUrl); ?>" target="_blank" href="<?php echo CommonHelper::generateUrl('Shops', 'view', array($shop_id)); ?>"><i class="icn icn--home">
                <svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#manage-shop" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#manage-shop"></use></svg></i></a></li>
                <?php } ?>
            </ul>
            <div class="c-header-icon bell">
                <a data-org-url="<?php echo CommonHelper::generateUrl('Account', 'Messages', array(), '', null, false, $getOrgUrl); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'Messages'); ?>" title="<?php echo Labels::getLabel('LBL_Messages', $siteLangId);?>">
                <i class="icn"><svg class="svg bell-shake-delay">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#notification" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#notification"></use>
                    </svg>
                </i>
                <span class="h-badge"><span class="heartbit"></span><?php echo CommonHelper::displayBadgeCount($todayUnreadMessageCount, 9); ?></span></a>
            </div>
            <div class="short-links">
                <ul>
                    <?php /*$this->includeTemplate('_partial/headerLanguageArea.php');*/ ?>
                    <?php $this->includeTemplate('_partial/headerUserArea.php', array('isUserDashboard' => $isUserDashboard)); ?>
                </ul>
            </div>
        </div>
    </header>
    <div class="display-in-print text-center">
        <img src="<?php echo CommonHelper::generateFullUrl('Image', 'invoiceLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId, FatUtility::VAR_STRING, '') ?>"
            title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId, FatUtility::VAR_STRING, '') ?>">
    </div>
