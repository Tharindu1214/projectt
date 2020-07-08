<?php
define('CONF_FORM_ERROR_DISPLAY_TYPE', Form::FORM_ERROR_TYPE_AFTER_FIELD);
define('CONF_FORM_REQUIRED_STAR_WITH', Form::FORM_REQUIRED_STAR_WITH_CAPTION);
define('CONF_FORM_REQUIRED_STAR_POSITION', Form::FORM_REQUIRED_STAR_POSITION_AFTER);

FatApplication::getInstance()->setControllersForStaticFileServer(array('images','img','fonts','templates','innovas','assetmanager'));

$innova_settings  = array('width'=>'730', 'height'=>'400','arrStyle'=>'[["body",false,"","min-height:250px;"]]',  'groups'=>' [
    ["group1", "", ["Bold", "Italic", "Underline", "FontName", "ForeColor", "TextDialog", "RemoveFormat"]],
    ["group2", "", ["Bullets", "Numbering", "JustifyLeft", "JustifyCenter", "JustifyRight"]],
    ["group3", "", ["LinkDialog", "ImageDialog", "Table", "TableDialog"]],
    ["group5", "", ["Undo", "Redo", "FullScreen", "SourceDialog"]]]',
    'fileBrowser'=> '"'.CONF_WEBROOT_URL.'innova/assetmanager/asset.php"');

FatApp::setViewDataProvider('_partial/buyerDashboardNavigation.php', array('Navigation', 'buyerDashboardNavigation'));
FatApp::setViewDataProvider('_partial/buyerDashboardMobileNavigation.php', array('Navigation', 'buyerDashboardNavigation'));
FatApp::setViewDataProvider('_partial/advertiser/advertiserDashboardNavigation.php', array('Navigation', 'advertiserDashboardNavigation'));
FatApp::setViewDataProvider('_partial/advertiser/advertiserDashboardMobileNavigation.php', array('Navigation', 'advertiserDashboardNavigation'));
FatApp::setViewDataProvider('_partial/seller/sellerDashboardNavigation.php', array('Navigation', 'sellerDashboardNavigation'));
FatApp::setViewDataProvider('_partial/seller/sellerDashboardMobileNavigation.php', array('Navigation', 'sellerDashboardNavigation'));
FatApp::setViewDataProvider('_partial/affiliate/affiliateDashboardNavigation.php', array('Navigation', 'affiliateDashboardNavigation'));
FatApp::setViewDataProvider('_partial/topHeaderDashboard.php', array('Navigation', 'topHeaderDashboard'));

FatApp::setViewDataProvider('_partial/headerWishListAndCartSummary.php', array('Common', 'headerWishListAndCartSummary'));
FatApp::setViewDataProvider('_partial/headerUserArea.php', array('Common', 'headerUserArea'));
FatApp::setViewDataProvider('_partial/headerNavigation.php', array('Navigation', 'headerNavigation'));
FatApp::setViewDataProvider('_partial/headerSearchFormArea.php', array('Common', 'headerSearchFormArea'));
FatApp::setViewDataProvider('_partial/headerLanguageArea.php', array('Common', 'headerLanguageArea'));
FatApp::setViewDataProvider('_partial/dashboardLanguageArea.php', array('Common', 'headerLanguageArea'));

FatApp::setViewDataProvider('_partial/loginPageRight.php', array('Block','loginPageRight'));
FatApp::setViewDataProvider('_partial/customPageLeft.php', array('Navigation','customPageLeft'));
FatApp::setViewDataProvider('_partial/dashboardTop.php', array('Navigation','dashboardTop'));

FatApp::setViewDataProvider('_partial/custom/header-breadcrumb.php', array('Common', 'setHeaderBreadCrumb'));
FatApp::setViewDataProvider('_partial/footerNewsLetterForm.php', array('Common', 'footerNewsLetterForm'));
// FatApp::setViewDataProvider('_partial/userDashboardMessages.php', array('Common', 'userMessages'));
FatApp::setViewDataProvider('_partial/headerTopNavigation.php', array('Navigation', 'headerTopNavigation'));
FatApp::setViewDataProvider('_partial/footerNavigation.php', array('Navigation', 'footerNavigation'));
FatApp::setViewDataProvider('_partial/seller/sellerNavigationLeft.php', array('Navigation', 'sellerNavigationLeft'));
FatApp::setViewDataProvider('_partial/seller/sellerNavigationRight.php', array('Navigation', 'sellerNavigationRight'));
FatApp::setViewDataProvider('_partial/footerSocialMedia.php', array('Common', 'footerSocialMedia'));
// FatApp::setViewDataProvider('_partial/footerTopBrands.php', array('Common', 'footerTopBrands'));
// FatApp::setViewDataProvider('_partial/footerTopCategories.php', array('Common', 'footerTopCategories'));
FatApp::setViewDataProvider('_partial/footerTrustBanners.php', array('Common', 'footerTrustBanners'));
FatApp::setViewDataProvider('_partial/blogNavigation.php', array('Navigation', 'blogNavigation'));
//FatApp::setViewDataProvider('_partial/brandFilters.php', array('Common', 'brandFilters'));
FatApp::setViewDataProvider('_partial/seller/sellerSalesGraph.php', array('Statistics', 'sellerSalesGraph'));
// FatApp::setViewDataProvider('_partial/faq-list.php', array('Common', 'faqList'));

FatApp::setViewDataProvider('_partial/blogSidePanel.php', array('Common', 'blogSidePanelArea'));
FatApp::setViewDataProvider('_partial/blogTopFeaturedCategories.php', array('Common', 'blogTopFeaturedCategories'));
//FatApp::setViewDataProvider('_partial/poll-form.php', array('Common', 'pollForm'));
