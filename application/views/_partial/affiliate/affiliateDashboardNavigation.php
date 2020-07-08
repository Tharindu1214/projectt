<?php
$controller = strtolower($controller);
$action = strtolower($action);
?> <div class="sidebar no-print">
    <div class="logo-wrapper"> <?php
        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $logoUrl = CommonHelper::generateUrl('home', 'index');
        } else {
            $logoUrl = CommonHelper::generateUrl();
        }
        ?> <div class="logo-dashboard"><a href="<?php echo $logoUrl; ?>"><img src="<?php echo CommonHelper::generateFullUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>"
                    alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"></a></div>
        <?php 
            $isOpened = '';
            if (!empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])){
                $isOpened = 'is-opened';
            }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <?php if (User::canViewAffiliateTab()) { ?>
            <li class="menu__item <?php echo ($controller == 'affiliate' && $action == 'referredbyme') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Sharing", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Affiliate', 'ReferredByMe'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reffered" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reffered"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Referral", $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li> <?php if (!User::canViewAffiliateTab()) {
            ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'messages') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'Messages'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svgdash-messages"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Credits", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'credits');?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?></span></a></div>
                </li> <?php if (!User::canViewAffiliateTab()) {
            ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'wishlist') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Wishlist/Favorites", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'wishlist'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Wishlist/Favorites', $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Change_Email", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'changeEmailPassword');?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-email" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-password"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Change_Email_/_Password', $siteLangId);?></span></a></div>
                </li>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</div>
