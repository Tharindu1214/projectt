<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="heading3"><?php echo Labels::getLabel('LBL_Advertise_With_Us',$siteLangId);?></div>
<div class="registeration-process">
	<ul>
	  <li><a href="#"><?php echo Labels::getLabel('LBL_Details',$siteLangId);?></a></li>
	  <li><a href="#"><?php echo Labels::getLabel('LBL_Company_Details',$siteLangId);?></a></li>
	  <li class="is--active"><a href="#"><?php echo Labels::getLabel('LBL_Your_Password',$siteLangId);?></a></li>
	  <li><a href="#"><?php echo Labels::getLabel('LBL_Confirmation',$siteLangId);?></a></li>
	</ul>
</div>
<?php
	$passwordFrm->setFormTagAttribute('onsubmit', 'setupPasswordForm(this); return(false);');
	$passwordFrm->setFormTagAttribute('class','form form--normal');
	$passwordFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
	$passwordFrm->developerTags['fld_default_col'] = 12;	
	echo $passwordFrm->getFormHtml();
?>