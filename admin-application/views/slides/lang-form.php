<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$slideLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$slideLangFrm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');	
$slideLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$slideLangFrm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">		
		<h4><?php echo Labels::getLabel('LBL_Slide_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="slideForm(<?php echo $slide_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($slide_id > 0) {
				foreach($languages as $langId => $langName){?>
					<li><a class="<?php echo ($slide_lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="slideLangForm(<?php echo $slide_id ?>, <?php echo $langId;?>);"><?php echo  Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
				}
			?>
			<li><a href="javascript:void(0)" <?php if( $slide_id > 0 ){ ?> onclick="slideMediaForm(<?php echo $slide_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $slideLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>

</div>
</div>
</section>