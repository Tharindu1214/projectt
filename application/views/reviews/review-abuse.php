<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute( 'class', 'form form--horizontal' );
$frm->setFormTagAttribute('onsubmit', 'setupReviewAbuse(this);return false;');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<div class="panel">
	<div class="container">
	   <div class="row">
			<div class="col-xs-10 panel__right--full " >
				<div class="cols--group">
					<div class="panel__head">
						<h2><?php echo Labels::getLabel('LBL_Report_Abuse', $siteLangId); ?></h2>
					</div>
					<div class="panel__body">
						<div class="box box--white  p-4">
							<div class="box__body">
								<?php echo $frm->getFormHtml(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
