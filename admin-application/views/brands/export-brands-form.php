<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'exportBrands(this); return false;' );
?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Export_Brands',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0);" onclick="exportBrandsForm();"><?php echo Labels::getLabel('LBL_Content',$adminLangId); ?></a></li>
			<li><a href="javascript:void(0);" onclick="exportMediaForm();"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">			
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>			
		</div>
	</div>
</div>
</div>
</div>
</section>