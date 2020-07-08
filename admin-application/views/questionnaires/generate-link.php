<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Copy_Link',$adminLangId); ?> (<?php echo $questionnaireData['questionnaire_name']; ?>)</h1>
	<div class="tabs_nav_container responsive flat">
		
		<div class="tabs_panel_wrap selectme" id="selectme" onclick="selectGeneratedLink(event)">
			<?php echo $link; ?>
		</div>
	</div>
</div>