<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$langFrm->setFormTagAttribute('onsubmit', 'setupPromotionLang(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;

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
					<li ><a <?php echo ($langId == $promotion_lang_id) ? 'class="active"' : ''; ?>  href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionLangForm(<?php echo $promotionId;?>,<?php echo $langId;?>)" <?php }?>>
					<?php echo $langName;?></a></li>
				<?php } ?>
			
				<?php if($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES){?>
				<li ><a  class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>		
				<?php }?>			
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $langFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>
