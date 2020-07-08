 <?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
 <script src='https://www.google.com/recaptcha/api.js'></script>
 <?php ?>
    <div class="page__cell">
        <div class="container container-fluid container--narrow">
		
			  <div class="box box--white">
                    <figure class="logo"><img src="<?php echo CommonHelper::generateUrl('Image','siteAdminLogo', array(  $adminLangId)); ?>" alt=""></figure>
                   
                   <div class="-align-center">
                       <h3><?php echo Labels::getLabel('LBL_Forgot_Your_Password?',CommonHelper::getLangId()); ?> </h3>
                       <p><?php echo Labels::getLabel('LBL_Enter_The_E-mail_Address_Associated_With_Your_Account',$adminLangId) ?></p>
                   </div>
                    <div class="box__centered box__centered--form">
                        <?php echo $frmForgot->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="field_cover field_cover--mail"><?php echo $frmForgot->getFieldHTML('admin_email'); ?></div>
                                    </div>
                                </div>
                            </div>
							<?php if(FatApp::getConfig('CONF_RECAPTCHA_SITEKEY',FatUtility::VAR_STRING,'') != '' ){?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="field_cover">
                                            <div class="captcha-wrap"><?php echo $frmForgot->getFieldHTML('security_code'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
							<?php }?>							
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                       <?php echo $frmForgot->getFieldHTML('btn_forgot'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row -align-center">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <a href="<?php echo CommonHelper::generateUrl('adminGuest','loginForm');?>" class="-link-underline -txt-uppercase"><?php echo Labels::getLabel('LBL_Back_to_Login',$adminLangId); ?></a>
                                    </div>
                                </div>
                            </div>
							<?php echo $frmForgot->getExternalJS(); ?>
                        </form>
                    </div>
                    
                </div>
          
		
	 </div>
	</div>
            
         