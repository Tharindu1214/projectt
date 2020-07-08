<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$pollingLangFrm->setFormTagAttribute('id', 'polling');
$pollingLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$pollingLangFrm->setFormTagAttribute('onsubmit', 'setupPollingLang(this); return(false);');
if(!empty($polling_type)){
	$polling_type_text ='';
	if($polling_type == Polling::POLLING_TYPE_PRODUCTS){
		$polling_type_text = 'Products';
	} else if($polling_type == Polling::POLLING_TYPE_CATEGORY){
		$polling_type_text = 'Categories';
	}
}
else
{
	die( Labels::getLabel('LBL_Required_variables_not_passed.',$adminLangId));
}
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Polling_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="pollingForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ( $polling_id > 0 ) {
				foreach( $languages as $langId=>$langName ){ ?>
					<li><a class="<?php echo ($polling_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="pollingLangForm(<?php echo $polling_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
			 } if(!empty($polling_type) && !empty($polling_type_text)){ ?>
			<li><a href="javascript:void(0)" onclick="linksForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $pollingLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
