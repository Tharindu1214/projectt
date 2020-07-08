  <!--left panel start here-->
        <span class="leftoverlay"></span>
        <aside class="leftside">

            <div class="sidebar_inner">
                 <div class="profilewrap">
                        <div class="profilecover">
                            <figure class="profilepic"><img id="leftmenuimgtag"  src="<?php echo CommonHelper::generateUrl('image', 'profileImage', array(AdminAuthentication::getLoggedAdminId(), "THUMB", true )); ?>" alt=""></figure>
                            <span class="profileinfo"><?php echo Labels::getLabel('LBL_Welcome', $adminLangId);?> <?php echo $adminName; ?></span>
                        </div>

                        <div class="profilelinkswrap">
                            <ul class="leftlinks">
                                <li class=""><a href="<?php echo CommonHelper::generateUrl('profile');?>"><?php echo Labels::getLabel('LBL_View_Profile', $adminLangId);?></a></li>
                                <li class=""><a href="<?php echo CommonHelper::generateUrl('profile', 'changePassword');?>"><?php echo Labels::getLabel('LBL_Change_Password', $adminLangId);?></a></li>
                                <li class=""><a href="<?php echo CommonHelper::generateUrl('profile', 'logout');?>"><?php echo Labels::getLabel('LBL_Logout', $adminLangId);?></a></li>
                             </ul>
                        </div>
                    </div>
            <ul class="leftmenu">
            <!--Dashboard-->
            <?php if (
                $objPrivilege->canViewAdminDashboard(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li><a href="<?php echo CommonHelper::generateUrl();?>"><?php echo Labels::getLabel('LBL_Dashboard', $adminLangId);?></a></li>
            <?php }?>
            <!--Products -->
            <?php if (
                $objPrivilege->canViewShops(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAttributes(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true)||
                $objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Catalog', $adminLangId);?></a>
                <ul>
                    <?php
                    if ($objPrivilege->canViewShops(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Shops');?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId);?></a></li>
                    <?php }?>
                    <?php
                    if ($objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ProductCategories');?>"><?php echo Labels::getLabel('LBL_Product_Categories', $adminLangId);?></a></li>
                    <?php }?>
                    <?php
                    if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('products');?>"><?php echo Labels::getLabel('LBL_Products', $adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('sellerProducts', 'index');?>"><?php echo Labels::getLabel('LBL_Seller_Inventory', $adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('sellerProducts', 'specialPrice');?>"><?php echo Labels::getLabel('LBL_Special_Price', $adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('sellerProducts', 'volumeDiscount');?>"><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId);?></a></li>
                    <?php }/* if($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('sellerProducts','catalog');?>"><?php echo Labels::getLabel('LBL_Add_New_Product',$adminLangId);?></a></li>
                    <?php } */ ?>
                    <?php
                    if ($objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('productReviews');?>"><?php echo Labels::getLabel('LBL_Product_Reviews', $adminLangId);?></a></li>
                    <?php }?>
                    <?php
                    if ($objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Brands');?>"><?php echo Labels::getLabel('LBL_Brands', $adminLangId);?></a></li>
                    <?php }?>
                    <?php
                    /* if($objPrivilege->canViewFilterGroups(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('filterGroups');?>">Filters</a></li>
                    <?php }  */?>
                    <?php
                    /* if($objPrivilege->canViewAttributes(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Attributes');?>"><?php echo Labels::getLabel('LBL_Attributes',$adminLangId);?></a></li>
                    <?php } */ ?>
                    <?php
                    /* if($objPrivilege->canViewExtraAttributes(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('extraAttributeGroups');?>">Extra Attributes</a></li>
                    <?php } */ ?>
                    <?php
                    if ($objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Options');?>"><?php echo Labels::getLabel('LBL_Options', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php
                    if ($objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Tags');?>"><?php echo Labels::getLabel('LBL_Tags', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php
                    if ($objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Brands', 'BrandRequests');?>"><?php echo Labels::getLabel('LBL_Brand_Requests', $adminLangId);?><?php if ($brandReqCount) { ?><span class='badge'>(<?php echo $brandReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php /* if($objPrivilege->canViewSellerCatalogRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_SELLER_CAN_REQUEST_PRODUCT', FatUtility::VAR_INT, 0)){?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Users','sellerCatalogRequests');?>"><?php echo Labels::getLabel('LBL_Product_Catalog_Requests',$adminLangId);?> <?php if($catReqCount){ ?><span class='badge'>(<?php echo $catReqCount; ?>)</span><?php } ?></a></li>
                    <?php } */ ?>
                    <?php if ($objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('CustomProducts');?>"><?php echo Labels::getLabel('LBL_Custom_Product_Catalog_Requests', $adminLangId);?> <?php if ($custProdReqCount) { ?><span class='badge'>(<?php echo $custProdReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                </ul>
            </li>
            <?php }?>
            <?php if (
                $objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerCatalogRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Users', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Users');?>"><?php echo Labels::getLabel('LBL_Users', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Users', 'sellerForm');?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Form', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Users', 'sellerApprovalRequests');?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Requests', $adminLangId);?> <?php if ($supReqCount) { ?><span class='badge'>(<?php echo $supReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('userGdprRequests');?>"><?php echo Labels::getLabel('LBL_Users_GDPR_Requests', $adminLangId);?> <?php if ($gdprReqCount) { ?><span class='badge'>(<?php echo $gdprReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                </ul>
            </li>
            <?php }?>
            <!--FAQ-->
            <!--<li><a href="<?php echo CommonHelper::generateUrl('faqs');?>">Manage FAQ's</a></li>-->
            <!--CMS-->
            <?php if (
                $objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCities(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPolicyPoints(AdminAuthentication::getLoggedAdminId(), true)  ||
                $objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Cms', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ContentPages'); ?>"><?php echo Labels::getLabel('LBL_Content_Pages', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ContentBlock'); ?>"><?php echo Labels::getLabel('LBL_Content_Blocks', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ContentBlock', 'importInstructions'); ?>"><?php echo Labels::getLabel('LBL_Import_Instructions', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Navigations'); ?>"><?php echo Labels::getLabel('LBL_Navigation_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Countries'); ?>"><?php echo Labels::getLabel('LBL_Countries_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('States'); ?>"><?php echo Labels::getLabel('LBL_States_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewCities(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Cities'); ?>"><?php echo Labels::getLabel('LBL_Cities_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Collections'); ?>"><?php echo Labels::getLabel('LBL_Collection_Management', $adminLangId);?> </a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewPolicyPoints(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('PolicyPoints'); ?>"><?php echo Labels::getLabel('LBL_Policy_Points_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('emptyCartItems'); ?>"><?php echo Labels::getLabel('LBL_Empty_Cart_Items_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SocialPlatform'); ?>"><?php echo Labels::getLabel('LBL_Social_Platforms_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('ShopReportReasons'); ?>"><?php echo Labels::getLabel('LBL_Shop_Report_Reasons_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('OrderCancelReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Cancel_Reasons_Management', $adminLangId);?> </a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('OrderReturnReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Return_Reasons_Management', $adminLangId);?> </a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Testimonials'); ?>"><?php echo Labels::getLabel('LBL_Testimonials_Management', $adminLangId);?> </a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('DiscountCoupons'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId);?></a></li>
                    <?php }?>

                    <!-- <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SellerDiscountCoupons'); ?>"><?php echo Labels::getLabel('LBL_Seller_Discount_Coupons', $adminLangId);?></a></li>
                    <?php }?> -->

                    <?php if ($objPrivilege->canViewLanguageLabels(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Labels'); ?>"><?php echo Labels::getLabel('LBL_Language_Labels', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewSlides(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('slides'); ?>"><?php echo Labels::getLabel('LBL_Home_Page_Slides_Management', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewBanners(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Banners'); ?>"><?php echo Labels::getLabel('LBL_Banners', $adminLangId);?></a></li>
                    <?php }?>

                </ul>
            </li>
            <?php }?>
            <?php if (
                $objPrivilege->canViewShippingMethods(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewShippingCompanyUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewShippingDurationLabels(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewManualShippingApi(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Shipping_Api', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewShippingMethods(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ShippingMethods'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Methods', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewShippingDurationLabels(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ShippingDurations'); ?>"><?php echo Labels::getLabel('LBL_Duration_Labels', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewShippingCompanies(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ShippingCompanies'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Companies', $adminLangId);?></a></li>
                    <?php }?>

                    <?php if ($objPrivilege->canViewShippingCompanyUsers(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ShippingCompanyUsers');?>"><?php echo Labels::getLabel('LBL_Shipping_Company_Users', $adminLangId);?></a></li>
                    <?php }?>

                    <?php /* if($objPrivilege->canViewManualShippingApi(AdminAuthentication::getLoggedAdminId(), true)){?>
                        <li><a href="<?php echo CommonHelper::generateUrl('ManualShippingApi'); ?>"><?php echo Labels::getLabel('LBL_Manual_Shipping_Api',$adminLangId);?></a></li>
                    <?php } */ ?>
                </ul>
            </li>
            <?php }?>
            <!--Settings-->
            <?php if (
                $objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPaymentMethods(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTax(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAffiliateCommissionSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true)  ||
                $objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true)  ||
                $objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Settings', $adminLangId);?></a>
                <ul>

                    <?php if ($objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('configurations'); ?>"><?php echo Labels::getLabel('LBL_General_Settings', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewPaymentMethods(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('PaymentMethods'); ?>"><?php echo Labels::getLabel('LBL_Payment_Methods', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('CurrencyManagement'); ?>"><?php echo Labels::getLabel('LBL_Currency_Management', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewTax(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Tax'); ?>"><?php echo Labels::getLabel('LBL_Sales_Tax', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('Commission'); ?>"><?php echo Labels::getLabel('LBL_Commission_Settings', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAffiliateCommissionSettings(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('AffiliateCommission'); ?>"><?php echo Labels::getLabel('LBL_Affiliate_Commission_Settings', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('EmailTemplates'); ?>"><?php echo Labels::getLabel('LBL_Email_Templates_Management', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('OrderStatus'); ?>"><?php echo Labels::getLabel('LBL_Order_Status_Management', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SellerPackages'); ?>"><?php echo Labels::getLabel('LBL_Seller_Packages_Management', $adminLangId);?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('RewardsOnPurchase'); ?>"><?php echo Labels::getLabel('LBL_Rewards_on_every_purchase', $adminLangId);?></a></li>
                    <?php }?>
                </ul>
            </li>    <?php }?>
            <!--Orders-->
            <?php if (
                $objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Orders', $adminLangId);?></a>
                <ul>

                    <?php if ($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('orders'); ?>"><?php echo Labels::getLabel('LBL_Orders', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SellerOrders'); ?>"><?php echo Labels::getLabel('LBL_Seller_Orders', $adminLangId);?> <?php if (!empty($sellerOrderCount)) { ?><span class='badge'>(<?php echo $sellerOrderCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SubscriptionOrders'); ?>"><?php echo Labels::getLabel('LBL_Subscription_Orders', $adminLangId);?> </a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('WithdrawalRequests'); ?>"><?php echo Labels::getLabel('LBL_Withdrawl_Requests', $adminLangId);?> <?php if ($drReqCount) { ?><span class='badge'>(<?php echo $drReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('OrderCancellationRequests'); ?>"><?php echo Labels::getLabel('LBL_Cancellation_Requests', $adminLangId);?> <?php if ($orderCancelReqCount) { ?><span class='badge'>(<?php echo $orderCancelReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo CommonHelper::generateUrl('OrderReturnRequests'); ?>"><?php echo Labels::getLabel('LBL_Return/Refund_Requests', $adminLangId);?> <?php if ($orderRetReqCount) { ?><span class='badge'>(<?php echo $orderRetReqCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                </ul>
            </li> <?php }?>
            <?php if (
                $objPrivilege->canViewRecomendedWeightages(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Smart_Recommendations', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewRecomendedWeightages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('SmartRecomendedWeightages'); ?>"><?php echo Labels::getLabel('LBL_Manage_Weightages', $adminLangId);?></a></li>
                        <li><a href="<?php echo CommonHelper::generateUrl('RecomendedTagProducts'); ?>"><?php echo Labels::getLabel('LBL_Recommended_Tag_Products_Weightages', $adminLangId);?></a></li>
                        <?php /*?><li><a href="<?php echo CommonHelper::generateUrl('SmartRecomendedProducts'); ?>">Tag Product Weightages</a></li>
                        <li><a href="<?php echo CommonHelper::generateUrl('ProductBrowsingHistory'); ?>">Products Browsing History</a></li>    <?php */?>
                    <?php } ?>
                </ul>
            </li>
            <?php }?>

            <?php if ($objPrivilege->canViewPromotions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li><a href="<?php echo CommonHelper::generateUrl('promotions'); ?>"><?php echo Labels::getLabel('LBL_PPC_Promotions_Management', $adminLangId);?></a></li>
            <?php }?>

            <?php if (
                $objPrivilege->canViewSuccessStories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewLanguageLabels(AdminAuthentication::getLoggedAdminId(), true) ||
                /* $objPrivilege->canViewHomePageElements(AdminAuthentication::getLoggedAdminId(), true) ||  */
                $objPrivilege->canViewSlides(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBanners(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProductTempImages(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Misc', $adminLangId);?></a>
                <ul>
                    <?php /* if($objPrivilege->canViewSuccessStories(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('SuccessStories'); ?>"><?php echo Labels::getLabel('LBL_Success_Stories',$adminLangId);?></a></li>
                    <?php } */ ?>

                    <?php /* if($objPrivilege->canViewHomePageElements(AdminAuthentication::getLoggedAdminId(), true)){?>
                    <li><a href="<?php echo CommonHelper::generateUrl('HomePageElements'); ?>">Home Page Elements</a></li>
                    <?php } */?>

                    <?php if ($objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('MetaTags'); ?>"><?php echo Labels::getLabel('LBL_Meta_Tags_Management', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('FaqCategories'); ?>"><?php echo Labels::getLabel('LBL_FAQs', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('UrlRewriting'); ?>"><?php echo Labels::getLabel('LBL_Url_Rewriting', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('ThemeColor'); ?>"><?php echo Labels::getLabel('LBL_Theme_Settings', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProductTempImages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('ProductTempImages'); ?>"><?php echo Labels::getLabel('LBL_Product_Temp_Images', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canUploadBulkImages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('UploadBulkImages'); ?>"><?php echo Labels::getLabel('LBL_Upload_Bulk_Images', $adminLangId);?></a></li>
                    <?php } ?>
                </ul>
            </li>
            <?php }
            if ($objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Blog', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('BlogPostCategories'); ?>"><?php echo Labels::getLabel('LBL_Blog_Post_Categories', $adminLangId);?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('BlogPosts'); ?>"><?php echo Labels::getLabel('LBL_Blog_Posts', $adminLangId);?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('BlogContributions'); ?>"><?php echo Labels::getLabel('LBL_Blog_Contributions', $adminLangId);?> <?php if ($blogContrCount) { ?><span class='badge'>(<?php echo $blogContrCount; ?>)</span><?php } ?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo CommonHelper::generateUrl('BlogComments'); ?>"><?php echo Labels::getLabel('LBL_Blog_Comments', $adminLangId);?> <?php if ($blogCommentsCount) { ?><span class='badge'>(<?php echo $blogCommentsCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                </ul>
            </li>
            <?php } ?>

            <?php if ($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewAffiliatesReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Reports', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('SalesReport'); ?>"><?php echo Labels::getLabel('LBL_Sales', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('UsersReport'); ?>"><?php echo Labels::getLabel('LBL_Buyers/Sellers', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAffiliatesReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('AffiliatesReport'); ?>"><?php echo Labels::getLabel('LBL_Affiliates', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('AdvertisersReport'); ?>"><?php echo Labels::getLabel('LBL_Advertisers', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Products(Seller_Products)', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('CatalogReport'); ?>"><?php echo Labels::getLabel('LBL_Products(Catalog_Wise)', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('ShopsReport'); ?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('TaxReport'); ?>"><?php echo Labels::getLabel('LBL_Tax', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('CommissionReport'); ?>"><?php echo Labels::getLabel('LBL_Commission', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('TopProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Top_Products', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('BadProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Most_Refunded_Products', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('TopCategoriesReport'); ?>"><?php echo Labels::getLabel('LBL_Top_Categories', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('BadCategoriesReport'); ?>"><?php echo Labels::getLabel('LBL_Bad_Categories', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo CommonHelper::generateUrl('DiscountCouponsReport'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId);?></a></li>
                    <?php }    ?>
                </ul>
            </li>
            <?php } ?>

            <!--Admin Users-->
            <?php if ($objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true) || $objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li><a href="<?php echo CommonHelper::generateUrl('AdminUsers')?>"><?php echo Labels::getLabel('LBL_Manage_Admin_Users', $adminLangId);?></a>
            </li>
            <?php } ?>
            <!-- <?php if ($objPrivilege->canViewQuestionBanks(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Questionnaires', $adminLangId);?></a>
                <ul>
                    <li><a href="<?php echo CommonHelper::generateUrl('QuestionBanks'); ?>"><?php echo Labels::getLabel('LBL_Question_Banks', $adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('Questionnaires'); ?>"><?php echo Labels::getLabel('LBL_Questionnaires', $adminLangId);?></a></li>
                </ul>
            </li>
            <?php } ?>
            <?php if ($objPrivilege->canViewPolling(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li><a href="<?php echo CommonHelper::generateUrl('Polling'); ?>"><?php echo Labels::getLabel('LBL_Polling', $adminLangId);?></a></li>
            <?php } ?> -->
            <?php if ($objPrivilege->canViewTools(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Tools', $adminLangId);?></a>
                <ul>
                    <?php /* <li><a href="<?php echo CommonHelper::generateUrl('sentEmails'); ?>"><?php echo Labels::getLabel('LBL_Sent_Emails',$adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('','',array(),CONF_WEBROOT_FRONT_URL).'restore.php?passkey=yokart-restore'; ?>"><?php echo Labels::getLabel('LBL_Restore_Default',$adminLangId);?> </a></li> */ ?>
                    <?php if ($objPrivilege->canViewSellerProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('SellerProducts', 'thresholdProducts'); ?>"><?php echo Labels::getLabel('LBL_Threshold_Products', $adminLangId);?> <?php if ($threshSelProdCount) { ?><span class='badge'>(<?php echo $threshSelProdCount; ?>)</span><?php } ?></a></li>
                    <?php }?>
                    <?php if ($objPrivilege->canViewAbusiveWords(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('AbusiveWords'); ?>"><?php echo Labels::getLabel('LBL_Abusive_Keyword', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewMessages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo CommonHelper::generateUrl('Messages'); ?>"><?php echo Labels::getLabel('LBL_Messages', $adminLangId);?></a></li>
                    <?php }?>
                </ul>
            </li>
            <?php }?>

            <?php if ($objPrivilege->canViewSitemap(AdminAuthentication::getLoggedAdminId(), true)) { ?>
				<li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sitemap',$adminLangId);?></a>
					<ul>
						<li><a href="<?php echo CommonHelper::generateUrl('sitemap', 'generate'); ?>"><?php echo Labels::getLabel('LBL_Update_Sitemap',$adminLangId);?></a></li>
						<li><a href="<?php echo CommonHelper::generateFullUrl('custom', 'sitemap', array(), CONF_WEBROOT_FRONT_URL); ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_HTML',$adminLangId);?></a></li>
						<li><a href="<?php echo CommonHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL).'/sitemap.xml'; ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_XML',$adminLangId);?></a></li>
					</ul>
				</li>
			<?php }?>

            <?php /*<li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Support_Link', $adminLangId);?></a>
                <ul>
                    <li><a target="_blank" href="http://www.yo-kart.com/recent-updates.html"><?php echo Labels::getLabel('LBL_Version_Update', $adminLangId);?></a></li>
                    <li><a target="_blank" href="http://faq.yo-kart.com/"><?php echo Labels::getLabel('LBL_FAQs', $adminLangId);?></a></li>
                    <li><a target="_blank" href="http://www.yo-kart.com/addons-integrations.html"><?php echo Labels::getLabel('LBL_Add_Ons', $adminLangId);?></a></li>
                    <li><a href="<?php echo CommonHelper::generateUrl('Support'); ?>"><?php echo Labels::getLabel('LBL_Report_An_Issue', $adminLangId);?></a></li>
                </ul>
            </li>*/ ?>
        </ul>
            </div>
        </aside>
        <!--left panel end here-->
