<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php 
	$onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';
	$loginFrm->setFormTagAttribute('class', 'form seller-login');
	$loginFrm->setValidatorJsObjectName('loginValObj');
	$loginFrm->setFormTagAttribute('action', CommonHelper::generateUrl('GuestUser', 'login')); 
	$loginFrm->setFormTagAttribute('onsubmit', $onSubmitFunctionName . '(this, loginValObj); return(false);');
	$loginFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-4 col-sm-';
	$loginFrm->developerTags['fld_default_col'] = 4;
	$loginFrm->removeField($loginFrm->getField('remember_me'));
	$loginFrm->addHtml('','forgotPassword','<a class="forgot" href="'.CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm').'">'.Labels::getLabel('LBL_Forgot_Password?',$siteLangId).'</a>');
	$fldSubmit = $loginFrm->getField('btn_submit');

echo $loginFrm->getFormTag();
?>
<?php 

$usernameFld = $loginFrm->getField('username');
$usernameFld->setFieldTagAttribute('class','no--focus');

$passwordFld = $loginFrm->getField('password');
$passwordFld->setFieldTagAttribute('class','no--focus');

?>

<div class="field-set"> <?php echo $loginFrm->getFieldHtml('username'); ?> </div>
<div class="field-set"> <?php echo $loginFrm->getFieldHtml('password') ?> </div>
<div class="field-set "> <?php echo $loginFrm->getFieldHtml('btn_submit'); ?> </div>
<div> <?php echo $loginFrm->getFieldHtml('forgotPassword'); ?> </div>
<?php echo $loginFrm->getExternalJs();  ?>
</form>
