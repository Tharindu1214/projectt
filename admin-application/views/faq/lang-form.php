<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$faqLangFrm->setFormTagAttribute('id', 'faqCat');
$faqLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$faqLangFrm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
$faqLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$faqLangFrm->developerTags['fld_default_col'] = 12;


?>

<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Faq_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		

<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Faq_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="faqForm(<?php echo $faqcat_id ?>,<?php echo $faq_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($faq_id > 0) {
				foreach($languages as $langId => $langName){?>
					<li><a class="<?php echo ($faq_lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="faqLangForm(<?php echo $faqcat_id ?>,<?php echo $faq_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
			} ?>			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $faqLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div></div></div></section>