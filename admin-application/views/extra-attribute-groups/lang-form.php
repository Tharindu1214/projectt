<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$extraAttrGroupLangFrm->setFormTagAttribute('id', 'frmExtraAttributeGroupLang');
$extraAttrGroupLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$extraAttrGroupLangFrm->setFormTagAttribute('onsubmit', 'setupExtraAttributeGroupLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Extra_Attribute_Group_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="extraAttributeGroupForm(<?php echo $eattrgroup_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ( $eattrgroup_id > 0) {
				foreach($languages as $langId => $langName){?>
					<li><a class="<?php echo ($eattrgroup_lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="extraAttributeGroupLangForm(<?php echo $eattrgroup_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $extraAttrGroupLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
