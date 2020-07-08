<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$tagLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$tagLangFrm->setFormTagAttribute('onsubmit', 'setupTagLang(this); return(false);');
$tagLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$tagLangFrm->developerTags['fld_default_col'] = 12;	

?>

<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Tag_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	
<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Product_Tag_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addTagForm(<?php echo $tag_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($tag_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($tag_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="addTagLangForm(<?php echo $tag_id ?>, <?php echo $langId;?>);"><?php echo Labels::getLabel("LBL_".$langName,$adminLangId);?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $tagLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
