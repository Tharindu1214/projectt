<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<!-- Mobile Specific Metas ================================================== --> 
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php	
	echo $this->writeMetaTags();
	echo $this->getJsCssIncludeHtml(false);
	
	
	echo $str = '<script type="text/javascript">
		var SITE_ROOT_URL = "' . CommonHelper::generateFullUrl('','',array(),CONF_WEBROOT_FRONT_URL) . '" ;
		var langLbl = ' . json_encode(
			$jsVariables 
		) . ';
		var CONF_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 0) . ';
		var layoutDirection ="'.CommonHelper::getLayoutDirection().'";
		var CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 3) . ';		
		if( CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES <= 0  ){
			CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = 3;
		}
		</script>' . "\r\n";;
	?>
</head>
<body class="">
<!--wrapper start here-->
<main id="wrapper">   
    <!--header start here-->
	<header id="header">
        <div class="headerwrap">
            <div class="one_third_grid"></div>
            <div class="one_third_grid">
            	<div class="text-center"><a href="<?php   echo CommonHelper::generateUrl('ThemeColor','activateThemeColor',array($theme)); ?>" class="themebtn btn-default btn-sm"><?php echo Labels::getLabel('LBL_Activate_Theme',$adminLangId);?></a></div>
            </div>
            <div class="one_third_grid"></div>
        </div>          
    </header>    
    <!--header end here-->
<div id="body">
	<!--main panel start here-->
	<div>
		<iframe id="theme_preview_iframe" src="<?php echo CommonHelper::generateFullUrl('','',array(),CONF_WEBROOT_FRONT_URL); ?>?theme-preview" data="<?php echo $theme?>"></iframe>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>
<style>
#theme_preview_iframe {
    width: 100%;
	height:1200px;
    margin: 0 auto;
}
</style>