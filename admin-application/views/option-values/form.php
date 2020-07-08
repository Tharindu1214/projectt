<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$optionValueFrm->setFormTagAttribute('class', 'web_form form_horizontal');

$optionValueFrm->setFormTagAttribute('onsubmit', 'setUpOptionValues(this); return(false);');
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['fld_default_col']=12;

echo '<h3>'.Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES',$adminLangId).'<h3>';
echo $optionValueFrm->getFormHtml();
?>