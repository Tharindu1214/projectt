<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 <?php echo (empty($pageData)) ? '' : '';?>">
                    <div class="section-head">
                        <div class="section__heading mb-3">
                            <h3><?php echo Labels::getLabel('LBL_Reset_Password', $siteLangId);?></h3>
                            <p><?php echo Labels::getLabel('LBL_Reset_Password_Msg', $siteLangId);?></p>
                        </div>
                    </div>
                    <?php
                    $frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
                    $frm->setFormTagAttribute('class', 'form');
                    $frm->setValidatorJsObjectName('resetValObj');
                    $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                    $frm->developerTags['fld_default_col'] = 12;
                    $frm->setFormTagAttribute('action', '');
                    $btnFld = $frm->getField('btn_submit');
                    $btnFld->setFieldTagAttribute('class', 'btn--block');
                    $frm->setFormTagAttribute('onSubmit', 'resetpwd(this, resetValObj); return(false);');
                    $passFld = $frm->getField('new_pwd');
                    $passFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_NEW_PASSWORD', $siteLangId));
                    $confirmFld = $frm->getField('confirm_pwd');
                    $confirmFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_CONFIRM_NEW_PASSWORD', $siteLangId)); ?>

                    <?php echo $frm->getFormTag();    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('new_pwd'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('confirm_pwd'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('btn_submit'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo $frm->getFieldHtml('user_id');
                          echo $frm->getFieldHtml('token');
                          echo $frm->getExternalJS(); ?>
                    </form>
                </div>
                <?php if (!empty($pageData)) {
                              $this->includeTemplate('_partial/GuestUserRightPanel.php', $pageData, false);
                          } ?>
            </div>
        </div>
    </section>
</div>
