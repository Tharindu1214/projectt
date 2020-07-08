<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$frmImage->developerTags['colClassPrefix'] = 'col-md-';
$frmImage->developerTags['fld_default_col'] = 12;

$frmImage->setFormTagAttribute('class', 'web_form form_horizontal');
$frmImage->setFormTagAttribute('onsubmit', 'updateProductTempImage(this); return(false);');
$frmImage->addHiddenField('', 'afile_id',$afile_id);
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Edit_Product_Temp_Image',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li>
					<a class="active" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Temp_Image',$adminLangId); ?></a>
				</li>
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $frmImage->getFormHtml(); ?>
				</div>
			</div>
		</div>
	</div>
</section>
