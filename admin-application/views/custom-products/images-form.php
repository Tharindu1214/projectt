<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$imagesFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$img_fld = $imagesFrm->getField('prod_image');
$img_fld->setFieldTagAttribute( 'onchange','submitImageUploadForm(); return false;');

$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 12;
$optionFld = $imagesFrm->getField('option_id');	
$optionFld->addFieldTagAttribute('class','option-js');
$langFld = $imagesFrm->getField('lang_id');	
$langFld->addFieldTagAttribute('class','language-js');

?>
<script type="text/javascript">
$(function() {
	$("#sortable").sortable({
	  stop: function () {
		var mysortarr = new Array();						
			$(this).find('li').each(function(){
			mysortarr.push($(this).attr("id"));				
		});
		var preq_id=$('#imageFrm input[name=preq_id]').val();			
		var sort = mysortarr.join('-');
		var lang_id = $('.language-js').val();
		var option_id = $('.option-js').val();		
		data='&preq_id='+preq_id+'&ids='+sort;
		fcom.updateWithAjax(fcom.makeUrl('products', 'setImageOrder' ), data, function (t) {
			productImages(preq_id,option_id,lang_id);
		});
	  }
	}).disableSelection();		
});
</script>


<section class="section">
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Product_Images',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">
<div class="col-sm-12">
  <h1><?php // echo Labels::getLabel('LBL_Product_Images',$adminLangId); ?> <?php //echo (count($product_images) > 0 && is_array($product_images)) ? '(Total Images: '. count($product_images).')' : ''; ?></h1>
  <div class="tabs_nav_container responsive flat">
    <div class="tabs_panel_wrap">
      <div class="tabs_panel"> <?php echo $imagesFrm->getFormHtml(); ?> </div>
    </div>
    <div id="imageupload_div" class="padd15">
      <?php if( !empty($product_images) ){ ?>
      <ul class="grids--onefifth ui-sortable" id="<?php if($canEdit){ ?>sortable<?php } ?>">
        <?php 
				$count=1;
				foreach( $product_images as $afile_id => $row ){ ?>
        <li id="<?php echo $row['afile_id']; ?>">
          <div class="logoWrap">
            <div class="logothumb"> <img src="<?php echo CommonHelper::generateUrl('image','customProduct', array($row['afile_record_id'], "THUMB",$row['afile_id']),CONF_WEBROOT_URL); ?>" title="<?php echo $row['afile_name'];?>" alt="<?php echo $row['afile_name'];?>"> <?php echo ( $count == 1 ) ? '<small><strong>'.Labels::getLabel('LBL_Default_Image',$adminLangId).'</strong></small>' : '&nbsp;'; if($canEdit){ ?> <a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $row['afile_name'];?>" onclick="deleteProductImage(<?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_id']; ?>);" class="delete"><i class="ion-close-round"></i></a>
              <?php } ?>
            </div>
            <?php if(!empty($imgTypesArr[$row['afile_record_subid']])){
							echo '<small class=""><strong>'.Labels::getLabel('LBL_Type',$adminLangId).': </strong> '.$imgTypesArr[$row['afile_record_subid']].'</small><br/>';
						} 
						
						$lang_name = Labels::getLabel('LBL_All',$adminLangId);
						if( $row['afile_lang_id'] > 0 ){
							$lang_name = $languages[$row['afile_lang_id']];
						?>
            <?php } ?>
            <small class=""><strong> <?php echo Labels::getLabel('LBL_Language',$adminLangId); ?>:</strong> <?php echo $lang_name; ?></small> </div>
        </li>
        <?php $count++;}
				?>
      </ul>
      <?php }	?>
    </div>
  </div>
</div>
</div></div></section>