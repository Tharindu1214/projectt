<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$promotionLangFrm->setFormTagAttribute('onsubmit', 'setupPromotionLang(this); return(false);');
$promotionLangFrm->setFormTagAttribute('class','form form--horizontal layout--'.$formLayout);
$promotionLangFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$promotionLangFrm->developerTags['fld_default_col'] = 12;
?>
	
	
	<div class="row">
		<div class="col-md-12">			 
				<div class="tabs tabs-sm clearfix">
					<ul>
						<li><a href="javascript:void(0)" onClick="promotionGeneralForm(<?php echo $promotion_id ?>)"><?php echo Labels::getLabel('LBL_General',$siteLangId); ?></a></li>
						<?php $inactive = ($promotion_id==0)?'fat-inactive':'';		
						foreach($language as $langId => $langName){?>	
						<li class="<?php echo ($formLangId == $langId)?'is-active':'' ; ?>"><a href="javascript:void(0)" <?php if($promotion_id>0){ ?> onClick="promotionLangForm(<?php echo $promotion_id;?>,<?php echo $langId;?>)" <?php }?>>
						<?php echo $langName;?></a></li>
						<?php } ?>
						<li class="<?php echo $inactive; ?>"><a href="javascript:void(0)" onClick="promotionMediaForm(<?php echo $promotion_id;?>)"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
					</ul>
				</div>
			 
			<div class="form__subcontent">
				<?php echo $promotionLangFrm->getFormTag();
				echo $promotionLangFrm->getFormHtml(false);
				echo '</form>'; ?>
			</div>	
		</div>	
	</div>
