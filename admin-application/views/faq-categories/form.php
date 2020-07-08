<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$faqCatFrm->setFormTagAttribute('id', 'faqCat');
$faqCatFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$faqCatFrm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$faqCatFrm->developerTags['colClassPrefix'] = 'col-md-';
$faqCatFrm->developerTags['fld_default_col'] = 12;

?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Faq_Category_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		

<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Faq_Category_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="faqCatForm(<?php echo $faqcat_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ($faqcat_id==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" 
				<?php if($faqcat_id>0){?> onclick="faqCatLangForm(<?php echo $faqcat_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
			<?php } ?>			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $faqCatFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>

</div>
</div>
</section>