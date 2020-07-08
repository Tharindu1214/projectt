<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'bpCat');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$identiFierFld = $frm->getField('post_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup',"Slugify(this.value,'urlrewrite_custom','post_id');getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val())");
$IDFld = $frm->getField('post_id');
$IDFld->setFieldTagAttribute('id',"post_id");
$urlFld = $frm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id',"urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Blog','postDetail',array($post_id),CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onkeyup',"getSlugUrl(this,this.value)");
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
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
						<li><a class="active" href="javascript:void(0);" onclick="blogPostForm(<?php echo $post_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($post_id>0){?> onclick="linksForm(<?php echo $post_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Link_Category',$adminLangId); ?></a></li>
						<?php foreach($languages as $langId=>$langName){ ?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($post_id>0){?> onclick="langForm(<?php echo $post_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
						<?php } ?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($post_id>0){?> onclick="postImages(<?php echo $post_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Post_Images',$adminLangId); ?></a></li>
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