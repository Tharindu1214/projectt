<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupEtpls(this); return(false);');

$fld = $frm->getField('etpl_code');
$fld->setFieldTagAttribute('disabled','disabled');
?>
<div class="col-sm-12">
	<h1>Manage Email Templates</h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<?php
			$inactive=($etplCode=='')?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){ ?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($etplCode != ''){?> onclick="editEtplLangForm('<?php echo $etplCode ?>', <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>