<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if( !empty($images) ){
	$htmlAfterField = '<div class="gap"></div><ul class="image-listing">';
	foreach($images as $bannerImg){
		$imgUrl =  '';
		switch($promotionType){
			case Promotion::TYPE_BANNER:
				$imgUrl = CommonHelper::generateFullUrl('Banner','Thumb',array($bannerImg['afile_record_id'],$bannerImg['afile_lang_id'],$bannerImg['afile_screen']),CONF_WEBROOT_FRONT_URL);
			break;
			case Promotion::TYPE_SLIDES:
				$imgUrl = CommonHelper::generateFullUrl('Image','Slide',array($bannerImg['afile_record_id'],$bannerImg['afile_screen'],$bannerImg['afile_lang_id'],'THUMB'),CONF_WEBROOT_FRONT_URL);
			break;
		}

		$htmlAfterField .= '<li><p>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'</p><p>'.$screenTypeArr[$bannerImg['afile_screen']].'</p><img src="'.$imgUrl.'"> <a href="javascript:void(0);" onClick="removePromotionBanner('.$promotionId.','.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].','.$bannerImg['afile_screen'].')" class="closeimg">x</a>';
	}
	$htmlAfterField.='</li></ul>';
	echo $htmlAfterField;
}
?>
