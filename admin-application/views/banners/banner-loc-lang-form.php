<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$bannerLocLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$bannerLocLangFrm->setFormTagAttribute('onsubmit', 'langSetup(this); return(false);');
$bannerLocLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$bannerLocLangFrm->developerTags['fld_default_col'] = 12;

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
			<li><a href="javascript:void(0);" onclick="bannerLocation(<?php echo $blocationId;?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($blocationId > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($bannerLocaLangId==$langId)?'active':''?>" href="javascript:void(0);" onclick="bannerLocationLangForm(<?php echo $blocationId;?>,<?php echo $langId;?>);"><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
				}
			?>
			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $bannerLocLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>

</div>
</div>
</section>
