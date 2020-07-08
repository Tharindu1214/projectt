<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangGateway(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Payment_Method_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a href="javascript:void(0);" onclick="gatewayForm(<?php echo $pMethodId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php
				if ($pMethodId > 0) {
					foreach($languages as $langId=>$langName){?>
						<li><a class="<?php echo ($lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="editGatewayLangForm(<?php echo $pMethodId ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
					<?php }
					}
				?>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $langFrm->getFormHtml(); ?>
				</div>
			</div>
		</div>
	</div>
</section>
