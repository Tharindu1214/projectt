<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'faqCms');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupFaqToCms(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_CMS_Page_To_Faq',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">		
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
			<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
