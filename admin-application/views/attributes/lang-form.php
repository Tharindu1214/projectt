<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$attrLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$attrLangFrm->setFormTagAttribute('onsubmit', 'setupAttrLang(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Attribute_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<?php 
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo (!$attr_id) ? 'fat-inactive' : '';  ?>"><a class="<?php echo ($attr_lang_id==$langId) ? ' active' : ''; ?>" href="javascript:void(0);" <?php echo ($attr_id) ? "onclick='langForm( ".$attr_id.",".$langId." );'" : ""; ?>><?php echo $langName;?></a></li>
				<?php }
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $attrLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
