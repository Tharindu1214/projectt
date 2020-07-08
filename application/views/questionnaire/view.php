<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Questionnaire','setup'));
$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-6 col-sm-';
$frm->developerTags['fld_default_col'] = 6;
$formFields = $frm->getAllFields();
?>

<div id="body" class="body bg--white">
  <section class="section bg--white">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 align--center">
          <div class="width--narrow">
            <h2><?php echo $questionnaire['questionnaire_name']; ?></h2>
            <p><?php echo $questionnaire['questionnaire_description']; ?></p>
          </div>
        </div>
      </div>
       
        <div class="panel panel--centered clearfix">
         
            <div class="section clearfix">
              <div class="section__body">
                <div class="box box--white box--listing"> <?php echo $frm->getFormTag(); ?>
                  <div class="replaced"> <?php echo $frm->getFieldHtml('qfeedback_questionnaire_id'); ?>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="field-set">
                          <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('Lbl_Name',$siteLangId); ?> <span class="mandatory">*</span></label>
                          </div>
                          <div class="field-wraper">
                            <div class="field_cover">
                              <?php $frm->getField('qfeedback_user_name')->addFieldTagAttribute('placeholder','e.g James');
															echo $frm->getFieldHtml('qfeedback_user_name'); ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="field-set">
                          <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('Lbl_Email',$siteLangId); ?> <span class="mandatory">*</span></label>
                          </div>
                          <div class="field-wraper">
                            <div class="field_cover">
                              <?php $frm->getField('qfeedback_user_email')->addFieldTagAttribute('placeholder','e.g james@dummyid.com');
															echo $frm->getFieldHtml('qfeedback_user_email'); ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="field-set">
                          <div class="caption-wraper">
                            <label class="field_label"><?php echo Labels::getLabel('Lbl_Gender',$siteLangId); ?> <span class="mandatory">*</span></label>
                          </div>
                          <div class="field-wraper">
                            <div class="field_cover"> <?php echo $frm->getFieldHtml('qfeedback_user_gender'); ?> </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="q-group-container">
                    <?php foreach($formFields as $formField){
											$fldName = $formField->getName();
											if(in_array($fldName,array('qfeedback_user_email','qfeedback_user_name','qfeedback_user_gender','qfeedback_questionnaire_id'))){
												continue;
											}
											$fld = $frm->getField($fldName);
											if( $formField->fldType == 'radio'){
												$fld->setOptionListTagAttribute('class','listing--vertical listing--vertical-chcek');
												$fld->developerTags['rdLabelAttributes'] = array('class'=>'radio');
												$fld->developerTags['rdHtmlAfterRadio'] = '<i class="input-helper"></i>';
											}
											if( $formField->fldType == 'checkboxes'){
												$fld->setOptionListTagAttribute('class','listing--vertical listing--vertical-chcek');
												$fld->developerTags['cbLabelAttributes'] = array('class'=>'checkbox');
												$fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
											}
										?>
                    <div class="q-group">
                      <?php if($formField->fldType == 'submit'){
												echo $fld->getHtml();
											} else { ?>
                      <div class="q-group__head">
                        <h6><?php echo $fld->getCaption();echo !empty($fld->requirements()->getRequirementsArray()['required'])?'<span class="mandatory">*</span>':"" ?></h6>
                      </div>
                      <?php if( $formField->fldType == 'checkboxes' || $formField->fldType == 'radio'){
												/* make the field required false due to wrong validation check performed on checkbox list */
												$fld->requirements()->setRequired(false);
											} ?>
                      <div class="q-group__body <?php echo ($fld->getWrapperAttribute('class') == 'rating-f') ? 'rating-f' : ''; ?>"> <?php echo $fld->getHtml(); ?> </div>
                      <?php } ?>
                    </div>
                    <?php }	?>
                  </div>
                  </form>
                  <?php echo $frm->getExternalJs(); ?> </div>
              </div>
            </div>
          
        </div>
      
    </div>
  </section>
	<div class="gap"></div>
</div>
<script type="text/javascript">
$(document).ready(function () {
	$('.star-rating').barrating({ showSelectedRating:false });
});
</script>