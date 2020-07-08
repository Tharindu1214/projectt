<?php
class GuestAdvertiserController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->_template->addCss('css/seller.css');
    }

    public function account()
    {
        if (UserAuthentication::isUserLogged() && (User::isAdvertiser() || User::isSigningUpAdvertiser())) {
            FatApp::redirectUser(CommonHelper::generateUrl('advertiser'));
        }
        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_are_already_logged_in._Please_logout_and_register_for_advertiser.', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }

        $obj = new Extrapage();
        $slogan = $obj->getContentByPageType(Extrapage::ADVERTISER_BANNER_SLOGAN, $this->siteLangId);

        $this->set('slogan', $slogan);
        $this->set('siteLangId', $this->siteLangId);

        $this->_template->render();
    }

    public function form()
    {
        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userId = $this->getRegisteredAdvertiserId();
        if ($userId > 0) {
            $this->companyDetailsForm($userId);
        } else {
            $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
            $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
            if (!empty($cpage) && is_array($cpage)) {
                $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
            } else {
                $termsAndConditionsLinkHref = 'javascript:void(0)';
            }

            $registrationFrm = $this->getAdvertiserRegistrationForm();
            $this->set('termsAndConditionsLinkHref', $termsAndConditionsLinkHref);
            $this->set('frm', $registrationFrm);
            $this->set('siteLangId', $this->siteLangId);
            $this->_template->render(false, false);
        }
    }

    public function companyDetailsForm()
    {
        $frm = $this->getAdvertiserRegistrationForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $approvalFrm = $this->getCompanyDetailsForm();
        unset($post['btn_submit']);
        $approvalFrm->fill($post);

        $this->set('siteLangId', $this->siteLangId);
        $this->set('approvalFrm', $approvalFrm);
        $this->set('post', $post);
        $this->_template->render(false, false, 'guest-advertiser/company-details-form.php');
    }

    public function validateDetails()
    {
        $post = FatApp::getPostedData();
        if ($post == false) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!ValidateElement::username($post['user_username'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_USERNAME_MUST_BE_THREE_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!ValidateElement::password($post['user_password'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Data_verified', $this->siteLangId));
    }

    public function setupCompanyDetailsForm()
    {
        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getCompanyDetailsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User();
        $db = FatApp::getDb();
        $db->startTransaction();

        $post['user_is_advertiser'] = 1;
        $post['user_registered_initially_for'] = User::USER_TYPE_ADVERTISER;
        $post['user_preferred_dashboard'] = User::USER_ADVERTISER_DASHBOARD;

        $userObj->assignValues($post);

        if (!$userObj->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $active = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1)?0:1;
        $verify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1)?0:1;

        if (!$userObj->setLoginCredentials($post['user_username'], $post['user_email'], $post['user_password'], $active, $verify)) {
            Message::addErrorMessage(Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj->setUpRewardEntry($userObj->getMainTableRecordId(), $this->siteLangId);



        if (FatApp::getConfig('CONF_NOTIFY_ADMIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->notifyAdminRegistration($post, $this->siteLangId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->userEmailVerification($userObj, $post, $this->siteLangId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1)) {
                if (!$userObj->userWelcomeEmailRegistration($userObj, $post, $this->siteLangId)) {
                    Message::addErrorMessage(Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                    $db->rollbackTransaction();
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }

        $db->commitTransaction();
        if ($verify) {
            $this->set('msg', Labels::getLabel("MSG_SUCCESS_USER_SIGNUP_VERIFIED", $this->siteLangId));
        } else {
            $this->set('msg', Labels::getLabel("MSG_SUCCESS_USER_SIGNUP", $this->siteLangId));
        }

        $_SESSION['registered_supplier']['id'] = $userObj->getMainTableRecordId();
        $this->set('userId', $userObj->getMainTableRecordId());

        $this->_template->render(false, false, 'json-success.php');
    }

    /*     public function setupPasswordForm(){
    $userId = $this->getRegisteredAdvertiserId();

    if (UserAuthentication::isUserLogged()) {
    Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in',$this->siteLangId));
    FatUtility::dieJsonError(Message::getHtml());
    }

    $frm = $this->getPasswordForm();
    $post = $frm->getFormDataFromArray(FatApp::getPostedData());
    if ($post == false) {
    Message::addErrorMessage(current($frm->getValidationErrors()));
    FatUtility::dieJsonError( Message::getHtml());
    }

    $userObj = new User();
    $db = FatApp::getDb();
    $db->startTransaction();

    $userObj->assignValues($post);

    if (!$userObj->updateCredInfo($post,$userId)) {
    $db->rollbackTransaction();
    Message::addErrorMessage(Labels::getLabel("MSG_USER_INFO_COULD_NOT_BE_SAVED",$this->siteLangId) . $userObj->getError());
    FatUtility::dieJsonError( Message::getHtml());
    }

    $db->commitTransaction();
    $this->set('msg', Labels::getLabel("MSG_INFORMATION_SUBMITTED",$this->siteLangId));

    $this->set('userId',$userId);

    $this->_template->render(false, false, 'json-success.php');

    } */

    /* public function passwordForm($userId){

    $userId = FatUtility::int($userId);

    if(!$this->isRegisteredSupplierId($userId)){
    Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS",$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml());
    }

    if (UserAuthentication::isUserLogged()) {
    Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in',$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml());
    }

    $userObj = new User($userId);
    $userdata = $userObj->getUserInfo( array('credential_email', 'user_name'),false,false);

    if(false == $userdata){
    unset($_SESSION['registered_supplier']['id']);
    Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS",$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml());
    }

    $srch = $userObj->getUserSupplierRequestsObj();
    $srch->addFld(array('usuprequest_attempts','usuprequest_id'));

    $rs = $srch->getResultSet();
    if(!$rs){
    Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST',$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    $supplierRequest = FatApp::getDb()->fetch($rs);
    $maxAttempts = FatApp::getConfig('CONF_MAX_SUPPLIER_REQUEST_ATTEMPT',FatUtility::VAR_INT,3);

    if($supplierRequest && $supplierRequest['usuprequest_attempts'] > $maxAttempts){
    Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_consumed_max_attempts',$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    $data = array('id'=>$supplierRequest['usuprequest_id']);
    $passwordFrm = $this->getPasswordForm();
    $passwordFrm->fill($data);

    $this->set('siteLangId', $this->siteLangId);
    $this->set('passwordFrm', $passwordFrm);
    $this->_template->render(false,false,'guest-advertiser/password-form.php');
    } */

    public function profileConfirmation($userId)
    {
        $userId = FatUtility::int($userId);

        if (!$this->isRegisteredSupplierId($userId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        $userdata = $userObj->getUserInfo(array('credential_active','credential_verified'), false, false);

        if (false == $userdata) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (/* $userdata['credential_active'] == applicationConstants::ACTIVE &&  */$userdata['credential_verified'] == applicationConstants::YES) {
            $success_message = Labels::getLabel('MSG_SUCCESS_USER_SIGNUP_VERIFIED', $this->siteLangId);
        } else {
            $success_message = Labels::getLabel('MSG_SUCCESS_USER_SIGNUP', $this->siteLangId);
        }

        unset($_SESSION['registered_supplier']['id']);
        $this->set('success_message', $success_message);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function getRegisteredAdvertiserId()
    {
        if (!isset($_SESSION['registered_supplier']['id'])) {
            return false;
        }
        return $_SESSION['registered_supplier']['id'];
    }

    private function isRegisteredSupplierId($userId)
    {
        if (!isset($_SESSION['registered_supplier'])) {
            return false;
        }

        $userId = FatUtility::int($userId);
        if (1 > $userId || $userId != $_SESSION['registered_supplier']['id']) {
            return false;
        }
        return true;
    }

    private function getAdvertiserRegistrationForm()
    {
        $frm = new Form('frm');

        $frm->addHiddenField('', 'user_id', 0, array('id'=>'user_id'));

        $fld = $frm->addTextBox(Labels::getLabel('LBL_USERNAME', $this->siteLangId), 'user_username');
        $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
        $fld->requirements()->setRequired();
        $fld->requirements()->setUsername();

        $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $this->siteLangId), 'user_email');
        $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');

        $frm->addRequiredField(Labels::getLabel('LBL_NAME', $this->siteLangId), 'user_name');

        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_PHONE', $this->siteLangId), 'user_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);

        $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD', $this->siteLangId), 'user_password');
        $fld->requirements()->setRequired();
        $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));

        $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD', $this->siteLangId), 'password1');
        $fld1->requirements()->setRequired();
        $fld1->requirements()->setCompareWith('user_password', 'eq', Labels::getLabel('LBL_PASSWORD', $this->siteLangId));

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_SUBMIT', $this->siteLangId));

        return $frm;
    }

    private function getCompanyDetailsForm()
    {
        $frm = new Form('frmCompanyDetailsForm');
        $frm->addHiddenField('', 'id', 0);
        $frm->setFormTagAttribute("class", "form invalid");
        $frm->addTextBox(Labels::getLabel('LBL_Company', $this->siteLangId), 'user_company', '');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Brief_Profile', $this->siteLangId), 'user_profile_info', '');
        $fld->htmlAfterField ='<br/><small class="text--small">'.Labels::getLabel('MSG_Please_tell_us_something_about_yourself', $this->siteLangId).'</small>';
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Products/services_you_wish_to_advertise?', $this->siteLangId), 'user_products_services', '');
        $frm->addHiddenField('', 'user_name');
        $frm->addHiddenField('', 'user_phone');
        $frm->addHiddenField('', 'user_username');
        $frm->addHiddenField('', 'user_email');
        $frm->addHiddenField('', 'user_password');
        $frm->addHiddenField('', 'password1');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));
        return $frm;
    }

    /* private function getPasswordForm(){
    $frm = new Form('frmPasswordForm');
    $frm->addHiddenField('','id', 0);
    $frm->setFormTagAttribute("class", "form invalid");
    $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD',$this->siteLangId), 'user_password');
    $fld->requirements()->setRequired();
    $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
    $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));

    $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD',$this->siteLangId), 'password1');
    $fld1->requirements()->setRequired();
    $fld1->requirements()->setCompareWith('user_password', 'eq',Labels::getLabel('LBL_PASSWORD',$this->siteLangId));

    $frm->addSubmitButton('', 'btn_submit',Labels::getLabel('LBL_Submit',$this->siteLangId));
    return $frm;
    } */
}
