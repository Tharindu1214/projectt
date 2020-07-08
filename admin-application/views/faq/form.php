<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$faqFrm->setFormTagAttribute('id', 'prodBrand');
$faqFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$faqFrm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$faqFrm->developerTags['colClassPrefix'] = 'col-md-';
$faqFrm->developerTags['fld_default_col'] = 12;

?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_FAQ_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		

<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_FAQ_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="faqForm(<?php echo $faqcat_id ?>,<?php echo $faq_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ($faq_id==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" 
				<?php if($faq_id>0){?> onclick="faqLangForm(<?php echo $faqcat_id ?>,<?php echo $faq_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo $langName;?></a></li>
			<?php } ?>			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $faqFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>