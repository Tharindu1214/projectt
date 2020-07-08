<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
 <div class="section-head">1. <?php echo Labels::getLabel('LBL_Log_In_/_Register', $siteLangId); ?> </div>
           
		   <div class="box--tabled">
   <div class="box__cell">
	  <?php $this->includeTemplate('guest-user/loginFormTemplate.php', $loginFormData,false); ?>
   </div>
   
   <div class="box__cell">
	  <h6><?php echo Labels::getLabel('LBL_Log_In_Help', $siteLangId); ?></h6>
	  <p><?php echo Labels::getLabel('LBL_Login_help_description', $siteLangId); ?></p>
	  <p><?php echo nl2br(Labels::getLabel('LBL_Login_help_points', $siteLangId)); ?></p>
   </div>
</div>

<h6><?php echo Labels::getLabel('LBL_New_Customer_Sign-Up', $siteLangId); ?></h6>
<p><?php echo Labels::getLabel('LBL_sign_up_help_description', $siteLangId); ?></p>

<?php 
/* <p class="text--dark"><?php echo sprintf(Labels::getLabel('LBL_New_to',$siteLangId),FatApp::getConfig('CONF_WEBSITE_NAME',$siteLangId));?>? 
<a href="<?php echo CommonHelper::generateUrl('GuestUser', 'loginForm'); ?>" class="text text--uppercase"><?php echo Labels::getLabel('LBL_Sign_Up',$siteLangId);?></a>
</p> */
?>


<div class="colscontainer">
   <div class="col__left">
	   <?php $this->includeTemplate('guest-user/registerationFormTemplate.php', $signUpFormData,false ); ?>
   </div>
   
   <div class="col__right">
	   <div class="preview">
			<h6><?php echo Labels::getLabel('LBL_checkout_Sign_Up_Help_Points_heading',$siteLangId); ?></h6>
			<p><?php echo nl2br(Labels::getLabel('LBL_checkout_Sign_Up_Help_Points', $siteLangId)); ?></p>
	   </div>
   </div>
</div>
