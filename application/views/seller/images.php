<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script type="text/javascript">

$(function() {
	$("#sortable").sortable({
	  stop: function () {
		var mysortarr = new Array();
			$(this).find('li').each(function(){
			mysortarr.push($(this).attr("id"));
		});
		var product_id=$('#frmCustomProductImage input[name=product_id]').val();
		var sort = mysortarr.join('-');
		var lang_id = $('.language-js').val();
		var option_id = $('.option-js').val();
		data='&product_id='+product_id+'&ids='+sort;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setCustomProductImagesOrder' ), data, function (t) {
			productImages(product_id,option_id,lang_id);
		});
	  }
	}).disableSelection();
});

</script>
<?php if( !empty($images) ){ ?>
    <ul id="sortable" class="inline-images">
      <?php
		$count=1;
		foreach( $images as $afile_id => $row ){ ?>
      <li id="<?php echo $row['afile_id']; ?>"> <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($row['afile_record_id'], "THUMB", 0, $row['afile_id']),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>" title="<?php echo $row['afile_name'];?>" alt="<?php echo $row['afile_name'];?>"> <a class="deleteLink white" href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Delete',$siteLangId);?> <?php echo $row['afile_name'];?>" onclick="deleteCustomProductImage(<?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_id']; ?>);" class="delete"><i class="fa fa-times"></i></a> <?php echo ( $count == 1 ) ? '<small><strong>'.Labels::getLabel('LBL_Main_Photo',$siteLangId).'</strong></small>' : '&nbsp;';?></i></a>
        <?php if(!empty($imgTypesArr[$row['afile_record_subid']])){
					echo '<small class=""><strong>'.Labels::getLabel('LBL_Type',$siteLangId).':</strong> '.$imgTypesArr[$row['afile_record_subid']].'</small><br/>';
				}

				$lang_name = Labels::getLabel('LBL_All',$siteLangId);
				if( $row['afile_lang_id'] > 0 ){
					$lang_name = $languages[$row['afile_lang_id']];
				?>
        <?php } ?>
        <small class=""><strong> <?php echo Labels::getLabel('LBL_Language',$siteLangId);?>:</strong> <?php echo $lang_name; ?></small> </li>
      <?php $count++;}
			?>
    </ul>
    <?php }?>
