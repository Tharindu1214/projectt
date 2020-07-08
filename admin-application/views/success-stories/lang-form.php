<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('id', 'faqCat');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Success_Stories_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="storiesForm(<?php echo $sstory_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($sstory_id > 0) {
				foreach($languages as $langId => $langName){?>
					<li><a class="<?php echo ($sstory_lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="storiesLangForm(<?php echo $sstory_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
			} ?>			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $langFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
