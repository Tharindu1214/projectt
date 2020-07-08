<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
//$_SESSION['geo_location'] = true;
if ($controllerName != 'GuestUser' && $controllerName != 'Error') {
    $_SESSION['referer_page_url'] = CommonHelper::getCurrUrl();
}
$htmlClass = '';
$actionName = FatApp::getAction();
if ($controllerName == 'Products' && $actionName == 'view') {
    $htmlClass = 'product-view';
}
$additionalAttributes = (CommonHelper::getLayoutDirection() == 'rtl') ? 'direction="rtl" style="direction: rtl;"' : '';
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" <?php echo $additionalAttributes;?> class="<?php echo $htmlClass;?> <?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { echo "sticky-demo-header";} ?>">

<head>
    <!-- Yo!Kart -->
    <meta charset="utf-8">
    <meta name="author" content="">
    <!-- Mobile Specific Metas ===================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- favicon ================================================== -->

    <!--<link rel="shortcut icon" href="">-->
    <link rel="shortcut icon"
        href="<?php echo CommonHelper::generateUrl('Image', 'favicon', array($siteLangId)); ?>">
    <link rel="apple-touch-icon"
        href="<?php echo CommonHelper::generateUrl('Image', 'appleTouchIcon', array($siteLangId)); ?>">
    <link rel="apple-touch-icon" sizes="72x72"
        href="<?php echo CommonHelper::generateUrl('Image', 'appleTouchIcon', array($siteLangId,'MINI')); ?>">
    <link rel="apple-touch-icon" sizes="114x114"
        href="<?php echo CommonHelper::generateUrl('Image', 'appleTouchIcon', array($siteLangId,'SMALL')); ?>">

    <?php
    if ($canonicalUrl == '') {
        $canonicalUrl = CommonHelper::generateFullUrl($controllerName, FatApp::getAction(), !empty(FatApp::getParameters())?FatApp::getParameters():array());
    }
    ?>
    <link rel="canonical" href="<?php echo $canonicalUrl;?>" />
    <style>
        :root {
            --first-color: #<?php echo $themeDetail['tcolor_first_color']; ?>;
            --second-color: #<?php echo $themeDetail['tcolor_second_color']; ?>;
            --third-color: #<?php echo $themeDetail['tcolor_third_color']; ?>;
            --txt-color: #<?php echo $themeDetail['tcolor_text_color']; ?>;
            --txt-color-light: #<?php echo $themeDetail['tcolor_text_light_color']; ?>;
            --border-color: #<?php echo $themeDetail['tcolor_border_first_color']; ?>;
            --border-color-second: #<?php echo $themeDetail['tcolor_border_second_color'];?>;
            --second-btn-color: #<?php echo $themeDetail['tcolor_second_btn_color'];  ?>;
            --header-txt-color: #<?php echo $themeDetail['tcolor_header_text_color']; ?>;
            --body-color: #525252;
            --gray-light: #f8f8f8;
        }
    </style>
    <?php
    echo $str = '<script type="text/javascript">
        var langLbl = ' . FatUtility::convertToJson($jsVariables, JSON_UNESCAPED_UNICODE) . ';
        var CONF_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 0) . ';
        var CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 3) . ';
        var extendEditorJs = ' . $extendEditorJs . ';
        var themeActive = ' . $themeActive . ';
        var currencySymbolLeft = "' . $currencySymbolLeft . '";
        var currencySymbolRight = "' . $currencySymbolRight . '";
        if( CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES <= 0  ){
            CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = 3;
        }
    </script>' . "\r\n";

    if (FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '')) {
        echo FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '');
    }

    if (FatApp::getConfig("CONF_ENABLE_ENGAGESPOT_PUSH_NOTIFICATION", FatUtility::VAR_STRING, '')) {
        echo FatApp::getConfig("CONF_ENGAGESPOT_PUSH_NOTIFICATION_CODE", FatUtility::VAR_STRING, '');
        if (UserAuthentication::getLoggedUserId(true) > 0) { ?>
    <script type="text/javascript">
        Engagespot.init()
        Engagespot.identifyUser('YT_<?php echo UserAuthentication::getLoggedUserId(); ?>');
    </script>
    <?php
        }
    }
