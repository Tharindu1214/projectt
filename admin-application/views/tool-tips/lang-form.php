<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
//$tooltipLangFrm->setFormTagAttribute('id', 'prodBrand');
$tooltipLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$tooltipLangFrm->setFormTagAttribute('onsubmit', 'setupTooltipLang(this,"'.$action.'"); return(false);');
$tooltipLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$tooltipLangFrm->developerTags['fld_default_col'] = 12; 	
if($action == 'edit'){
	$fld_tooltip_key = $tooltipLangFrm->getField('tooltip_default_value_new');
	$fld_tooltip_key->setFieldTagAttribute('disabled','disabled');
}
?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Tooltip_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<?php if($action == 'add'){?>
			<li><a href="javascript:void(0);" onclick="tooltipForm(<?php echo $tooltipId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php }?>
			<?php 
			if ($tooltipId > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($tooltip_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="tooltipLangForm(<?php echo $tooltipId ?>, <?php echo $langId;?>,'<?php echo $action?>');"><?php echo labels::getLabel("LBL_".$langName,$adminLangId);?></a></li>
				<?php }
			} ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $tooltipLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
</div></div></section>