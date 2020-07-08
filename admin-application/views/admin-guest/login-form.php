 <?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
 <?php $userNameFld  = $frm->getField('username');
 $userNameFld->addFieldTagAttribute('placeholder',Labels::getlabel('LBL_Username',$adminLangId));
 $passwordFld  = $frm->getField('password');
 $passwordFld->addFieldTagAttribute('placeholder',Labels::getlabel('LBL_Password',$adminLangId));
 $rememberMeFld = $frm->getField('rememberme');
 $rememberMeFld->addFieldTagAttribute('class','switch-labels');
 ?>
    <div class="page__cell">
        <div class="container container-fluid container--narrow">
            <div class="box box--white">
                <figure class="logo"><img title="<?php echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId);?>" src="<?php echo CommonHelper::generateUrl('Image','siteAdminLogo', array(  $adminLangId)); ?>" alt="<?php echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId);?>"></figure>
                   
                    <div class="box__centered box__centered--form">
                         	<?php echo $frm->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">									
                                        <div class="field_cover field_cover--user"><?php echo $frm->getFieldHTML('username'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="field_cover field_cover--lock"><?php echo $frm->getFieldHTML('password'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-set">
                                        <label class="statustab -txt-uppercase">
											<?php  $remeberfld =  $frm->getFieldHTML('rememberme');  
												$remeberfld = str_replace("<label>","",$remeberfld);
												$remeberfld = str_replace("</label>","",$remeberfld);
												echo $remeberfld; 
												?>
                                           
                                            <i class="switch-handles"></i>
                                            <?php echo Labels::getlabel('LBL_Remember_me',$adminLangId);?>
                                      </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-set">
                                        <a href="<?php echo CommonHelper::generateUrl('adminGuest','forgotPasswordForm');?>" class="-link-underline -txt-uppercase -float-right"><?php echo Labels::getLabel('LBL_Forgot_Password?',$adminLangId); ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
										<?php echo $frm->getFieldHTML('btn_submit'); ?>
                                    </div>
                                </div>
                            </div>
							<?php echo $frm->getExternalJS(); ?>
                        </form>
                    </div>
                    
                </div>
            </div>
            
         