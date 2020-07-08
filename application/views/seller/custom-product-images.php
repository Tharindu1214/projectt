<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$imagesFrm->setFormTagAttribute('id', 'frmCustomProductImage');
$optionFld = $imagesFrm->getField('option_id');	
$optionFld->addFieldTagAttribute('class','option-js');
$langFld = $imagesFrm->getField('lang_id');	
$langFld->addFieldTagAttribute('class','language-js');
$img_fld = $imagesFrm->getField('prod_image');
$img_fld->setFieldTagAttribute( 'onchange','setupCustomProductImages(); return false;');
?>
<div class="pop-up-title"><?php echo Labels::getLabel('LBL_Product_Images', $siteLangId); ?></div>
<?php 
		$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
		$imagesFrm->developerTags['fld_default_col'] = 6;
	echo $imagesFrm->getFormHtml(); ?>
<div class="row">
<div class="col-lg-12 col-md-12">
  <div id="imageupload_div">
    
  </div>
  </li>
</div>