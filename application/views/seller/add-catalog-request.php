<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class','form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
/* $frm->setFormTagAttribute('onsubmit', 'setupCatalogRequest(this); return(false);');
$frm->setFormTagAttribute('class','form form--horizontal'); */
/*   */
?>
<div class="box__head">
   <h4><?php echo Labels::getLabel('LBL_Catalog_request_form', $siteLangId );?></h4>
	<div class="btn-group">
		<a href="javascript:void(0);" onClick="reloadList()" class="btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_Back',$siteLangId); ?></a>
	</div>
</div>
<div class="box__body">
	<?php
		echo $frm->getFormTag();
		echo $frm->getFormHtml(false);
		echo '</form>';
	?>
</div>
<span class="gap"></span>
</div>
