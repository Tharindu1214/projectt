<?php
	defined('SYSTEM_INIT') or die('Invalid Usage.');

	$frm->setFormTagAttribute('class', 'web_form form_horizontal');
	$frm->setFormTagAttribute( 'onSubmit', 'uploadZip(); return false;' );
	$frm->developerTags['colClassPrefix'] = 'col-md-';
	$frm->developerTags['fld_default_col'] = 8;
?>
<section class="section">
	<div class="sectionhead">
	    <h4><?php echo Labels::getLabel('LBL_Upload_Bulk_Media',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
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
