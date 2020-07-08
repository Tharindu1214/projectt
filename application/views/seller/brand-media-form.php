<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandReqMediaFrm->setFormTagAttribute('class', 'form form_horizontal');
$brandReqMediaFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$brandReqMediaFrm->developerTags['fld_default_col'] = 12;
$fld2 = $brandReqMediaFrm->getField('logo');
$fld2->addFieldTagAttribute('class','btn btn--primary btn--sm');

$preferredDimensionsStr = ' <small class="text--small">'. sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$siteLangId),'500*500').'</small>';

$htmlAfterField = $preferredDimensionsStr;
if( !empty($brandImages) ){
	$htmlAfterField .= '<div class="gap"></div><div class="row"><div class="col-lg-12 col-md-12"><div id="imageupload_div"><ul class="inline-images">';
	foreach($brandImages as $bannerImg){
		$htmlAfterField .= '<li>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'<img src="'.CommonHelper::generateFullUrl('Image','brandReal',array($bannerImg['afile_record_id'],$bannerImg['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeBrandLogo('.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].')" class="deleteLink white"><i class="fa fa-times"></i></a>';
		$lang_name = Labels::getLabel('LBL_All',$siteLangId);
				if( $bannerImg['afile_lang_id'] > 0 ){
					$lang_name = $languages[$bannerImg['afile_lang_id']];
				 } $htmlAfterField .='<small class=""><strong> '.Labels::getLabel('LBL_Language',$siteLangId).':</strong> '.$lang_name.'</small>';
	}
	$htmlAfterField.='</li></ul></div></div></div>';
}
$fld2->htmlAfterField = $htmlAfterField;
?>

<div class="box__head">
  <h4><?php echo Labels::getLabel('LBL_Request_New_Brand',$siteLangId); ?></h4>
</div>
<div class="box__body">
  <div class="tabs tabs--small tabs--scroll clearfix">
    <ul>
      <li><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
      <?php
			$inactive=($brandReqId==0)?'fat-inactive':'';
			foreach($languages as $langId=>$langName){
				?>
      <li class="<?php echo $inactive;?> "><a href="javascript:void(0);" <?php if($brandReqId>0){?> onclick="addBrandReqLangForm(<?php echo $brandReqId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
      <?php } ?>
      <li  class="is-active" ><a href="javascript:void(0)" onclick="brandMediaForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
    </ul>
  </div>
  <?php
		echo $brandReqMediaFrm->getFormHtml();

if( !empty($brandImages) ){
	?>
  <div class="gap"></div>
  <div class="row">
    <div class="col-lg-12 col-md-12">
      <div id="imageupload_div">
        <ul class="inline-images">
          <?php
	foreach($brandImages as $bannerImg){
		$htmlAfterField .= '<li>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'<img src="'.CommonHelper::generateFullUrl('Image','brandReal',array($bannerImg['afile_record_id'],$bannerImg['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeBrandLogo('.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].')" class="deleteLink white"><i class="fa fa-times"></i></a>';
		$lang_name = Labels::getLabel('LBL_All',$siteLangId);
				if( $bannerImg['afile_lang_id'] > 0 ){
					$lang_name = $languages[$bannerImg['afile_lang_id']];
				 } $htmlAfterField .='<small class=""><strong> '.Labels::getLabel('LBL_Language',$siteLangId).':</strong> '.$lang_name.'</small>';
	}?>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <?php
} ?>
</div>
