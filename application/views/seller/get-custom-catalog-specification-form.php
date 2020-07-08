<div class="replaced specification" id="specification<?php echo $divCount; ?>">
	<?php foreach($languages as $langId=>$langName){ ?>
	<div class="row align-items-center">
		<div class="col-md-4 ">
		   <div class="field-set">
			 <div class="caption-wraper">
			   <h5><?php  echo $langName;?></h5>
			 </div>
		   </div>
		</div>
		<div class="col-md-4 ">
		   <div class="field-set">
			 <div class="caption-wraper">
			   <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Name',$siteLangId)?></label>
			 </div>
			 <div class="field-wraper">
			   <div class="field_cover">
				<input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="Specification Name" type="text" name="prod_spec_name[<?php echo $langId ?>][<?php echo $divCount ?>]" value="">
			   </div>
			 </div>
		   </div>
		</div>
		<div class="col-md-3">
		   <div class="field-set">
			 <div class="caption-wraper">
			   <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value',$siteLangId)?></label>
			 </div>
			 <div class="field-wraper">
			   <div class="field_cover">
				<input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="Specification Value" type="text" name="prod_spec_value[<?php echo $langId ?>][<?php echo $divCount ?>]" value="">
			   </div>
			 </div>
		   </div>
		</div>
		<?php if($langId == key( array_slice( $languages, -1, 1, TRUE ) )){ ?>
		<div class="col-md-1 align--right">
		  <button type="button" onclick="removeSpecDiv(<?php echo $divCount ?>);" class="btn btn--primary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove',$siteLangId)?>"  ><i class="fa fa-minus"></i></button>
		</div>
		<?php }?>
	</div>
	<?php  } ?>
</div>
