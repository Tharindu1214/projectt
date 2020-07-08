<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php
	$frm->setFormTagAttribute('class','form form--horizontal');
	$frm->setFormTagAttribute('onSubmit', 'setUpSendMessage(this); return false;');
	$fromFld = $frm->getField('send_message_from');
	$toFld = $frm->getField('send_message_to');

	$fromFldHtml = new HtmlElement( 'div', array( 'class'=>'field-set' ));
	$fromFldCaptionWrapper = $fromFldHtml->appendElement('div', array('class' => 'caption-wraper'));
	$fromFldCaptionWrapper->appendElement( 'label', array('class'=>'field_label'), Labels::getLabel('LBL_From', $siteLangId) );

	$fromFldFieldWrapper = $fromFldHtml->appendElement('div', array('class' => 'field-wraper'));
	$fromFldData = $loggedUserData['credential_username'].' (<em>'.$loggedUserData['user_name'].'</em>)';
	$fromFldData .= '<br/><span class="text--small">'.Labels::getLabel('LBL_Contact_info_not_shared', $siteLangId).'</span>';
	$fromFldFieldWrapper->appendElement( 'div', array('class' => 'field_cover'), $fromFldData, true );

	$fromFld->value = $fromFldHtml->getHtml();


	$toFldHtml = new HtmlElement( 'div', array( 'class'=>'field-set' ));
	$toFldCaptionWrapper = $toFldHtml->appendElement('div', array('class' => 'caption-wraper'));
	$toFldCaptionWrapper->appendElement( 'label', array('class'=>'field_label'), Labels::getLabel('LBL_To', $siteLangId) );

	$toFldFieldWrapper = $toFldHtml->appendElement('div', array('class' => 'field-wraper'));
	$toFldFieldWrapper->appendElement( 'div', array('class' => 'field_cover'), $shop['shop_owner_name'].' (<em>'.$shop['shop_name'].'</em>)', true );

	$toFld->value = $toFldHtml->getHtml();
?>
<div class="container container--fixed">
	<div class="row">
		<div class="panel panel--centered clearfix">

			<div class="col-md-9 col--right">
				<div class="profile box box--white">
					<div class="profile__head">
						<div class="row">
							<?php
								$shop_city = $shop['shop_city'];
								$shop_state = ( strlen($shop['shop_city']) > 0 ) ? ', '. $shop['shop_state_name'] : $shop['shop_state_name'];
								$shop_country = ( strlen($shop_state) > 0 ) ? ', '.$shop['shop_country_name'] : $shop['shop_country_name'];
								$shopLocation = $shop_city . $shop_state. $shop_country;
							?>
							<div class="col-md-10 col-sm-8">
								<h3><?php echo $shop['shop_name']; ?></h3>
								<p><?php echo $shopLocation; ?> <?php echo Labels::getLabel('LBL_Opened_on', $siteLangId); ?> <?php echo FatDate::format($shop['shop_created_on']); ?></p>
								<h4>Send Message to Shop Owner</h4>
								<?php echo $frm->getFormHtml(); ?>
							</div>

						</div>
					</div>
					<div class="profile__body">

					</div>
					<div class="profile__footer">
						<div class="row">
						</div>
					</div>
				</div>

				<div class="section section--sorting">
					<div class="row">

						<div class="col-md-6 col-sm-4">
							<div class="search search--sort hide--mobile">
							</div>
						</div>
						<div class="col-md-6 col-sm-8 align--right">
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
