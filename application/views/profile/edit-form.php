<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');


$frm->setFormTagAttribute('class', 'form post-messages');
$fld = $frm->getField('update');
$fld->developerTags['col'] = 12;
$fld->addWrapperAttribute('style', 'text-align: right;');
 echo $frm->getFormHtml();
