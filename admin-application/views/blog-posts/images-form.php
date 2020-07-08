<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$imagesFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 12;

$img_fld = $imagesFrm->getField('post_image');
$img_fld->addFieldTagAttribute('class','btn btn--primary btn--sm');

$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class','language-js');

$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions',$adminLangId),'1000*563').'</small>';
$htmlAfterField = $preferredDimensionsStr;
$img_fld->htmlAfterField = $htmlAfterField; 
?>
<script type="text/javascript">
	$(function() {
		$("#sortable").sortable({
		  stop: function () {
			var mysortarr = new Array();						
				$(this).find('li').each(function(){
				mysortarr.push($(this).attr("id"));				
			});
			var post_id=$('#imageFrm input[name=post_id]').val();			
			var sort = mysortarr.join('-');				
			data='&post_id='+post_id+'&ids='+sort;
			fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'setImageOrder' ), data, function (t) {
				postImages(post_id);
			});
		  }
		}).disableSelection();		
	});
</script>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Blog_Post_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
		<?php $inactive=($post_id==0)?'fat-inactive':''; ?>
			<li><a href="javascript:void(0);" onclick="blogPostForm(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a href="javascript:void(0);" onclick="linksForm(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_Link_Category',$adminLangId); ?></a></li>
			
			<?php 
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($post_id>0){?> onclick="langForm(<?php echo $post_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
			<li><a class="active" href="javascript:void(0);" onclick="postImages(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_Post_Images',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
			<div class="col-sm-12">
				<h4><?php echo Labels::getLabel('LBL_Post_Images',$adminLangId); ?></h4>
				<?php echo $imagesFrm->getFormHtml(); ?>
				<div id="image-listing"></div>
			</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>