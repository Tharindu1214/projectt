<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Shipping_Api',$adminLangId); ?> </h1>
			<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchManualShipping(this); return(false);');
					$frmSearch->setFormTagAttribute ( 'id', 'frmManualShippingSearch' );
					$frmSearch->setFormTagAttribute ( 'class', 'web_form' );
					$frmSearch->developerTags['colClassPrefix'] = 'col-md-';					
					$frmSearch->developerTags['fld_default_col'] = 6;					
					
					$countryFld = $frmSearch->getField('country_id');
					$countryFld->setFieldTagAttribute('id','country_id');
					$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,0,\'#state_id\')');

					$stateFld = $frmSearch->getField('state_id');
					$stateFld->setFieldTagAttribute('id','state_id');
					
					$btn = $frmSearch->getField('btn_clear');
					$btn->setFieldTagAttribute('onClick','clearSearch()');
					echo  $frmSearch->getFormHtml();
				?>    
			</div>
		</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Shipping_Cost_List',$adminLangId); ?> </h4>
			<?php if($canEdit){ ?>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="manualShippingForm(0)";><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a>
			<?php } ?>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>
<script language="javascript">
$(document).ready(function(){
	getCountryStates($( "#country_id" ).val(),0,'#state_id');
});	
</script>