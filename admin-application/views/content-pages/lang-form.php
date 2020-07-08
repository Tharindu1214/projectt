<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$blockLangFrm->setFormTagAttribute('class', 'web_form layout--'.$formLayout);
$blockLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$blockLangFrm->developerTags['fld_default_col'] = 12;

if($cpage_layout == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE)
{
	$fld = $blockLangFrm->getField('cpage_bg_image');
	$fld->addFieldTagAttribute('class','btn btn--primary btn--sm');

	$preferredDimensionsStr = '<small class="text--small"> '.Labels::getLabel('LBL_This_will_be_displayed_on_your_cms_Page.',$adminLangId).' '.sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$adminLangId),'1300*400').'</small>';

	$htmlAfterField = $preferredDimensionsStr;
	if( !empty($bgImages) ){
		$htmlAfterField .= '<ul class="image-listing grids--onethird">';
		foreach($bgImages as $bgImage){
		$htmlAfterField .= '<li>'.$bannerTypeArr[$bgImage['afile_lang_id']].'<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('image','cpageBackgroundImage',array($bgImage['afile_record_id'],$bgImage['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeBgImage('.$bgImage['afile_record_id'].','.$bgImage['afile_lang_id'].','.$cpage_layout.')" class="remove--img"><i class="ion-close-round"></i></a></div>';
		}
		$htmlAfterField.='</li></ul>';
	} else {
		$htmlAfterField.='<div class="temp-hide"><ul class="image-listing grids--onethird"><li><div class="uploaded--image"></div></li></ul></div>';
	}
	$fld->htmlAfterField = $htmlAfterField;
}
?>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Content_Pages_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<h1><?php //echo Labels::getLabel('LBL_Content_Pages_Setup',$adminLangId); ?></h1>
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a href="javascript:void(0);" onclick="addForm(<?php echo $cpage_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<?php
						if ($cpage_id > 0) {
							foreach($languages as $langId => $langName){?>
								<li><a class="<?php echo ($cpage_lang_id == $langId)?'active':''?>" href="javascript:void(0);"
								onclick="addLangForm(<?php echo $cpage_id ?>, <?php echo $langId;?>, <?php echo $cpage_layout;?>);"><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
							<?php }
							}
						?>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php
								echo $blockLangFrm->getFormTag();
								echo $blockLangFrm->getFormHtml(false);
								echo '</form>';
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
