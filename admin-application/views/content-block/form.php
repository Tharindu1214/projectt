<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$blockFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$blockFrm->setFormTagAttribute('onsubmit', 'setupBlock(this); return(false);');
$blockFrm->developerTags['colClassPrefix'] = 'col-md-';
$blockFrm->developerTags['fld_default_col'] = 12;

$identiFierFld = $blockFrm->getField('epage_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup',"Slugify(this.value,'urlrewrite_custom','epage_id');
getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val())");
$IDFld = $blockFrm->getField('epage_id');
$IDFld->setFieldTagAttribute('id',"epage_id");
$urlFld = $blockFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id',"urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Custom','View',array($epage_id),CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onKeyup',"getSlugUrl(this,this.value)");
?>
<section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Content_Block_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">

<div class="col-sm-12">
	<h1><?php // echo Labels::getLabel('LBL_Content_Block_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addBlockForm(<?php echo $epage_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php
			$inactive = ($epage_id == 0)?'fat-inactive':'';
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
				<?php if($epage_id>0){?> onclick="addBlockLangForm(<?php echo $epage_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $blockFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div></div></section>
