<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$extraAttributeLangFrm->setFormTagAttribute('id', 'frmExtraAttributeLang');
$extraAttributeLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$extraAttributeLangFrm->setFormTagAttribute('onsubmit', 'setUpLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Attribute_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addForm(<?php echo $eattribute_eattrgroup_id ?>,<?php echo $eattribute_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($eattribute_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($attribute_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="langForm(<?php echo $eattribute_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $extraAttributeLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
