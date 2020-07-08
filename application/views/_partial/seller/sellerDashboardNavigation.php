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
            if (array_key_exists('openSidebar', $_COOKIE) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])){
                $isOpened = 'is-opened';
            }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Shop', $siteLangId);?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'shop') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'shop'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId);?></span></a></div>
                </li>
                <!-- <li class="menu__item"><div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_View_Shop', $siteLangId); ?>" target="_blank" href="<?php echo CommonHelper::generateUrl('Shops', 'view', array($shop_id)); ?>"><i class="icn shop"><svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-view-shop" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-view-shop"></use></svg>
                   </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_View_Shop', $siteLangId); ?></span></a></    div></li> -->
                <li
                    class="menu__item <?php echo ($controller == 'seller' && ($action == 'customcatalogproductform' || $action == 'customproductform'|| $action == 'catalog' || $action == 'products' || $action == 'customcatalogproducts')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('seller', 'catalog'); ?>"><i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-products" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-products"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products', $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'inventoryupdate')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Inventory_Update', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('seller', 'InventoryUpdate'); ?>"><i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-inventory-update" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-inventory-update"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Inventory_Update', $siteLangId); ?></span></a></div>
                </li> <?php  if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0)) {
                ?> <li class="menu__item <?php echo ($controller == 'importexport' && ($action == 'index')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('ImportExport', 'index'); ?>"><i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-import-export" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-import-export"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?></span></a></div>
                </li> <?php
            } ?>
            <li class="divider"></li>
            <li class="menu__item">
                <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Promotions', $siteLangId);?></span></div>
            </li>
            <li class="menu__item <?php echo ($controller == 'seller' && $action == 'specialprice') ? 'is-active' : ''; ?>">
                <div class="menu__item__inner">
                    <a title="<?php echo Labels::getLabel('LBL_Special_Price', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'specialPrice'); ?>">
                        <i class="icn shop"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers"></use>
                            </svg>
                        </i>
                        <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Special_Price', $siteLangId);?></span>
                    </a>
                </div>
            </li>
            <li class="menu__item <?php echo ($controller == 'seller' && $action == 'volumediscount') ? 'is-active' : ''; ?>">
                <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'volumeDiscount'); ?>">
                        <i class="icn shop"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers"></use>
                            </svg>
                        </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId);?></span></a></div>
            </li>
            <li class="divider"></li>
             <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Sales', $siteLangId);?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'sales') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Sales', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'Sales'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Sales', $siteLangId);?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'ordercancellationrequests') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Cancellation_Requests', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'orderCancellationRequests'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Cancellation_Requests", $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'orderreturnrequests' || $action == 'vieworderreturnrequest')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Return_Requests', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'orderReturnRequests'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Return_Requests", $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'shippingsettings')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Return_Requests', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'shippingSettings'); ?>">
                            <i class="icn shop">
                            <svg class="svg" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="18px" height="18px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve">
<g>
	<g>
		<path d="M226.764,375.35c-28.249,0-51.078,22.91-51.078,51.16c0,28.166,22.829,51.078,51.078,51.078s51.078-22.912,51.078-51.078
			C277.841,398.26,255.013,375.35,226.764,375.35z M226.764,452.049c-14.125,0-25.54-11.498-25.54-25.541
			c0-14.123,11.415-25.539,25.54-25.539c14.124,0,25.539,11.416,25.539,25.539C252.302,440.551,240.888,452.049,226.764,452.049z
			 M612,337.561v54.541c0,13.605-11.029,24.635-24.636,24.635h-26.36c-4.763-32.684-32.929-57.812-66.927-57.812
			c-33.914,0-62.082,25.129-66.845,57.812H293.625c-4.763-32.684-32.93-57.812-66.845-57.812c-33.915,0-62.082,25.129-66.844,57.812
			h-33.012c-13.606,0-24.635-11.029-24.635-24.635v-54.541H612L612,337.561z M494.143,375.35c-28.249,0-51.16,22.91-51.16,51.16
			c0,28.166,22.912,51.078,51.16,51.078c28.166,0,51.077-22.912,51.077-51.078C545.22,398.26,522.309,375.35,494.143,375.35z
			 M494.143,452.049c-14.125,0-25.539-11.498-25.539-25.541c0-14.123,11.414-25.539,25.539-25.539
			c14.042,0,25.539,11.416,25.539,25.539C519.682,440.551,508.185,452.049,494.143,452.049z M602.293,282.637l-96.817-95.751
			c-6.159-6.077-14.453-9.526-23.076-9.526h-48.86v-18.313c0-13.631-11.004-24.635-24.635-24.635H126.907
			c-13.55,0-24.635,11.005-24.635,24.635v3.86L2.3,174.429l177.146,23.068L0,215.323l178.814,25.423L0,256.25l102.278,19.29
			l-0.007,48.403h509.712v-17.985C611.983,297.171,608.452,288.796,602.293,282.637z M560.084,285.839h-93.697
			c-2.135,0-3.86-1.724-3.86-3.859v-72.347c0-2.135,1.725-3.86,3.86-3.86h17.82c0.985,0,1.971,0.411,2.71,1.068l75.796,72.347
			C565.257,281.569,563.532,285.839,560.084,285.839z"/>
	</g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>
                            </i><span class="menu-item__title">Shipping Settings</span></a></div>
                </li>
                <li class="divider"></li>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Settings', $siteLangId);?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'taxcategories') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Tax_Category', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'taxCategories'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-tax-category" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-tax-category"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Tax_Category', $siteLangId);?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'options') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Options', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'options'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-options" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-options"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Options', $siteLangId);?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'seller' && $action == 'socialplatforms') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Manage_Social_Platforms', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Seller', 'socialPlatforms'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-socialplatforms" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-socialplatforms"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Manage_Social_Platforms', $siteLangId);?></span></a></div>
                </li>
                <li class="divider"></li>
                <?php if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) { ?>
                    <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Subscription', $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'subscriptions' || $action == 'viewsubscriptionorder')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Subscriptions', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Seller', 'subscriptions'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Subscriptions", $siteLangId); ?></span></a></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'packages')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('seller', 'Packages'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-subscription-packages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-subscription-packages"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?></span></a></div>
                    </li>

                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'selleroffers')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('seller', 'SellerOffers'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?></span></a></div>
                    </li>

                    <li class="divider"></li>
                <?php } ?>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Profile", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Account', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'messages') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Messages', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Account', 'Messages'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?> <?php if ($todayUnreadMessageCount > 0) {
            ?><span
                                    class="msg-count"><?php echo ($todayUnreadMessageCount < 9) ? $todayUnreadMessageCount : '9+' ; ?></span> <?php
        } ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Account', 'credits');?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Change_Email', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Account', 'changeEmailPassword');?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-email" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-password"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Change_Email_/_Password', $siteLangId);?></span></a></div>
                </li>
                <li class="divider"></li>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Reports", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'reports' && $action == 'salesreport') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Reports', 'SalesReport'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales-report" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales-report"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsperformance') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Performance', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Reports', 'ProductsPerformance'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-performance" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-performance"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Performance', $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventory') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Reports', 'productsInventory'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventorystockstatus') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId);?>" href="<?php echo CommonHelper::generateUrl('Reports', 'productsInventoryStockStatus'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory-stock" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory-stock"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId); ?></span></a></div>
                </li>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</div>
