<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOptions->setFormTagAttribute('class', 'form form_horizontal');
$frmOptions->developerTags['colClassPrefix'] = 'col-md-';
$frmOptions->developerTags['fld_default_col'] = 6;
$frmOptions->setFormTagAttribute('onsubmit', 'submitOptionForm(this); return(false);');
echo $frmOptions->getFormHtml();
?>
<script type="text/javascript">
$(document).ready(function(){
	fcom.resetFaceboxHeight();
});
</script>
