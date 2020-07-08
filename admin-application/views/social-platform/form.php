<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

?><section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Social_Platform_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	
<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Social_Platform_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addForm(<?php echo $splatform_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ($splatform_id == 0) ? 'fat-inactive' : '';	
			foreach( $languages as $langId => $langName ){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" 
				<?php if($splatform_id>0){?> onclick="addLangForm(<?php echo $splatform_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
			<?php } ?>
			<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($splatform_id>0){?> onclick="mediaForm(<?php echo $splatform_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
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