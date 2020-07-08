<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'changeEmailFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-xl-12 col-lg-12 col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('autocomplete', 'off');
$frm->setFormTagAttribute('onsubmit', 'updateEmail(this); return(false);');

$fldSubmit = $frm->getField('btn_submit');
$fldSubmit->htmlAfterField ='<br/><small>'.Labels::getLabel('MSG_Your_email_not_change_untill_you_confirm', $siteLangId).'</small>';

echo $frm->getFormHtml();
