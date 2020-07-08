<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'returnAddressFrm');
$frm->setFormTagAttribute('class','form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setReturnAddress(this); return(false);');

$countryFld = $frm->getField('ura_country_id');
$countryFld->setFieldTagAttribute('id','ura_country_id');
$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,'.$stateId.',\'#ura_state_id\')');

$stateFld = $frm->getField('ura_state_id');
$stateFld->setFieldTagAttribute('id','ura_state_id');

?>
<div class="row">	
	<div class="col-md-8">
		 
			<div class="tabs tabs-sm clearfix">
				<ul class="setactive-js">
					<li class="is-active"><a href="javascript:void(0)" onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_General',$siteLangId); ?></a></li>
					<?php foreach($languages as $langId => $langName){?>
					<li><a href="javascript:void(0);" onclick="returnAddressLangForm(<?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php } ?>
				</ul>
			</div>
		 
		<?php echo $frm->getFormHtml();?>
	</div>
</div>
<script language="javascript">
$(document).ready(function(){
	getCountryStates($( "#ura_country_id" ).val(),<?php echo $stateId ;?>,'#ura_state_id');
});	
</script>