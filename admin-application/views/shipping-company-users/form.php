<?php 
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmUser->setFormTagAttribute('class', 'web_form form_horizontal');
$frmUser->setFormTagAttribute('onsubmit', 'setupUsers(this); return(false);');

if( $user_id > 0 ){
	$fld_credential_username = $frmUser->getField('credential_username');
	$fld_credential_username->setFieldTagAttribute('disabled','disabled');

	$user_email = $frmUser->getField('credential_email');
	$user_email->setFieldTagAttribute('disabled','disabled');
}

$frmUser->developerTags['colClassPrefix'] = 'col-md-';
$frmUser->developerTags['fld_default_col'] = 12;

$dobFld = $frmUser->getField('user_dob');
$dobFld->setFieldTagAttribute('class','user_dob_js');	

$countryFld = $frmUser->getField('user_country_id');
$countryFld->setFieldTagAttribute('id','user_country_id');
$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,'.$stateId.',\'#user_state_id\')');

$stateFld = $frmUser->getField('user_state_id');
$stateFld->setFieldTagAttribute('id','user_state_id');
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Shipping_Company_User',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="border-box border-box--space">
			<?php echo $frmUser->getFormHtml(); ?>
		</div>
	</div>
</section>
<script language="javascript">
$(document).ready(function(){
	getCountryStates($( "#user_country_id" ).val(),<?php echo $stateId ;?>,'#user_state_id');
	$('.user_dob_js').datepicker('option', {maxDate: new Date()});
});
</script>