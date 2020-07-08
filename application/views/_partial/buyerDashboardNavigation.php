<?php
    $controller = strtolower($controller);
    $action = strtolower($action); ?>
<div class="sidebar no-print">
    <div class="logo-wrapper">
        <?php
        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $logoUrl = CommonHelper::generateUrl('home', 'index');
        } else {
            $logoUrl = CommonHelper::generateUrl();
        } ?>
        <div class="logo-dashboard"><a href="<?php echo $logoUrl; ?>"><img src="<?php echo CommonHelper::generateFullUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"></a></div>

        <?php
        $isOpened = '';
        if (isset($_COOKIE['openSidebar']) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
            $isOpened = 'is-opened';
        }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'orders' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'Orders'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-order" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-order"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'mydownloads')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Downloads", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'MyDownloads'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-downloads" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-downloads"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Downloads", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'ordercancellationrequests') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Order_Cancellation_Requests", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Cancellation_Requests", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'orderreturnrequests' || $action == 'vieworderreturnrequest')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'orderReturnRequests'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="divider"></li>
                <?php if (User::canViewBuyerTab()) { ?>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Offers_&_Rewards", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'offers') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_My_Offers", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'offers'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Offers", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'rewardpoints') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Reward_Points", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'rewardPoints'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reward-points" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reward-points"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Reward_Points", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                    <?php if (FatApp::getConfig('CONF_ENABLE_REFERRER_MODULE', FatUtility::VAR_INT, 1)) { ?>
                    <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'shareearn') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_Share_and_Earn", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Buyer', 'shareEarn'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-share-earn" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-share-earn"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Share_and_Earn", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <?php } ?>
                <li class="divider"></li>
                <?php } ?>
                <li class="menu__item">
                    <div class="menu__item__inner"><span class="menu-head"><?php echo Labels::getLabel("LBL_General", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'messages') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'Messages'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>
                            <?php if ($todayUnreadMessageCount > 0) { ?>
                                <span class="msg-count"><?php echo ($todayUnreadMessageCount < 9) ? $todayUnreadMessageCount : '9+' ; ?></span>
                            <?php } ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_My_Credits", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'credits');?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'wishlist') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Wishlist/Favorites", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'wishlist');?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Wishlist/Favorites', $siteLangId);?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'savedproductssearch' && $action == 'listing') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Saved_Searches", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('SavedProductsSearch', 'listing');?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-saved-searches" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-saved-searches"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Saved_Searches', $siteLangId);?></span>
                        </a>
                    </div>
                </li>
                <li class="divider"></li>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Profile", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Account_Settings", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Account_Settings", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'myaddresses') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Manage_Addresses", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'myAddresses'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-address" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-address"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Manage_Addresses", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Change_Email", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Account', 'changeEmailPassword');?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-email" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-password"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Change_Email_/_Password', $siteLangId);?></span>
                        </a>
                    </div>
                </li>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</div>
