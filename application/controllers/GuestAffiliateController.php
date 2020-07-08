<?php
class GuestAffiliateController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->_template->addCss('css/seller.css');
    }

    public function index()
    {
        if (UserAuthentication::isUserLogged() && (User::isAffiliate()  || User::isSigningUpAffiliate())) {
            FatApp::redirectUser(CommonHelper::generateUrl('affiliate'));
        }

        $extraPageObj = new Extrapage();
        $bannerSlogan = $extraPageObj->getContentByPageType(Extrapage::AFFILIATE_BANNER_SLOGAN, $this->siteLangId);

        /* UserAuthentication::setSessionAffiliateRegistering( array('user_id' => 99, 'affiliate_register_step_number' => 3) ); */

        $affiliate_register_step_number = (UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number')) ? UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number') : 1;
        $this->set('affiliate_register_step_number', $affiliate_register_step_number);
        $this->set('bannerSlogan', $bannerSlogan);
        $this->_template->render();
    }

    public function setupAffiliateRegister()
    {
        $affiliate_register_step_number = (UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number')) ? UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number') : 1;
        $frm = $this->getAffiliateRegistrationForm($affiliate_register_step_number);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post['user_state_id'] = FatApp::getPostedData('user_state_id', FatUtility::VAR_INT, 0);
        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
        }
        $user_id = FatUtility::int(UserAuthentication::getSessionAffiliateByKey('user_id'));
        $userObj = new User($user_id);

        switch ($affiliate_register_step_number) {
        case UserAuthentication::AFFILIATE_REG_STEP1:
            $post['user_email'] = FatApp::getPostedData('user_email', FatUtility::VAR_STRING, '');
            $post['user_password'] = FatApp::getPostedData('user_password', FatUtility::VAR_STRING, '');
            $post['user_username'] = FatApp::getPostedData('user_username', FatUtility::VAR_STRING, '');

            if (!ValidateElement::username($post['user_username'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_USERNAME_MUST_BE_THREE_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }

            if (!ValidateElement::password($post['user_password'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }

            $db = FatApp::getDb();
            $db->startTransaction();

            $post['user_is_buyer'] = 0;
            $post['user_is_supplier'] = 0;
            $post['user_is_affiliate'] = 1;
            $post['user_registered_initially_for'] = User::USER_TYPE_AFFILIATE;
            $post['user_preferred_dashboard'] = User::USER_AFFILIATE_DASHBOARD;
            $post['user_affiliate_commission'] = FatApp::getConfig('CONF_AFFILIATE_SIGNUP_COMMISSION', FatUtility::VAR_FLOAT, 0);

            $userObj->assignValues($post);
            if (!$userObj->save()) {
                $db->rollbackTransaction();
                Message::addErrorMessage(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }

            $user_id = $userObj->getMainTableRecordId();

            $active = FatApp::getConfig("CONF_AFFILIATES_REQUIRES_APPROVAL", FatUtility::VAR_INT, 1) ? 0 : 1;
            $verify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_AFFILIATE_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;

            if (!$userObj->setLoginCredentials($post['user_username'], $post['user_email'], $post['user_password'], $active, $verify)) {
                Message::addErrorMessage(Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
                $db->rollbackTransaction();
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }

            if (FatApp::getConfig('CONF_NOTIFY_ADMIN_AFFILIATE_REGISTRATION', FatUtility::VAR_INT, 1) == 1) {
                if (!$this->notifyAdminRegistration($userObj, $post)) {
                    Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                    $db->rollbackTransaction();
                    if (FatUtility::isAjaxCall()) {
                        FatUtility::dieWithError(Message::getHtml());
                    }
                    FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
                }
            }

            if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_AFFILIATE_REGISTRATION', FatUtility::VAR_INT, 1)) {
                if (!$this->userEmailVerification($userObj, $post)) {
                    Message::addErrorMessage(Labels::getLabel("MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                    $db->rollbackTransaction();
                    if (FatUtility::isAjaxCall()) {
                        FatUtility::dieWithError(Message::getHtml());
                    }
                    FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
                }
            } else {
                if (FatApp::getConfig('CONF_WELCOME_EMAIL_AFFILIATE_REGISTRATION', FatUtility::VAR_INT, 1) == 1) {
                    if (!$this->userWelcomeEmailRegistration($userObj, $post)) {
                        Message::addErrorMessage(Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                        $db->rollbackTransaction();
                        if (FatUtility::isAjaxCall()) {
                            FatUtility::dieWithError(Message::getHtml());
                        }
                        FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
                    }
                }
            }

            $db->commitTransaction();

            UserAuthentication::setSessionAffiliateRegistering(
                array(
                'user_id' => $user_id,
                'affiliate_register_step_number' => UserAuthentication::AFFILIATE_REG_STEP2 )
            );
            $msg = UserAuthentication::getAffiliateRegisterationStepArr($this->siteLangId)[UserAuthentication::AFFILIATE_REG_STEP1];

            break;

        case UserAuthentication::AFFILIATE_REG_STEP2:
            /* saving user extras[ */
            $dataToSave = array(
            'uextra_user_id'        =>    $user_id,
            'uextra_company_name'    =>    $post['uextra_company_name'],
            'uextra_website'        =>    CommonHelper::processUrlString($post['uextra_website'])
            );
            $dataToUpdateOnDuplicate = $dataToSave;
            unset($dataToUpdateOnDuplicate['uextra_user_id']);
            if (!FatApp::getDb()->insertFromArray(User::DB_TBL_USR_EXTRAS, $dataToSave, false, array(), $dataToUpdateOnDuplicate)) {
                Message::addErrorMessage(Labels::getLabel("LBL_Details_could_not_be_saved!", $this->siteLangId));
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }
            /* ] */

            /* Saving User Adrress[ */
            $dataToSave = array(
            'user_address1'    => $post['user_address1'],
            'user_address2'    =>    $post['user_address2'],
            'user_country_id'    =>    $post['user_country_id'],
            'user_state_id'    =>    $post['user_state_id'],
            'user_city'        =>    $post['user_city'],
            'user_zip'        =>    $post['user_zip']
            );
            $userObj->assignValues($dataToSave);
            if (!$userObj->save()) {
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            /* $ua_id = FatUtility::int(UserAuthentication::getSessionAffiliateByKey('ua_id'));
            $addressObj = new UserAddress( $ua_id );
            $dataToSave = array(
            'ua_user_id'    =>    $user_id,
            'user_address1'    =>    $post['user_address1'],
            'user_address2'    =>    $post['user_address2'],
            'user_country_id'    =>    $post['user_country_id'],
            'user_state_id'    =>    $post['user_state_id'],
            'user_city'        =>    $post['user_city'],
            'user_zip'        =>    $post['user_zip']
            );
            $addressObj->assignValues($dataToSave, true);
            if (!$addressObj->save()) {
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
            }
            $ua_id = $addressObj->getMainTableRecordId(); */
            /* ] */

            UserAuthentication::setSessionAffiliateRegistering(array( 'affiliate_register_step_number' => UserAuthentication::AFFILIATE_REG_STEP3));
            $msg = UserAuthentication::getAffiliateRegisterationStepArr($this->siteLangId)[UserAuthentication::AFFILIATE_REG_STEP2];
            break;

        case UserAuthentication::AFFILIATE_REG_STEP3:
            /* saving user extras[ */
            $dataToSave = array(
            'uextra_user_id'    =>    $user_id,
            'uextra_tax_id'        =>    $post['uextra_tax_id'],
            'uextra_payment_method'    =>    $post['uextra_payment_method'],
            'uextra_cheque_payee_name'=>$post['uextra_cheque_payee_name'],
            'uextra_paypal_email_id'=>    $post['uextra_paypal_email_id'],
            );
            $dataToUpdateOnDuplicate = $dataToSave;
            unset($dataToUpdateOnDuplicate['uextra_user_id']);
            if (!FatApp::getDb()->insertFromArray(User::DB_TBL_USR_EXTRAS, $dataToSave, false, array(), $dataToUpdateOnDuplicate)) {
                Message::addErrorMessage(Labels::getLabel("LBL_Details_could_not_be_saved!", $this->siteLangId));
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }
            /* ] */

            /* saving user bank details[ */
            $bankInfoData = array(
            'ub_bank_name'        =>    $post['ub_bank_name'],
            'ub_account_holder_name'    =>    $post['ub_account_holder_name'],
            'ub_account_number'=>    $post['ub_account_number'],
            'ub_ifsc_swift_code'    => $post['ub_ifsc_swift_code'],
            'ub_bank_address'        => $post['ub_bank_address'],
            );
            if (!$userObj->updateBankInfo($bankInfoData)) {
                Message::addErrorMessage($userObj->getError());
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('GuestAffiliate'));
            }
            /* ] */

            UserAuthentication::setSessionAffiliateRegistering(array( 'affiliate_register_step_number' => UserAuthentication::AFFILIATE_REG_STEP4));
            $msg = UserAuthentication::getAffiliateRegisterationStepArr($this->siteLangId)[UserAuthentication::AFFILIATE_REG_STEP3];
            break;

        case UserAuthentication::AFFILIATE_REG_STEP4:
            break;
        }


        if (FatUtility::isAjaxCall()) {
            $msg = str_replace(" ", "_", $msg);
            $this->set('msg', Labels::getLabel('LBL_'.$msg.'_Saved', $this->siteLangId));
            //$this->set( 'redirectUrl', $redirectUrl );
            $this->set('affiliate_register_step_number', UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number'));
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }
        FatApp::redirectUser($redirectUrl);
    }

    public function affiliateRegistrationStep($registeration_step_number = UserAuthentication::AFFILIATE_REG_STEP1)
    {
        $registeration_step_number = FatUtility::int($registeration_step_number);
        $registerForm = $this->getAffiliateRegistrationForm($registeration_step_number);

        $affiliate_register_step_number = (UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number')) ? UserAuthentication::getSessionAffiliateByKey('affiliate_register_step_number') : UserAuthentication::AFFILIATE_REG_STEP1;

        //$ua_id = FatUtility::int(UserAuthentication::getSessionAffiliateByKey('ua_id'));
        $user_id = FatUtility::int(UserAuthentication::getSessionAffiliateByKey('user_id'));

        switch ($affiliate_register_step_number) {
        case UserAuthentication::AFFILIATE_REG_STEP1:
            $termsAndConditionsLinkHref = 'javascript:void(0)';
            $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_AFFILIATE_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
            $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
            if (!empty($cpage) && is_array($cpage)) {
                $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
            }
            $this->set('termsAndConditionsLinkHref', $termsAndConditionsLinkHref);
            break;

        case UserAuthentication::AFFILIATE_REG_STEP2:
            $frmData = array();
            $userExtraData = User::getUserExtraData($user_id, array('uextra_company_name', 'uextra_website'));
            $userExtraData = (empty($userExtraData)) ? array(): $userExtraData;
            $userData = User::getAttributesById($user_id, array( 'user_address1', 'user_address2', 'user_country_id', 'user_state_id', 'user_city', 'user_zip' ));
            $this->set('stateId', $userData['user_state_id']);
            $frmData = array_merge($userExtraData, $userData);
            $registerForm->fill($frmData);
            break;

        case UserAuthentication::AFFILIATE_REG_STEP3:
            $userObj = new User($user_id);
            $userBankInfoData = $userObj->getUserBankInfo();
            $userExtraData = User::getUserExtraData($user_id, array('uextra_cheque_payee_name', 'uextra_paypal_email_id', 'uextra_payment_method'));
            $userExtraData = (empty($userExtraData)) ? array(): $userExtraData;
            $userBankInfoData = (empty($userBankInfoData)) ? array(): $userBankInfoData;
            $uextra_payment_method = (isset($userExtraData['uextra_payment_method']) && $userExtraData['uextra_payment_method'] >0) ? $userExtraData['uextra_payment_method'] : User::AFFILIATE_PAYMENT_METHOD_CHEQUE;
            $frmData = array_merge($userExtraData, $userBankInfoData, array('uextra_payment_method' => $uextra_payment_method));
            $registerForm->fill($frmData);
            $this->set('userExtraData', $userExtraData);
            break;

        case UserAuthentication::AFFILIATE_REG_STEP4:
            $successMsg = Labels::getLabel('LBL_You_have_been_registered_successfully.', $this->siteLangId);
            if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_AFFILIATE_REGISTRATION', FatUtility::VAR_INT, 1)) {
                $successMsg .= Labels::getLabel('LBL_A_verification_link_has_been_sent_to_your_Email_Address._Please_verify_your_email_and_access_my_account_area.', $this->siteLangId);
            }
            $this->clearAffiliateSession();
            $this->set('successMsg', $successMsg);
            break;
        }

        $this->set('affiliate_register_step_number', $affiliate_register_step_number);
        $this->set('registerStepsArr', UserAuthentication::getAffiliateRegisterationStepArr($this->siteLangId));
        $this->set('registerForm', $registerForm);
        $this->_template->render(false, false);
    }

    private function notifyAdminRegistration($userObj, $data)
    {
        return $userObj->notifyAdminRegistration($data, $this->siteLangId);
    }

    private function userWelcomeEmailRegistration($userObj, $data)
    {
        $link = CommonHelper::generateFullUrl('GuestAffiliate');
        $data = array(
            'user_name' => $data['user_name'],
        'user_email' => $data['user_email'],
        'link' => $link,
        );
        $email = new EmailHandler();
        if (!$email->sendWelcomeEmail($this->siteLangId, $data)) {
            Message::addMessage(Labels::getLabel("MSG_ERROR_IN_SENDING_WELCOME_EMAIL", $this->siteLangId));
            return false;
        }
        return true;
    }

    private function getAffiliateRegistrationForm($registeration_step_number = UserAuthentication::AFFILIATE_REG_STEP1)
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmAffiliateRegister');

        switch ($registeration_step_number) {
        case UserAuthentication::AFFILIATE_REG_STEP1:

            $frm->addHiddenField('', 'user_id', 0, array('id'=>'user_id'));

            $fld = $frm->addTextBox(Labels::getLabel('LBL_USERNAME', $siteLangId), 'user_username');
            $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
            $fld->requirements()->setRequired();
            $fld->requirements()->setUsername();

            $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $siteLangId), 'user_email');
            $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');

            $frm->addRequiredField(Labels::getLabel('LBL_NAME', $siteLangId), 'user_name');

            $phoneFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $siteLangId), 'user_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
            $phoneFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);

            $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD', $siteLangId), 'user_password');
            $fld->requirements()->setRequired();
            $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
            $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $siteLangId));

            $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD', $siteLangId), 'password1');
            $fld1->requirements()->setRequired();
            $fld1->requirements()->setCompareWith('user_password', 'eq', Labels::getLabel('LBL_PASSWORD', $siteLangId));

            /* $fld->requirements()->setInt(); */

            $frm->addHtml('', 'agree_fld_html_div', '&nbsp;');
            $fld = $frm->addCheckBox('', 'agree', 1);
            $fld->requirements()->setRequired();
            $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Terms_Condition_is_mandatory.', $siteLangId));

            $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Register', $siteLangId));
            $frm->setFormTagAttribute('onsubmit', 'setupAffiliateRegister(this); return(false);');

            break;

        case UserAuthentication::AFFILIATE_REG_STEP2:
            $frm->addTextBox(Labels::getLabel('LBL_Company', $siteLangId), 'uextra_company_name');
            $frm->addTextBox(Labels::getLabel('LBL_Website', $siteLangId), 'uextra_website');

            $frm->addTextBox(Labels::getLabel('LBL_Address_Line1', $siteLangId), 'user_address1')->requirements()->setRequired();
            $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $siteLangId), 'user_address2');

            $countryObj = new Countries();
            $countriesArr = $countryObj->getCountriesArr($siteLangId);
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $siteLangId), 'user_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'), array(), Labels::getLabel('LBL_Select', $siteLangId));
            $fld->requirement->setRequired(true);

            $frm->addSelectBox(Labels::getLabel('LBL_State', $siteLangId), 'user_state_id', array())->requirement->setRequired(true);
            $frm->addTextBox(Labels::getLabel('LBL_City', $this->siteLangId), 'user_city');
            $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $siteLangId), 'user_zip');

            $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Register', $siteLangId));
            $frm->setFormTagAttribute('onsubmit', 'setupAffiliateRegister(this); return(false);');

            break;

        case UserAuthentication::AFFILIATE_REG_STEP3:
            $frm->addTextBox(Labels::getLabel('LBL_Tax_Id', $siteLangId), 'uextra_tax_id');

            $frm->addRadioButtons(Labels::getLabel('LBL_Payment_Method', $siteLangId), 'uextra_payment_method', User::getAffiliatePaymentMethodArr($siteLangId), User::AFFILIATE_PAYMENT_METHOD_CHEQUE, array('class' => 'links--inline'));

            $frm->addTextBox(Labels::getLabel('LBL_Cheque_Payee_Name', $siteLangId), 'uextra_cheque_payee_name');

            $frm->addTextBox(Labels::getLabel('LBL_Bank_Name', $siteLangId), 'ub_bank_name');
            $frm->addTextBox(Labels::getLabel('LBL_Account_Holder_Name', $siteLangId), 'ub_account_holder_name');
            $frm->addTextBox(Labels::getLabel('LBL_Bank_Account_Number', $siteLangId), 'ub_account_number');
            $frm->addTextBox(Labels::getLabel('LBL_Swift_Code', $siteLangId), 'ub_ifsc_swift_code');
            $frm->addTextArea(Labels::getLabel('LBL_Bank_Address', $siteLangId), 'ub_bank_address');
            $fld = $frm->addTextBox(Labels::getLabel('LBL_PayPal_Email_Account', $siteLangId), 'uextra_paypal_email_id');
            $fld->requirements()->setEmail();

            $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Register', $siteLangId));
            $frm->setFormTagAttribute('onsubmit', 'setupAffiliateRegister(this); return(false);');

            break;

        case UserAuthentication::AFFILIATE_REG_STEP4:
            $frm->addHtml('', 'affiliate_success_html', '');
            break;
        }
        return $frm;
    }

    private function userEmailVerification($userObj, $data)
    {
        $verificationCode = $userObj->prepareUserVerificationCode();

        $link = CommonHelper::generateFullUrl('GuestUser', 'userCheckEmailVerification', array('verify'=>$verificationCode));
        $data = array(
            'user_name' => $data['user_name'],
            'link' => $link,
        'user_email' => $data['user_email'],
        );

        $email = new EmailHandler();

        if (!$email->sendSignupVerificationLink($this->siteLangId, $data)) {
            Message::addMessage(Labels::getLabel("MSG_ERROR_IN_SENDING_VERFICATION_EMAIL", $this->siteLangId));
            return false;
        }

        return true;
    }

    private function clearAffiliateSession()
    {
        unset($_SESSION[UserAuthentication::AFFILIATE_SESSION_ELEMENT_NAME]);
    }
}
