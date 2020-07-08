<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$bannerLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$bannerLangFrm->setFormTagAttribute('onsubmit', 'langSetup(this); return(false);');
$bannerLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$bannerLangFrm->developerTags['fld_default_col'] = 12;


?>


<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Banner_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="bannerForm(<?php echo $blocation_id;?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($banner_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($banner_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="bannerLangForm(<?php echo $blocation_id;?>,<?php echo $banner_id ?>, <?php echo $langId;?>);"><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
				}
			?>
			<li><a href="javascript:void(0)" onclick="mediaForm(<?php echo $blocation_id ?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $bannerLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
</div>
</div>
</section>