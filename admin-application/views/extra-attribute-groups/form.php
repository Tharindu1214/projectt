<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$extraAttrGroupsFrm->setFormTagAttribute('id', 'frmExtraAttributeGroup');
$extraAttrGroupsFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$extraAttrGroupsFrm->setFormTagAttribute('onsubmit', 'setupExtraAttributeGroup(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Extra_Attribute_Group_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="extraAttributeGroupForm(<?php echo $eattrgroup_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ( $eattrgroup_id == 0 ) ? 'fat-inactive' : '';	
			foreach($languages as $langId => $langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($eattrgroup_id>0){?> onclick="extraAttributeGroupLangForm(<?php echo $eattrgroup_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $extraAttrGroupsFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
