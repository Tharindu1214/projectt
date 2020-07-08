<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$filterLangFrm->setFormTagAttribute('id', 'prodBrand');
$filterLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$filterLangFrm->setFormTagAttribute('onsubmit', 'setUpLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Product_Brand_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addForm(<?php echo $filtergroup_id ?>,<?php echo $filter_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($filter_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($filter_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="langForm(<?php echo $filter_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $filterLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
