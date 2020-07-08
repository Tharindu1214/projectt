<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="box">
	<?php 
	/**
	 * @var Form $frm
	 */
	$frm->setFormTagAttribute('class', 'form post-messages');
	$frm->setFormTagAttribute('onsubmit', 'searchUsers(this, 1); return(false);');
	$frm->developerTags['fld_default_col'] = 2;
	echo $frm->getFormHtml();
	?>
	<div style="clear: both;"></div>
</div>
<div id="suggestion-list">

</div>
<hr>
<div id="user-list">

</div>
