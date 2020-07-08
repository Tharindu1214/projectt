<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$mediaFrm->setFormTagAttribute('onsubmit', 'setupPromotion(this); return(false);');
$mediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$mediaFrm->developerTags['fld_default_col'] = 12;	

$fld1 = $mediaFrm->getField('banner_image');	
$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class','language-js');
$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class','display-js');

$preferredDimensionsStr = '<span class="uploadimage--info" > '.sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$adminLangId),$bannerWidth . ' * ' . $bannerHeight).'</span>';
$htmlAfterField = $preferredDimensionsStr; 
/* $htmlAfterField.='<div id="image-listing-js"></div>'; */
$fld1->htmlAfterField = $htmlAfterField;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Promotion_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
			
				<li><a href="javascript:void(0);" onClick="addPromotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>	
				<?php $inactive = ($promotionId==0)?'fat-inactive':'';	
				
				foreach($language  as $langId => $langName){?>	
					<li><a href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionLangForm(<?php echo $promotionId;?>,<?php echo $langId;?>)" <?php }?>>
				<?php echo $langName;?></a></li>
				<?php } ?>
				
				<?php if($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES){?>
				<li ><a  class="<?php echo $inactive; ?> active" href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>		
				<?php }?>			
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $mediaFrm->getFormHtml(); ?>
				</div>
			</div>	
			<div id="image-listing-js"></div>		
		</div>
	</div>						
</section>

<script>
	$(document).on('change','.display-js',function(){
		var promotionType = <?php echo $promotionType ?>;
		var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
		var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;

		if(promotionType == <?php echo Promotion::TYPE_SLIDES ?>){
			if($(this).val() == screenDesktop)
			{
				$('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '1350 * 405'));
			}
			else if($(this).val() == screenIpad)
			{
				$('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '1024 * 360'));
			}
			else{
				$('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '640 * 360'));
			}
		}else if(promotionType==<?php echo Promotion::TYPE_BANNER ?>){
			var deviceType = $(this).val();
			fcom.ajax(fcom.makeUrl('Promotions', 'getBannerLocationDimensions', [<?php echo $promotionId;?>,deviceType]), '', function(t) {
				var ans = $.parseJSON(t);
				$('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ans.bannerWidth +' * '+ ans.bannerHeight));
			});
		}
	});
</script>
