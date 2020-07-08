<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

if (isset($includeEditor) && $includeEditor == true) {
    $extendEditorJs = 'true';
} else {
    $extendEditorJs = 'false';
}

if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
    $themeActive = 'true';
} else {
    $themeActive = 'false';
}

array_walk($jsVariables, function (&$item1, $key) {
    $item1 = html_entity_decode($item1, ENT_QUOTES, 'UTF-8');
});
$commonHead1Data = array(
    'siteLangId' => $siteLangId,
    'controllerName' => $controllerName,
    'jsVariables' => $jsVariables,
    'extendEditorJs' => $extendEditorJs,
    'themeDetail' => $themeDetail,
    'themeActive' => $themeActive,
    'currencySymbolLeft' => $currencySymbolLeft,
    'currencySymbolRight' => $currencySymbolRight,
    'canonicalUrl' => isset($canonicalUrl)?$canonicalUrl:'',
);

$this->includeTemplate('_partial/header/commonHead1.php', $commonHead1Data, false);
/* This is not included in common head, because, commonhead file not able to access the $this->Controller and $this->action[ */
echo $this->writeMetaTags();
/* ] */

/* This is not included in common head, because, if we are adding any css/js from any controller then that file is not included[ */
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);
/* ] */

$commonHead2Data = array(
    'siteLangId' => $siteLangId,
    'controllerName' => $controllerName,
    'action' => $action,
    'isUserDashboard' => $isUserDashboard,
);
if (isset($layoutTemplate) && $layoutTemplate != '') {
    $commonHead2Data['layoutTemplate'] = $layoutTemplate;
    $commonHead2Data['layoutRecordId'] = $layoutRecordId;
}
if (isset($socialShareContent) && $socialShareContent != '') {
    $commonHead2Data['socialShareContent'] = $socialShareContent;
}
if (isset($includeEditor) && $includeEditor == true) {
    $commonHead2Data['includeEditor'] = $includeEditor;
}
$this->includeTemplate('_partial/header/commonHead2.php', $commonHead2Data, false);

if (isset($isUserDashboard) && $isUserDashboard) {
    $this->includeTemplate('_partial/topHeaderDashboard.php', $commonHead2Data, false);
    $exculdeMainHeaderDiv = true;
}

if (!isset($exculdeMainHeaderDiv)) {
    $this->includeTemplate('_partial/topHeader.php', array('siteLangId'=>$siteLangId), false);
}

if (!$isAppUser) {
    $controllerName = strtolower($controllerName);
    switch ($controllerName) {
        case 'checkout':
        case 'walletpay':
        case 'subscriptioncheckout':
            $this->includeTemplate('_partial/header/checkout-header.php', array('siteLangId'=>$siteLangId,'headerData'=>$headerData,'controllerName'=>$controllerName), false);
            break;
    }
}
