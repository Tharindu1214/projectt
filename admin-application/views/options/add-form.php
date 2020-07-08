<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOptions->setFormTagAttribute('class', 'web_form');
$frmOptions->setFormTagAttribute('onsubmit', 'submitOptionForm(this); return(false);');
$frmOptions->developerTags['colClassPrefix'] = 'col-md-';
$frmOptions->developerTags['fld_default_col']=6;
echo $frmOptions->getFormHtml();
?>
