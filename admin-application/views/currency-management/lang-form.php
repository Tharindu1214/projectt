<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangCurrency(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Currency_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a href="javascript:void(0);" onclick="currencyForm(<?php echo $currencyId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php 
				if ($currencyId > 0) {
					foreach($languages as $langId=>$langName){?>
						<li><a class="<?php echo ($lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="editCurrencyLangForm(<?php echo $currencyId ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
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

