<?php 
	defined('SYSTEM_INIT') or die('Invalid Usage.');
	$prodSpecFrm->setFormTagAttribute('class', 'form web_form');
	$prodSpecFrm->setFormTagAttribute('onsubmit', 'return submitSpecificationForm(this); return(false);');
	$prodSpecFrm->developerTags['fld_default_col']=12;

?>
<?php echo $prodSpecFrm->getFormTag();?>

<?php foreach($languages as $langId=>$langName){ ?>
<div class="row">
 <div class="col-lg-12 col-md-12 col-sm-12 col-xm-12 ">
  <div class="row"> 
  <div class="col-md-12">
   <div class="field-set">
     <div class="caption-wraper">
       <h5><?php  echo $langName;?></h5>
     </div>
   </div>
  </div> 
 </div>
 </div>
</div>
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12 col-xm-12">
<div class="row"> 
 <div class="col-lg-6 col-md-6 col-md-12 <?php echo 'layout--'.Language::getLayoutDirection($langId); ?>">
   <div class="field-set">
     <div class="caption-wraper">
       <label class="field_label"><?php  $fld = $prodSpecFrm->getField('prod_spec_name['.$langId.']');
		echo $fld->getCaption();?><span class="mandatory">*</span></label>
     </div>
     <div class="field-wraper">
       <div class="field_cover">
        <?php if(isset($data['prod_spec_name['.$langId.']']))$fld->value= $data['prod_spec_name['.$langId.']'];?>
        <?php echo $prodSpecFrm->getFieldHtml('prod_spec_name['.$langId.']');?>
       </div>
     </div>
   </div>
 </div> 
 <div class="col-lg-6 col-md-6 col-md-12 <?php echo 'layout--'.Language::getLayoutDirection($langId); ?>">
   <div class="field-set">
     <div class="caption-wraper">
       <label class="field_label"><?php $fld = $prodSpecFrm->getField('prod_spec_value['.$langId.']');  echo $fld->getCaption();?><span class="mandatory">*</span></label>
     </div>
     <div class="field-wraper">
       <div class="field_cover">
        <?php   if(isset($data['prod_spec_value['.$langId.']']))  $fld->value= $data['prod_spec_value['.$langId.']']; 
		      echo $prodSpecFrm->getFieldHtml('prod_spec_value['.$langId.']');?>
       </div>
     </div>
   </div>
 </div> </div>
 </div>
</div>
<?php  } ?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xm-12">
	<div class="row"> 
		<div class="col-md-12">
		   <div class="field-set">
			 <div class="caption-wraper">
			   <label class="field_label"><?php  $fld = $prodSpecFrm->getField('btn_submit');
			  echo $fld->getCaption();?></label>
			 </div>
			 <div class="field-wraper">
			   <div class="field_cover">
				<?php echo $prodSpecFrm->getFieldHtml('product_id');?>
				<?php echo $prodSpecFrm->getFieldHtml('prodspec_id');?>
				<?php echo $prodSpecFrm->getFieldHtml('btn_submit');?>
			   </div>
			 </div>
		   </div>
		 </div> 
	 </div>
</div>
</div>
<?php echo $prodSpecFrm->getExternalJs();?>
</form>	