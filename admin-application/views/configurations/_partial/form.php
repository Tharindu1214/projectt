<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div id="tabs_0<?php echo $form_type; ?>" class="tabs_panel">
	<div class="wrapcenter">
						
						<?php 
							
							$frm->setValidatorJsObjectName ( 'formValidator_'.$form_type );
							$frm->setFormTagAttribute ( 'onsubmit', 'submitForm(this,formValidator_'.$form_type.'); return(false);' );
							//$frm->setFormTagAttribute ( 'onsubmit', 'submitForm(this); return(false);' );
							$frm->setFormTagAttribute ( 'class', 'web_form' );
							$frm->developerTags['fld_default_col'] = 6;
							$frm->setFormTagAttribute ( 'action', CommonHelper::generateUrl("configurations","action") );
							echo $frm->getFormTag();
							echo $table->getHtml();
							echo $frm->getExternalJS();
							
						?>	
						</form>
						
	</div>    
</div>		
		