<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangPackage(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Seller_Packages_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a href="javascript:void(0)" onclick="editPackageForm(<?php echo $spackageId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>
				<?php 
				$inactive=($spackageId==0)?'fat-inactive':'';	
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo $inactive;?>"><a  class="<?php echo ($lang_id == $langId)?'active':''?>" href="javascript:void(0);" <?php if($spackageId>0){?> onclick="editPackageLangForm(<?php echo $spackageId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
				<?php } ?>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $langFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>

