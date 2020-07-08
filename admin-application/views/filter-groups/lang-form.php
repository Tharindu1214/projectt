<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$filterGroupLangFrm->setFormTagAttribute('id', 'frmFilterGroups');
$filterGroupLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$filterGroupLangFrm->setFormTagAttribute('onsubmit', 'setupFilterGroupLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Filter_Group_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="filterGroupForm(<?php echo $filtergroup_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($filtergroup_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($filtergroup_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="filterGroupLangForm(<?php echo $filtergroup_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $filterGroupLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
