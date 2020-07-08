<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'bpCat');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCategory(this); return(false);');
$identiFierFld = $frm->getField('bpcategory_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup',"Slugify(this.value,'urlrewrite_custom','bpcategory_id');getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val(),'','pre',true)");
$IDFld = $frm->getField('bpcategory_id');
$IDFld->setFieldTagAttribute('id',"bpcategory_id");
$urlFld = $frm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id',"urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Blog','Category',array($bpcategory_id),CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onkeyup',"getSlugUrl(this,this.value)");
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;





?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Blog_Post_Category_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="categoryForm(<?php echo $bpcategory_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>
			<?php 
			$inactive=($bpcategory_id==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($bpcategory_id>0){?> onclick="categoryLangForm(<?php echo $bpcategory_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);;?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>