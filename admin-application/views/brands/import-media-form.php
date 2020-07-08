<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'importFile("importMedia"); return false;' );
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Import_Brands',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="importBrandsForm();"><?php echo Labels::getLabel('LBL_Content',$adminLangId); ?></a></li>
			<li><a class="active" href="javascript:void(0);" onclick="importMediaForm();"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">			
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>			
		</div>
	</div>
</div>