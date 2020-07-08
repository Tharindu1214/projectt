<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'pwdFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-xl-12 col-lg-12 col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('autocomplete', 'off');
$frm->setFormTagAttribute('onsubmit', 'updatePassword(this); return(false);');
echo $frm->getFormHtml();
