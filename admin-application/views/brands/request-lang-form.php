<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodBrandLangFrm->setFormTagAttribute('id', 'prodBrand');
$prodBrandLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$prodBrandLangFrm->setFormTagAttribute('onsubmit', 'setupBrandLang(this); return(false);');
$prodBrandLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodBrandLangFrm->developerTags['fld_default_col'] = 12; 	


?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Product_Brand_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="brandRequestForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($brand_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($brand_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="brandRequestLangForm(<?php echo $brand_id ?>, <?php echo $langId;?>);"><?php echo labels::getLabel("LBL_".$langName,$adminLangId);?></a></li>
				<?php }
				} ?>
			<li><a href="javascript:void(0);" <?php if( $brand_id > 0 ){?> onclick="brandRequestMediaForm(<?php echo $brand_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $prodBrandLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
