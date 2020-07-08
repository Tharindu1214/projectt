<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$additionalAttributes = (CommonHelper::getLayoutDirection() == 'rtl') ? 'direction="rtl" style="direction: rtl;"' : '';
?>
<!doctype html>
<html <?php echo $additionalAttributes;?> class="<?php if(CommonHelper::demoUrl()) { echo "sticky-demo-header";}?>">
<head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="author" content="">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">-->
	<?php
	if( isset($includeEditor) && $includeEditor == true ){
	$extendEditorJs	= 'true';
	}else{
		$extendEditorJs	= 'false';
	}
	echo $str = '<script type="text/javascript">
		var SITE_ROOT_URL = "' . CommonHelper::generateFullUrl('','',array(),CONF_WEBROOT_FRONT_URL) . '" ;
		var langLbl = ' . json_encode(
			$jsVariables
		) . ';
		var CONF_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 0) . ';
		var layoutDirection ="'.$layoutDirection.'";
		var CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 3) . ';
		var extendEditorJs = ' . $extendEditorJs . ';
		if( CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES <= 0  ){
			CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = 3;
		}
		</script>' . "\r\n";;


	 if( AttachedFile::getAttachment( AttachedFile::FILETYPE_FAVICON, 0, 0, $adminLangId ) ){ ?>
	<link rel="shortcut icon" href="<?php echo CommonHelper::generateUrl('image', 'favicon', array($adminLangId), CONF_WEBROOT_FRONT_URL) ?>">
	<?php } ?>

	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,500i,700,700i,900,900i" rel="stylesheet">
