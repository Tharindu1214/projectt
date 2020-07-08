<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( isset($includeEditor) && $includeEditor == true ){
	$extendEditorJs	= 'true';
}else{
	$extendEditorJs	= 'false';
}
if( CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'] ) ){
	$themeActive = 'true';
}else{
	$themeActive = 'false';
}
$commonHead1Data = array(
	'siteLangId'		=>	$siteLangId,
	'controllerName'	=>	$controllerName,
	'jsVariables'		=>	$jsVariables,
	'extendEditorJs'	=>	$extendEditorJs,
	'themeDetail'	    =>	$themeDetail,
	'themeActive'         =>    $themeActive,
	'currencySymbolLeft'  =>    $currencySymbolLeft,
	'currencySymbolRight' =>    $currencySymbolRight,
	'canonicalUrl' =>    isset($canonicalUrl)?$canonicalUrl:'',
	);
$this->includeTemplate( '_partial/header/commonHead1.php', $commonHead1Data,false);
/* This is not included in common head, because, commonhead file not able to access the $this->Controller and $this->action[ */
echo $this->writeMetaTags();
/* ] */
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);

$commonHead2Data = array(
	'siteLangId'		=>	$siteLangId,
	'controllerName'	=>	$controllerName,
);

if( isset($layoutTemplate) && $layoutTemplate != '' ){
	$commonHead2Data['layoutTemplate']	= $layoutTemplate;
	$commonHead2Data['layoutRecordId']	= $layoutRecordId;
}
if( isset($socialShareContent) && $socialShareContent != '' ){
	$commonHead2Data['socialShareContent']	= $socialShareContent;
}
$this->includeTemplate('_partial/header/commonHead2.php', $commonHead2Data,false);
?>
<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
	$this->includeTemplate('restore-system/top-header.php');
    $this->includeTemplate('restore-system/page-content.php');
} ?>
<div class="wrapper">
  <div  id="header" class="header-supplier">
	<div class="top-bar">
	  <div class="container">
		<div class="row">
		  <div class="col-lg-4 col-xs-6 d-none d-xl-block d-lg-block hide--mobile">
			<div class="slogan"><?php echo Labels::getLabel('LBL_Multi-vendor_Ecommerce_Marketplace_Solution',$siteLangId); ?></div>
		  </div>
		  <div class="col-lg-8 col-xs-12">
			<div class="short-links">
			  <ul>
				<?php $this->includeTemplate('_partial/headerTopNavigation.php'); ?>
				<?php $this->includeTemplate('_partial/headerLanguageArea.php'); ?>
			  </ul>
			</div>
		  </div>
		</div>
	  </div>
	</div>
    <div class="top-head">
      <div class="container">
        <div class="row row align-items-center">
          <div class="col-3">
            
            <div class="logo header-login-logo zoomIn"> <a href="<?php echo CommonHelper::generateUrl(); ?>"><img src="<?php echo CommonHelper::generateFullUrl('Image','siteLogo',array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>"></a></div>
          </div>
          <div class="col-9 yk-login--wrapper">
			<div class="seller-login-trigger hide--desktop">
				<a class="seller_login_toggle" href="javascript:void(0)"></a>
				<?php if(!empty($seller_navigation_left)) { ?>
					<div class="seller_nav-trigger"> <a class="seller_nav_toggle" href="javascript:void(0)"><span></span></a> </div>
				<?php }?>
			</div>
            <?php $this->includeTemplate( '_partial/seller/sellerHeaderLoginForm.php',$loginData,false); ?>
          </div>
        </div>
        <div class="row"></div>
        <div class="row"></div>
      </div>
    </div>
    <div class="bottom-head">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="short-nav">
              <?php $this->includeTemplate( '_partial/seller/sellerNavigationLeft.php'); ?>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="short-nav align--right hide--mobile hide--tab">
              <?php $this->includeTemplate( '_partial/seller/sellerNavigationRight.php'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
