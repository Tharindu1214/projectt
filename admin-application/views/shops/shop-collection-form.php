<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
	$shopLogoFrm->setFormTagAttribute('onsubmit', 'setupShopMedia(this); return(false);');
	$shopLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
	$shopLogoFrm->developerTags['fld_default_col'] = 12;
	$fld = $shopLogoFrm->getField('shop_logo');
	$fld->addFieldTagAttribute('class','btn btn--primary btn--sm');

	$shopBannerFrm->setFormTagAttribute('onsubmit', 'setupShopMedia(this); return(false);');
	$shopBannerFrm->developerTags['colClassPrefix'] = 'col-md-';
	$shopBannerFrm->developerTags['fld_default_col'] = 12;
	$fld = $shopBannerFrm->getField('shop_banner');
	$fld->addFieldTagAttribute('class','btn btn--primary btn--sm');

?>

<div class="tabs__content">
	<div class="form__content ">
		<div class="col-md-12" id="shopFormBlock">
			<div id="mediaResponse"></div>
			<div class="col-md-6">
				<div class="preview">
				  <small class="text--small"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_banner_text',$adminLangId),'1000*250')?></small>
				  <?php echo $shopBannerFrm->getFormHtml();?>
					<?php foreach($bannerAttachments as $img){?>
					<div class="row">
						<div class="profile__pic">
							<img src="<?php echo CommonHelper::generateUrl('Image','shopBanner',array($img['afile_record_id'],$img['afile_lang_id'],'PREVIEW'));?>" alt="">
						</div>
						<small class="text--small"><?php echo $bannerTypeArr[$img['afile_lang_id']];?></small>
						<div class="btngroup--fix">
							<a class = "btn btn--secondary btn--sm" href="javascript:void(0);" onClick="removeShopBanner(<?php echo $img['afile_record_id']; ?>,<?php echo $img['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove',$adminLangId);?></a>
						</div>
					</div>
					<span class="gap"></span>
					<?php }?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="preview">
					<small class="text--small"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_logo_text',$adminLangId),'60*60')?></small>
					<?php echo $shopLogoFrm->getFormHtml();?>
					<div class="row">
						<?php $count = 1; foreach($logoAttachments as $img){?>
						<div class="col-md-6">
							<div class="profile__pic"><img src="<?php echo CommonHelper::generateUrl('Image','shopLogo',array($img['afile_record_id'],$img['afile_lang_id'],'SMALL'));?>" alt=""></div>
							<small class="text--small"><?php echo $bannerTypeArr[$img['afile_lang_id']];?></small>
							<div class="btngroup--fix">
								<a class = "btn btn--secondary btn--sm" href="javascript:void(0);" onClick="removeShopLogo(<?php echo $img['afile_record_id']; ?>,<?php echo $img['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove',$adminLangId);?></a>
							</div>
						</div>
						<?php if($count == 2) {$count = 1; echo "<span class='gap'></span>";}?>
						<?php $count++;}?>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>
