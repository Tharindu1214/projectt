<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form post-messages');
$frm->developerTags['fld_default_col'] = 2;
?>
<div class="box">
<?php
echo $frm->getFormHtml();
?>
<div style="clear: both;"></div>
</div>
