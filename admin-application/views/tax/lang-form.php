<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$taxLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$taxLangFrm->setFormTagAttribute('onsubmit', 'setupTaxLang(this); return(false);');
$taxLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$taxLangFrm->developerTags['fld_default_col'] = 12;
?>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Sales_Tax_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a href="javascript:void(0);" onclick="taxForm(<?php echo $taxcat_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php 
				if ($taxcat_id > 0) {
					foreach($languages as $langId=>$langName){?>
						<li><a class="<?php echo ($taxcat_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="addTaxLangForm(<?php echo $taxcat_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
					<?php }
					}
				?>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $taxLangFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>

