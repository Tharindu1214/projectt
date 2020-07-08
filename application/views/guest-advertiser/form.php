<?php $frm->setFormTagAttribute('class', 'form form--normal');
$frm->setFormTagAttribute('onsubmit', 'register(this); return(false);');

$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
?>

<div class="section-head">
			<div class="section__heading">
				<h2><?php echo Labels::getLabel('LBL_Advertise_With_Us',$siteLangId);?></h2>
			</div>
					</div>
	<div class="registeration-process">
		<ul>
		  <li class="is--active"><a href="#"><?php echo Labels::getLabel('LBL_Details',$siteLangId);?></a></li>
		  <li><a href="#"><?php echo Labels::getLabel('LBL_Company_Details',$siteLangId);?></a></li>
		  <li><a href="#"><?php echo Labels::getLabel('LBL_Confirmation',$siteLangId);?></a></li>
		</ul>
	</div>
	<?php echo $frm->getFormTag(); ?>

    <div class="row">
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_NAME',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('user_name');?></div>
			   </div>
		   </div>
	   </div>
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_PHONE',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('user_phone');?></div>
			   </div>
		   </div>
	   </div>
   </div>
   <div class="row">
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_USERNAME',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('user_username');?></div>
			   </div>
		   </div>
	   </div>
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_EMAIL',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('user_email');?></div>
			   </div>
		   </div>
	   </div>
   </div>
   <div class="row">
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_PASSWORD',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('user_password');?></div>
				   <span class="text--small"><?php echo sprintf(Labels::getLabel('LBL_Example_password',$siteLangId),
'User@123') ?></span>
			   </div>
		   </div>
	   </div>
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_CONFIRM_PASSWORD',$siteLangId);?> <span class="mandatory">*</span></label></div>
			   <div class="field-wraper">
				   <div class="field_cover"><?php echo $frm->getFieldHTML('password1');?></div>
			   </div>
		   </div>
	   </div>
   </div>
   <div class="row">
	   <div class="col-md-6">
		   <div class="field-set">
			   <div class="caption-wraper"><label class="field_label"></label></div>
			   <div class="field-wraper">
				   <div class="field_cover">
				   <?php echo $frm->getFieldHTML('user_id');?>
				   <?php echo $frm->getFieldHTML('btn_submit');?>
				   </div>
			   </div>
		   </div>
	   </div>
   </div>
   <?php  echo $frm->getExternalJS();?>
</form>
