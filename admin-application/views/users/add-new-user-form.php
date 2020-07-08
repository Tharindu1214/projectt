<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmUser->setFormTagAttribute('class', 'web_form form_horizontal');
$frmUser->setFormTagAttribute('onsubmit', 'addNewUsers(this); return(false);');


?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_User_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addUserForm();"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>	
			<li><a class="" href="javascript:void(0)" onclick="addBankInfoForm();"><?php echo Labels::getLabel('LBL_Bank_Info',$adminLangId); ?></a></li>	
			<li><a class="" href="javascript:void(0)" onclick="userAddresses();"><?php echo Labels::getLabel('LBL_Addresses',$adminLangId); ?></a></li>	
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmUser->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
