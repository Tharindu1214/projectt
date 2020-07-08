<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$spPlanFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$spPlanFrm->setFormTagAttribute('onsubmit', 'submitPlanForm(this); return(false);');
$spPlanFrm->developerTags['colClassPrefix'] = 'col-md-';
$spPlanFrm->developerTags['fld_default_col'] = 12;

$fldFreqText=$spPlanFrm->getField(SellerPackagePlans::DB_TBL_PREFIX.'frequency_text');
$fldFreqText->htmlAfterField='<br/><small class="text--small">'.Labels::getLabel('LBL_PLease_Specify_the_Years_for_unlimited_years',$adminLangId).'</small>';
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Seller_Packages_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="editPackageForm(<?php echo $spackageId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>	
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $spPlanFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>

<script>
setPlanFields(<?php echo $spackageType;?>);	
</script>