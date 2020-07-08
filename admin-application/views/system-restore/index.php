<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$restore_point_frm->setFormTagAttribute('class', 'web_form form_horizontal');
$restore_point_frm->developerTags['colClassPrefix'] = 'col-md-';
$restore_point_frm->developerTags['fld_default_col'] = '12';
?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5>System Restore Point</h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>	
				<section class="section">
					<div class="sectionbody space">
						<div class="tablewrap">
							<?php echo $restore_point_frm->getFormHtml(); ?>
						</div> 
					</div>
				</section>
			</div>		
		</div>
	</div>
</div>