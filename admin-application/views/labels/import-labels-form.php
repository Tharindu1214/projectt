<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'submitImportLaeblsUploadForm(); return false;' );
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$fld = $frm->getField('import_file');
//$fld->setFieldTagAttribute( 'onchange','submitImportLaeblsUploadForm(); return false;');?>

<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Import_Labels',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
			<div class="col-sm-12">
				<h1><?php //echo Labels::getLabel('LBL_Import_Labels',$adminLangId); ?></h1>
				<div class="tabs_nav_container responsive flat">
					<div class="tabs_panel_wrap">

						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
						</div>
						<div id="fileupload_div"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>