<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$fld1 = $frm->getField('image');
$fld1->addFieldTagAttribute('class','btn btn--primary btn--sm');

$htmlAfterField = '<span class="uploadimage--info">'. Labels::getLabel('LBL_This_will_be_displayed_in_30x30_on_your_store.',$adminLangId).'<br/>'. Labels::getLabel('LBL_SVG_images_are_not_supported_in_emails.',$adminLangId) .'</span>';
if( isset($img) && !empty($img) ){
	$htmlAfterField .= '<ul class="grids--onethird"> <li><div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('Image','SocialPlatform',array($splatform_id,'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeImg('.$splatform_id.')" class="remove--img"><i class="ion-close-round"></i></a></div></li></ul>';
}
$fld1->htmlAfterField = $htmlAfterField;
?>

<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Image_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Image_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addForm(<?php echo $splatform_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php
			if ( $splatform_id > 0 ) {
				foreach($languages as $langId=>$langName){?>
					<li><a href="javascript:void(0);" onclick="addLangForm(<?php echo $splatform_id ?>, <?php echo $langId;?>);"><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
				}
			?>
			<li><a class="active" href="javascript:void(0);" <?php if($splatform_id>0){?> onclick="mediaForm(<?php echo $splatform_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
