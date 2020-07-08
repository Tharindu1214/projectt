<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$spackageFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$spackageFrm->setFormTagAttribute('onsubmit', 'submitPackageForm(this); return(false);');
$spackageFrm->developerTags['colClassPrefix'] = 'col-md-';
$spackageFrm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Seller_Packages_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="editPackageForm(<?php echo $spackageId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>
				<?php 
				$inactive=($spackageId==0)?'fat-inactive':'';	
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($spackageId>0){?> onclick="editPackageLangForm(<?php echo $spackageId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
				<?php } ?>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $spackageFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>	