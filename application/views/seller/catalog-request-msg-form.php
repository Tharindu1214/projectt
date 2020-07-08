<div class="popup__body">

	<div class="col-md-12">
	<?php echo $searchFrm->getFormHtml(); ?>
		<h5><?php echo Labels::getLabel('LBL_Catelog_Request_Messages', $siteLangId); ?> </h5>
		<div id="loadMoreBtnDiv"></div>
		<ul class="media media--details" id="messagesList">
		</ul>

		<?php
		$frm->setFormTagAttribute('onSubmit','setUpCatalogRequestMessage(this); return false;');
		$frm->setFormTagAttribute('class', 'form');
		$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
		$frm->developerTags['fld_default_col'] = 12;
		?>
		<ul class="media media--details" id="frmArea">
			<li>
				<div class="grid grid--first">
					<div class="avtar"><img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($logged_user_id, 'THUMB', 1)); ?>" alt="<?php echo $logged_user_name; ?>" title="<?php echo $logged_user_name; ?>"></div>
				</div>
				<div class="grid grid--second">
					<span class="media__title"><?php echo $logged_user_name; ?></span>
					<div class="grid grid--third">
						<div class="replaced">
						<?php echo $frm->getFormHtml(); ?>
						</div>
					</div>
				</div>

			</li>
		</ul>

	</div>
</div>
