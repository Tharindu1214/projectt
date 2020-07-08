<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$extraAttributeFrm->setFormTagAttribute('id', 'extraAttributeFrm');
$extraAttributeFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$extraAttributeFrm->setFormTagAttribute('onsubmit', 'setUp(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Attribute_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addForm(<?php echo $eattrgroup_id; ?>,<?php echo $eattribute_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive=($eattribute_id==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($eattribute_id>0){?> onclick="langForm(<?php echo $eattribute_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php }
			
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $extraAttributeFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
