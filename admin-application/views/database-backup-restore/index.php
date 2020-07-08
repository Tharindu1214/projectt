<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$backup_frm->setFormTagAttribute('class', 'web_form');
$backup_frm->developerTags['colClassPrefix'] = 'col-md-';
$backup_frm->developerTags['fld_default_col'] = 6;

$upload_frm->setFormTagAttribute('class', 'web_form');
$upload_frm->developerTags['colClassPrefix'] = 'col-md-';
$upload_frm->developerTags['fld_default_col'] = 6;
?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Database_Backup_and_Restore',$adminLangId); ?></h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>	
				<section class="section">
					<div class="sectionbody space">
						<div class="tablewrap">
							<?php echo $backup_frm->getFormHtml(); ?>
						</div> 
					</div>
				</section>
				<section class="section">
					<div class="sectionbody space">
						<div class="tablewrap">
							<?php echo $upload_frm->getFormHtml(); ?>
						</div> 
					</div>
				</section>
				<section class="section">
					<div class="sectionhead">
						<h4><?php echo Labels::getLabel('LBL_DB_Backup_Files_List',$adminLangId); ?></h4>
					</div>
					<div class="sectionbody">
						<div class="tablewrap" id="listing">
                            
                        </div>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>