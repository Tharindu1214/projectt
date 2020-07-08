<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmTax->setFormTagAttribute('class', 'web_form form_horizontal');
$frmTax->setFormTagAttribute('onsubmit', 'setupTax(this); return(false);');
$frmTax->developerTags['colClassPrefix'] = 'col-md-';
$frmTax->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Tax_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="taxForm(<?php echo $taxcat_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php 
				$inactive=($taxcat_id==0)?'fat-inactive':'';	
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($taxcat_id>0){?> onclick="addTaxLangForm(<?php echo $taxcat_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
				<?php } ?>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $frmTax->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>

