<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupRewardsOnPurchase(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Rewards_On_Purchase_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="border-box border-box--space">
			<?php echo $frm->getFormHtml(); ?>
		</div>
	</div>											
</section>
