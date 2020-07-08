<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOptionsLang->setFormTagAttribute('class', 'web_form form_horizontal');
$frmOptionsLang->setFormTagAttribute('onsubmit', 'setupOptionsLang(this); return(false);');
$frmOptionsLang->developerTags['colClassPrefix'] = 'col-md-';
$frmOptionsLang->developerTags['fld_default_col'] = 6;

?><section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Option_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">


<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Options_Setup',$adminLangId); ?></h1>
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addOptionForm(<?php echo $option_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			if ($option_id > 0) {
				foreach($languages as $langId=>$langName){?>
					<li><a class="<?php echo ($option_lang_id==$langId)?'active':''?>" href="javascript:void(0);" onclick="addOptionLangForm(<?php echo $option_id ?>, <?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }
				}
			?>
		</ul>
		<div class="sectionbody space">
			<div class=" border-box border-box--space">
				<?php echo $frmOptionsLang->getFormHtml(); ?>
			</div>
		</div>
</div>
</div></div></section>	