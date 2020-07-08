<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'settingsInfoFrm');
$frm->setFormTagAttribute('class','form');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setSettingsInfo(this); return(false);');
?>
<div class="row">
	<div class="col-md-8">
		<?php echo $frm->getFormHtml();?>
	</div>
</div>