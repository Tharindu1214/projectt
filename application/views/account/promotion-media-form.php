<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
	$promotionMediaFrm->setFormTagAttribute('onsubmit', 'setupShopMedia(this); return(false);');
	$promotionMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
	$promotionMediaFrm->developerTags['fld_default_col'] = 12;
	$fld = $promotionMediaFrm->getField('promotion_media');
	$fld->addFieldTagAttribute('class','btn btn--primary btn--sm');
?>
<div class="tabs__content form">
	<div class="row">
		<div class="col-md-12">

				<div class="tabs tabs-sm tabs--scroll clearfix">
					<ul>
						<li><a href="javascript:void(0)" onClick="promotionGeneralForm(<?php echo $promotion_id ?>)"><?php echo Labels::getLabel('LBL_General',$siteLangId); ?></a></li>
						<?php $inactive = ($promotion_id==0)?'fat-inactive':'';
						foreach($language as $langId => $langName){?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0)" <?php if($promotion_id>0){ ?> onClick="promotionLangForm(<?php echo $promotion_id;?>,<?php echo $langId;?>)" <?php }?>>
						<?php echo $langName;?></a></li>
						<?php } ?>
						<li class="is-active"><a href="javascript:void(0)" onClick="promotionMediaForm(<?php echo $promotion_id;?>)"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
					</ul>
				</div>

			<div class="form__subcontent">
				<div id="mediaResponse"></div>
				<div class="col-md-6">
					<div class="preview">
					  <small class="text--small"><?php echo Labels::getLabel('MSG_Upload_Promotion_Media_text',$siteLangId); ?></small>
						<?php echo $promotionMediaFrm->getFormHtml();?>
						<?php foreach($bannerAttachments as $img){?>

							<div class=" col-md-12 profile__pic">
								<img src="<?php echo CommonHelper::generateUrl('Image','promotionMedia',array($img['afile_record_id'],$img['afile_lang_id'],'PREVIEW',$img['afile_id']));?>" alt="<?php echo Labels::getLabel('LBL_Promotion_Banner',$siteLangId);?>">
							</div>
							<div class="btngroup--fix">
								<a class = "btn btn--primary btn--sm" href="javascript:void(0);" onClick="removePromotionMedia(<?php echo $promotion_id; ?>,<?php echo $img['afile_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove',$siteLangId);?></a>
							</div>

						<span class="gap"></span>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
