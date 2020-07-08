<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
        <div class="page">
            <div class="container container-fluid">
                <div class="row">
                   <div class="col-lg-12 col-md-12 space">
                       
                        <div class="page__title">
                            <div class="row">
                                <div class="col--first col-lg-6">
                                    <span class="page__icon"><i class="ion-android-star"></i></span>
                                    <h5><?php echo Labels::getLabel('LBL_Change_Password',$adminLangId); ?></h5>
									<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Change_Password',$adminLangId); ?></h4>
                            </div>
                            <div class="sectionbody space">
							<?php 
							$pwdFrm->addFormTagAttribute('class', 'web_form form_horizontal');
							$pwdFrm->setFormTagAttribute('autocomplete', 'off');
							echo $pwdFrm->getFormtag(); ?> 
							 <div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label"><?php echo Labels::getLabel('LBL_Current_Password',$adminLangId); ?><span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php
												$curPwd = $pwdFrm->getField('current_password');
												$curPwd->setFieldTagAttribute('autocomplete', 'off');
												echo $pwdFrm->getFieldHTML('current_password'); 
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							 <div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label"><?php echo Labels::getLabel('LBL_New_Password',$adminLangId); ?><span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $pwdFrm->getFieldHTML('new_password'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>							
							 <div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label"><?php echo Labels::getLabel('LBL_Confirm_New_Password',$adminLangId); ?><span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $pwdFrm->getFieldHTML('conf_new_password'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>	
							<div class="row">
								<div class="col-md-12">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label"></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
											<?php echo $pwdFrm->getFieldHTML('btn_submit'); ?>
											<!--input value="cancel" type="button" -->
											</div>
										</div>
									</div>
								</div>
							</div>		
							</form><?php echo $pwdFrm->getExternalJS(); ?>
                            </div>
                        </div>               
                    </div>     
                </div>
            </div>
        </div>  