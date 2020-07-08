<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php 	$variables= array( 'language'=>$language,'siteLangId'=>$siteLangId,'shop_id'=>$shop_id,'action'=>$action);
	$this->includeTemplate('seller/_partial/shop-navigation.php',$variables,false);
?>
<div class="tabs tabs-sm tabs--scroll clearfix">
	<ul>
		<li ><a onclick="shopTemplates(this);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Template', $siteLangId);?></a></li>

		<li  class="is-active" >
			<a href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_Theme_Color', $siteLangId);?> </a>
		</li>
	</ul>
</div>

<div class="tabs__content">
	<div class="form__subcontent">
		<?php
		$themeColorFrm->setFormTagAttribute('onsubmit','setUpThemeColor(this); return(false);');
		$themeColorFrm->setFormTagAttribute('class','form form--horizontal');
		$themeColorFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
		$themeColorFrm->developerTags['fld_default_col'] = 6;
		$submitFld= $themeColorFrm->getField('btn_submit');
		$submitFld->setFieldTagAttribute('class','block-on-mobile');
		/* $submitFld->setWrapperAttribute('class','col-xs-6');
		$submitFld->developerTags['col'] = 6; */
		$resetFld= $themeColorFrm->getField('btn_reset');
		$resetFld->setFieldTagAttribute('onclick','resetDefaultCurrentTemplate()');
		$resetFld->setFieldTagAttribute('class','block-on-mobile');
		/* $resetFld->setWrapperAttribute('class','col-xs-6');
		$resetFld->developerTags['col'] = 6; */
		$submitFld->attachField($resetFld);

	echo $themeColorFrm->getFormHtml(); ?>
	</div>
</div>
