<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php 

if( isset($includeEditor) && $includeEditor == true ){
	$extendEditorJs	= 'true';
}else{
	$extendEditorJs	= 'false';
	$includeEditor	= false;
}

$commonHeadData = array(
	'adminLangId'		=>	$adminLangId,
	'jsVariables'		=>	$jsVariables,
	'extendEditorJs'    =>  $extendEditorJs,
	'includeEditor'	    =>   $includeEditor,
	'layoutDirection'	    =>  CommonHelper::getLayoutDirection()
);
$this->includeTemplate( '_partial/header/common-head.php', $commonHeadData, false);
echo $this->writeMetaTags();
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);
$commonHeadHtmlData = array(
	'bodyClass'         =>   $bodyClass,
	'includeEditor'	    =>   $includeEditor
);

$this->includeTemplate( '_partial/header/common-header-html.php', $commonHeadHtmlData, false);
if($isAdminLogged){ 
	include( '_partial/header/logged-user-header.php');
}
