<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$categoryReqLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$categoryReqLangFrm->setFormTagAttribute('onsubmit', 'setupCategoryReqLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Category_Request_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addCategoryReqForm(<?php echo $categoryReqId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($categoryReqId > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($scategoryreq_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="addCategoryReqLangForm(<?php echo $categoryReqId ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $categoryReqLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
