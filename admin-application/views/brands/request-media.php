<?php 
defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandLogoFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$brandLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
$brandLogoFrm->developerTags['fld_default_col'] = 12;
$logoFld = $brandLogoFrm->getField('logo');	
$logoFld->addFieldTagAttribute('class','btn btn--primary btn--sm');
$idFld = $brandLogoFrm->getField('brand_id');	
$idFld->addFieldTagAttribute('id','id-js');
$logoLangFld = $brandLogoFrm->getField('lang_id');	
$logoLangFld->addFieldTagAttribute('class','logo-language-js');
$logoPreferredDimensions = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$adminLangId),'500*500').'</small>';
$htmlAfterField = $logoPreferredDimensions; 
$htmlAfterField .= '<div id="logo-listing"></div>';
$logoFld->htmlAfterField = $htmlAfterField;

$brandImageFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$brandImageFrm->developerTags['colClassPrefix'] = 'col-md-';
$brandImageFrm->developerTags['fld_default_col'] = 12; 	
$imageFld = $brandImageFrm->getField('image');	
$imageFld->addFieldTagAttribute('class','btn btn--primary btn--sm');
$idFld = $brandImageFrm->getField('brand_id');	
$idFld->addFieldTagAttribute('id','id-js');
$imageLangFld = $brandImageFrm->getField('lang_id');	
$imageLangFld->addFieldTagAttribute('class','image-language-js');
$ImagePreferredDimensions = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$adminLangId),'2000*500').'<br/>'. Labels::getLabel('LBL_This_image_will_be_displayed_for_homepage_brands_collection',$adminLangId) .'</small>';
$htmlAfterField = $ImagePreferredDimensions; 
$htmlAfterField .= '<div id="image-listing"></div>';
$imageFld->htmlAfterField = $htmlAfterField;
?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Product_Brand_setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0)" onclick="brandRequestForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ( $brand_id == 0 ) ? 'fat-inactive' : '';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($brand_id>0){?> onclick="brandRequestLangForm(<?php echo $brand_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo labels::getlabel("LBL_".$langName,$adminLangId);?></a></li>
			<?php } ?>
			<li><a class="active" href="javascript:void(0)" onclick="brandRequestMediaForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
        <div class="tabs_panel_wrap">
            <div class="tabs_panel">
                <section class="">
                    <?php echo $brandLogoFrm->getFormHtml(); ?>
                </section>
                <section class="">
                <?php echo $brandImageFrm->getFormHtml(); ?>
                </section>
            </div>
        </div>
		
	</div>
</div>
