<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'custom-form setupSaveProductSearch-Js' );
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setupSaveProductSearch(this,event); return(false);');
$search_title_fld = $frm->getField('pssearch_name');
$search_title_fld->addFieldTagAttribute('placeholder',Labels::getLabel('LBL_Search_Title', $siteLangId));
?>

<div class="pop-up-title"><?php echo Labels::getLabel('LBL_Save_Search', $siteLangId); ?></div>
<div class="collection__form form">
  <?php
		echo $frm->getFormTag();
		echo $frm->getFieldHtml('pssearch_name');
		echo $frm->getFieldHtml('btn_submit');
	?>
  </form>
  <?php echo $frm->getExternalJs(); ?>
</div>
