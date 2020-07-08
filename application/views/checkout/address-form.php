<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
    if(!isset($cityId)){
        $cityId = 0;
    }
    $addressFrm->developerTags['fld_default_col'] = 12;
    $addressFrm->developerTags['colClassPrefix'] = 'col-md-';
    $addressFrm->setFormTagAttribute('class', 'form form--normal');
    $addressFrm->setFormTagAttribute('onsubmit', 'setUpAddress(this); return(false);');

    $ua_identifierFld = $addressFrm->getField('ua_identifier');
    $ua_identifierFld->developerTags['col'] = 6;

    $ua_nameFld = $addressFrm->getField('ua_name');
    $ua_nameFld->developerTags['col'] = 6;

    $countryFld = $addressFrm->getField('ua_country_id');
    $countryFld->developerTags['col'] = 6;
    $countryFld->setFieldTagAttribute('id','ua_country_id');
    $countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value, 0 ,\'#ua_state_id\')');

    $stateFld = $addressFrm->getField('ua_state_id');
    $stateFld->developerTags['col'] = 6;
    $stateFld->setFieldTagAttribute('id','ua_state_id');
    $stateFld->setFieldTagAttribute('onChange','getcities(this.value, 0 ,\'#ua_city_id\')');

    $cityFld = $addressFrm->getField('ua_city');
    $cityFld->developerTags['col'] = 12;
    $cityFld->setFieldTagAttribute('id','ua_city_id');
    $cityFld->setFieldTagAttribute('data-live-search',true);
    $cityFld->setFieldTagAttribute('class','selectpicker');
    
    /*$zipFld = $addressFrm->getField('ua_zip');
    $zipFld->developerTags['col'] = 6;*/

    $phoneFld = $addressFrm->getField('ua_phone');
    $phoneFld->developerTags['col'] = 6;

    $submitFld = $addressFrm->getField('btn_submit');
    $cancelFld = $addressFrm->getField('btn_cancel');
    $cancelFld->setFieldTagAttribute('class','btn btn--primary-border');
    $cancelFld->setFieldTagAttribute('onclick','resetAddress()');
?>
<div class="section-head">
	<div class="section__heading">
		<h2><?php
        $heading = Labels::getLabel('LBL_Billing_Address', $siteLangId);
        if ($cartHasPhysicalProduct) {
            $heading = Labels::getLabel('LBL_Billing/Delivery_Address', $siteLangId);
        }
        echo $heading; ?></h2>
	</div>
</div>
<div class="box box--white box--radius p-4">
    <section id="billing" class="section-checkout">
        <div class="section-head">
    		<div class="section__heading">
    			<h6><?php echo $labelHeading; ?></h6>
    		</div>
    	</div>
    </section>
    <?php echo $addressFrm->getFormHtml(); ?>
</div>

<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<link href="page-css/select2.min.css" rel="stylesheet" />
<script src="page-js/select2.min.js"></script> -->


<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#ua_country_id").val(), <?php echo $stateId ;?>, '#ua_state_id');
        getcities('<?php echo $stateId ;?>', '<?php echo $cityId ;?>', '#ua_city_id');
    });
</script>
