<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = '6';
$frm->getField('qbank')->addFieldTagAttribute('onchange','searchQuestionsToLink(this.form);return false;');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Link_Questions_to_Questionnaires',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<div class="tabs_panel_wrap" style="min-height: 500px;min-width: 900px;">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
				<div id="listQuestionsInQbank" class="col-md-12">
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$('select[name="qbank"]').change();
</script>