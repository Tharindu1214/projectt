<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmTag->setFormTagAttribute('class', 'web_form form_horizontal');
$frmTag->setFormTagAttribute('onsubmit', 'setupTag(this); return(false);');
$frmTag->developerTags['colClassPrefix'] = 'col-md-';
$frmTag->developerTags['fld_default_col'] = 12;	

?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Tag_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	

<div class="col-sm-12">
	<h1><?php //echo Labels::getLabel('LBL_Tag_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addTagForm(<?php echo $tag_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive=($tag_id==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($tag_id>0){?> onclick="addTagLangForm(<?php echo $tag_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel("LBL_".$langName,$adminLangId);?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmTag->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
