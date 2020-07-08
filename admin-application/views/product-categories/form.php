<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodCatFrm->setFormTagAttribute('id', 'prodCate');
$prodCatFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodCatFrm->developerTags['fld_default_col'] = 12;
// $prodCatFrm->setFormTagAttribute('action', CommonHelper::generateUrl('ProductCategories', 'setup',array($tabSelected,$prodcat_id)));
$prodCatFrm->setFormTagAttribute('class', 'web_form form_horizontal');
// $prodCatFrm->setValidatorJsObjectName('prodCateFrmObj');
$prodCatFrm->setFormTagAttribute('onsubmit', 'setupCategory(this); return(false);');


$identiFierFld = $prodCatFrm->getField('prodcat_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup',"Slugify(this.value,'urlrewrite_custom','prodcat_id');getSlugUrl($(\"#urlrewrite_custom\"),$(\"#urlrewrite_custom\").val(),'".$parentUrl."','pre',true)");
$IDFld = $prodCatFrm->getField('prodcat_id');
$IDFld->setFieldTagAttribute('id',"prodcat_id");
$urlFld = $prodCatFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id',"urlrewrite_custom");
$urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Category','View',array($prodcat_id),CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onkeyup',"getSlugUrl(this,this.value)");
?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Product_Category_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	

	<div class="col-sm-12">

			<div class="tabs_nav_container responsive flat">
				<ul class="tabs_nav">
					<li><a class="active" href="javascript:void(0)" onclick="categoryForm(<?php echo $prodcat_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
					<?php 
					$inactive = ( $prodcat_id == 0 ) ? 'fat-inactive' : '';	
					foreach( $languages as $langId => $langName ){ ?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($prodcat_id>0){?> onclick="categoryLangForm(<?php echo $prodcat_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
					<?php } ?>
					<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($prodcat_id>0){?> onclick="categoryMediaForm(<?php echo $prodcat_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
				</ul>
				<div class="tabs_panel_wrap">
					<div class="tabs_panel">
						<?php echo $prodCatFrm->getFormHtml(); ?>
					</div>
				</div>
			</div>
	<?php /*?><section class="section first">
		<div class="sectionbody space">
			<?php echo $prodCateFrm->getFormHtml(); ?>
		 </div>
	</section>
	<?php */?>
			</div>
		</div>
	</div>
</section>