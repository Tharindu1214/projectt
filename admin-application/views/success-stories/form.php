<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'prodBrand');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Success_Stories_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="storiesForm(<?php echo $sstory_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ($sstory_id==0)?'fat-inactive':'';	
			foreach($languages as $langId => $langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" 
				<?php if($sstory_id>0){?> onclick="storiesLangForm(<?php echo $sstory_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo $langName;?></a></li>
			<?php } ?>			
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
