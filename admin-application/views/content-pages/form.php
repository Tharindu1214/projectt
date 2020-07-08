<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$blockFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$blockFrm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$blockFrm->developerTags['colClassPrefix'] = 'col-md-';
$blockFrm->developerTags['fld_default_col'] = 12;

$identiFierFld = $blockFrm->getField('cpage_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup',"Slugify(this.value,'urlrewrite_custom','cpage_id');
getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val())");
$IDFld = $blockFrm->getField('cpage_id');
$IDFld->setFieldTagAttribute('id',"cpage_id");
$urlFld = $blockFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id',"urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Cms','View',array($cpage_id),CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onKeyup',"getSlugUrl(this,this.value)");

$pageLayout = $blockFrm->getField('cpage_layout');
$pageLayout->setFieldTagAttribute('onchange',"showLayout($(this))");
?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Content_Pages_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">


			<div class="col-sm-12">
				<h1><?php //echo Labels::getLabel('LBL_Content_Pages_Setup',$adminLangId); ?></h1>
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a class="active" href="javascript:void(0)" onclick="addForm(<?php echo $cpage_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<?php
						$inactive = ($cpage_id == 0)?'fat-inactive':'';
						foreach($languages as $langId=>$langName){?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
							<?php if($cpage_id>0){?> onclick="addLangForm(<?php echo $cpage_id ?>, <?php echo $langId;?>, <?php echo $cpage_layout;?>);" <?php }?>>
							<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
							<?php } ?>
						</ul>
						<div class="tabs_panel_wrap">
							<div class="tabs_panel">
								<div class="row">
									<div class="col-md-8">
										<?php echo $blockFrm->getFormHtml(); ?>
									</div>
									<div class="col-md-4" id="viewLayout-js"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
