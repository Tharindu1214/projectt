<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'profileInfoFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;

$fld = $frm->getField('user_profile_info');
$fld->developerTags['col'] = 6;

$fld = $frm->getField('user_products_services');
$fld->developerTags['col'] = 6;

$submitFld = $frm->getField('btn_submit');
$submitFld->developerTags['col'] = 12;
$submitFld->developerTags['noCaptionTag'] = true;

$frm->setFormTagAttribute('onsubmit', 'updateProfileInfo(this); return(false);');

$usernameFld = $frm->getField('credential_username');
$usernameFld->setFieldTagAttribute('disabled', 'disabled');

$userDobFld = $frm->getField('user_dob');
if (!empty($data['user_dob']) && $data['user_dob'] != '0000-00-00') {
    $userDobFld->setFieldTagAttribute('disabled', 'disabled');
}

$userDobFld->setFieldTagAttribute('class', 'user_dob_js');

$emailFld = $frm->getField('credential_email');
$emailFld->setFieldTagAttribute('disabled', 'disabled');

$countryFld = $frm->getField('user_country_id');
$countryFld->setFieldTagAttribute('id', 'user_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,'.$stateId.',\'#user_state_id\')');

$stateFld = $frm->getField('user_state_id');
$stateFld->setFieldTagAttribute('id', 'user_state_id');


$imgFrm->setFormTagAttribute('action', CommonHelper::generateUrl('Account', 'uploadProfileImage'));
/* $imgFrm->setFormTagAttribute('id', 'imageFrm');
$fld = $imgFrm->getField('user_profile_image');
$fld->addFieldTagAttribute('class','btn btn--primary btn--sm'); */
?>
<div class="row">
    <div class="col-xl-4">
        <div class="row">
            <div class="col-xl-12 col-lg-6 mb-4">
			<div class=" bg-gray rounded p-4">
                <div class="row align-items-center" id="profileImageFrmBlock">
                    <div class="col-6">
                        <div class="avtar avtar--large">
                            <?php 
                                $userId = UserAuthentication::getLoggedUserId();
                                $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
                                $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);

                                $profileImg = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'user', array($userId,'thumb',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                            ?>
                            <img src="<?php echo $profileImg;?>" alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId);?>">
                            <!--img src="<?php /* echo CommonHelper::generateUrl('Account', 'userProfileImage', array(UserAuthentication::getLoggedUserId(), 'croped', true)).'?t='.time(); ?>"
                                alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId); */?>"-->
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="btngroup--fix">
                            <?php echo $imgFrm->getFormTag();    ?>
                            <span class="btn btn--primary btn--sm btn--fileupload mt-1">
                                <?php echo $imgFrm->getFieldHtml('user_profile_image'); ?><?php echo ($mode == 'Edit') ? Labels::getLabel('LBL_Change', $siteLangId): Labels::getLabel('LBL_Upload', $siteLangId) ;?>
                            </span>
                            <?php echo $imgFrm->getFieldHtml('update_profile_img');
                            echo $imgFrm->getFieldHtml('rotate_left');
                            echo $imgFrm->getFieldHtml('rotate_right');
                            echo $imgFrm->getFieldHtml('remove_profile_img');
                            echo $imgFrm->getFieldHtml('action');
                            echo $imgFrm->getFieldHtml('img_data');
                            ?>
                            <?php if ($mode == 'Edit') { ?>
                            <a class="btn btn--primary-border btn--sm mt-1" href="javascript:void(0)" onClick="removeProfileImage()"><?php echo Labels::getLabel('LBL_Remove', $siteLangId);?></a>
                            <?php }?>
                            </form>
                            <?php echo $imgFrm->getExternalJS();?>
                            <div id="dispMessage"></div>
                        </div>
                    </div>
                </div>
				    </div>
            </div>
            <div class="col-xl-12 col-lg-6 mb-4">
                <?php if (User::canViewBuyerTab() && User::canViewSupplierTab()) { ?>
               <div class=" bg-gray rounded p-4"> <div class="align-items-center">
                    <h5><?php echo Labels::getLabel('LBL_Preferred_Dashboard', $siteLangId);?> </h5>
                    <div class="switch-group">
                        <ul class="switch setactive-js">
                            <?php if (User::canViewBuyerTab() && (User::canViewSupplierTab() || User::canViewAdvertiserTab() || User::canViewAffiliateTab())) { ?>
                            <li <?php echo (User::USER_BUYER_DASHBOARD == $data['user_preferred_dashboard'])?'class="is-active"':''?>><a href="javascript:void(0)"
                                    onClick="setPreferredDashboad(<?php echo User::USER_BUYER_DASHBOARD ;?>)"><?php echo Labels::getLabel('LBL_Buyer', $siteLangId);?></a></li>
                            <?php } ?>
                            <?php if (User::canViewSupplierTab() && (User::canViewBuyerTab() || User::canViewAdvertiserTab() || User::canViewAffiliateTab())) { ?>
                            <li <?php echo (User::USER_SELLER_DASHBOARD == $data['user_preferred_dashboard'])?'class="is-active"':''?>><a href="javascript:void(0)"
                                    onClick="setPreferredDashboad(<?php echo User::USER_SELLER_DASHBOARD ;?>)"><?php echo Labels::getLabel('LBL_Seller', $siteLangId);?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
				</div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <?php echo $frm->getFormHtml();?>
    </div>
</div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#user_country_id").val(), <?php echo $stateId ;?>, '#user_state_id');
        $('.user_dob_js').datepicker('option', {
            maxDate: new Date()
        });
    });
</script>
