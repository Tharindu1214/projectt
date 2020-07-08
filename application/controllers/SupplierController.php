<?php
class SupplierController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->_template->addCss('css/seller.css');
    }

    public function index()
    {
        if (UserAuthentication::isUserLogged() && (User::isSeller() || User::isSigningUpForSeller())) {
            FatApp::redirectUser(CommonHelper::generateUrl('seller'));
        }
        if (UserAuthentication::isUserLogged()) {
            if (User::canViewSupplierTab()) {
                FatApp::redirectUser(CommonHelper::generateUrl('account', 'supplierApprovalForm'));
            }
            Message::addErrorMessage(Labels::getLabel('MSG_You_are_already_logged_in._Please_logout_and_register_for_seller.', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        if (!FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) {
            FatApp::redirectUser(CommonHelper::generateUrl('guest-user', 'login-form', array(applicationConstants::YES)));
        }
        $sellerFrm = $this->getSellerForm();
        $obj = new Extrapage();
        $formText = $obj->getContentByPageType(Extrapage::SELLER_PAGE_FORM_TEXT, $this->siteLangId);
        $block1 = $obj->getContentByPageType(Extrapage::SELLER_PAGE_BLOCK1, $this->siteLangId);
        $block2 = $obj->getContentByPageType(Extrapage::SELLER_PAGE_BLOCK2, $this->siteLangId);
        $block3 = $obj->getContentByPageType(Extrapage::SELLER_PAGE_BLOCK3, $this->siteLangId);
        $slogan = $obj->getContentByPageType(Extrapage::SELLER_BANNER_SLOGAN, $this->siteLangId);

        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = '.applicationConstants::ACTIVE.'  and faq_deleted = '.applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::SELLER_PAGE);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $seller_navigation_left = Navigation::getNavigation(Navigations::NAVTYPE_SELLER_LEFT);
        $this->set('seller_navigation_left', $seller_navigation_left);
        $this->set('formText', $formText);
        $this->set('faqCount', $srch->recordCount());
        $this->set('block1', $block1);
        $this->set('block2', $block2);
        $this->set('block3', $block3);
        $this->set('slogan', $slogan);
        $this->set('sellerFrm', $sellerFrm);
        $this->set('faqSearchFrm', $this->getFaqSearchForm());
        $this->_template->render();
    }

    private function getFaqSearchForm()
    {
        $frm = new Form('frmSearchFaqs');
        $frm->addTextbox(Labels::getLabel('LBL_Enter_your_question', $this->siteLangId), 'question');
        $frm->addSubmitButton('', 'btn_submit', '');
        return $frm;
    }

    public function account()
    {
        if (UserAuthentication::isUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        if (!FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) {
            FatApp::redirectUser(CommonHelper::generateUrl('guest-user', 'registration-form'));
        }
        $frm=$this->getSellerForm();
        $postedData = $frm->getFormDataFromArray(FatApp::getPostedData());
        $obj = new Extrapage();
        $slogan = $obj->getContentByPageType(Extrapage::SELLER_BANNER_SLOGAN, $this->siteLangId);
        $this->set('slogan', $slogan);
        $this->set('postedData', $postedData);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render();
    }

    public function form()
    {
        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userId = $this->getRegisteredSupplierId();
        if ($userId > 0) {
            $this->profileActivationForm($userId);
        } else {
            $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
            $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
            if (!empty($cpage) && is_array($cpage)) {
                $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
            } else {
                $termsAndConditionsLinkHref = 'javascript:void(0)';
            }
            $frm = $this->getSellerForm();
            $post = $frm->getFormDataFromArray(FatApp::getPostedData());
            $registrationFrm = $this->getSellerRegistrationForm();
            unset($post['btn_submit']);
            $registrationFrm->fill($post);
            $this->set('termsAndConditionsLinkHref', $termsAndConditionsLinkHref);
            $this->set('frm', $registrationFrm);
            $this->set('siteLangId', $this->siteLangId);
            $this->_template->render(false, false);
        }
    }

    public function register()
    {
        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getSellerRegistrationForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!ValidateElement::username($post['user_username'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_USERNAME_MUST_BE_THREE_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            } else {
                $this->registrationForm();
                return;
            }
        }

        if (!ValidateElement::password($post['user_password'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            } else {
                $this->registrationForm();
                return;
            }
        }

        $userObj = new User();
        $db = FatApp::getDb();
        $db->startTransaction();
        if (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1) && !FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1)) {
            $post['user_is_supplier'] = 1;
            $post['user_is_advertiser'] = 1;
        }
        $post['user_is_buyer'] = 1;

        $post['user_registered_initially_for'] = User::USER_TYPE_SELLER;
        if (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) {
            $post['user_is_buyer'] = 0;
            $post['user_preferred_dashboard'] = User::USER_SELLER_DASHBOARD;
        }

        $userObj->assignValues($post);

        if (!$userObj->save()) {
            Message::addErrorMessage(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            $db->rollbackTransaction();
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

        if (FatApp::getPostedData('user_newsletter_signup')) {
            include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key == '' || $list_id == '') {
                Message::addErrorMessage(Labels::getLabel("LBL_Newsletter_is_not_configured_yet,_Please_contact_admin", $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $MailchimpObj = new Mailchimp($api_key);
            $Mailchimp_ListsObj = new Mailchimp_Lists($MailchimpObj);
            try {
                $subscriber = $Mailchimp_ListsObj->subscribe($list_id, array( 'email' => htmlentities($post['user_email'])));
                /* if ( empty( $subscriber['leid'] ) ) {
                Message::addErrorMessage( Labels::getLabel('MSG_Newsletter_subscription_valid_email', $siteLangId) );
                FatUtility::dieWithError( Message::getHtml() );
                } */
            } catch (Mailchimp_Error $e) {
                /* Message::addErrorMessage( $e->getMessage() );
                FatUtility::dieWithError( Message::getHtml() ); */
            }
        }

        if (FatApp::getConfig('CONF_NOTIFY_ADMIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->notifyAdminRegistration($post, $this->siteLangId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_USER,
        'notification_record_id' => $userObj->getMainTableRecordId(),
        'notification_user_id' => $userObj->getMainTableRecordId(),
        'notification_label_key' => Notification::NEW_SUPPLIER_REGISTERATION_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Message::getHtml());
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

    public function profileActivationForm()
    {
        if (!$userId = $this->getRegisteredSupplierId()) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User($userId);
        $userdata = $userObj->getUserInfo(array('credential_email', 'user_name'), false, false);

        if (false == $userdata) {
            unset($_SESSION['registered_supplier']['id']);
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld(array('usuprequest_attempts','usuprequest_id'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);
        $maxAttempts = FatApp::getConfig('CONF_MAX_SUPPLIER_REQUEST_ATTEMPT', FatUtility::VAR_INT, 3);

        if ($supplierRequest && $supplierRequest['usuprequest_attempts'] > $maxAttempts) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_consumed_max_attempts', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = array('id'=>$supplierRequest['usuprequest_id']);
        $approvalFrm = $this->getSupplierForm();
        $approvalFrm->fill($data);

        $this->set('siteLangId', $this->siteLangId);
        $this->set('approvalFrm', $approvalFrm);
        $this->_template->render(false, false, 'supplier/profile-activation-form.php');
    }

    public function setupSupplierApproval()
    {
        $userId = $this->getRegisteredSupplierId();

        if (!$this->isRegisteredSupplierId($userId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getSupplierForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        $supplier_form_fields = $userObj->getSupplierFormFields($this->siteLangId);

        foreach ($supplier_form_fields as $field) {
            $fieldIdsArr[] = $field['sformfield_id'];
            if ($field['sformfield_required'] && empty($post["sformfield_".$field['sformfield_id']])) {
                $error_messages[]=sprintf(Labels::getLabel('MSG_Label_Required', $this->siteLangId), $field['sformfield_caption']);
            }
        }

        if (!empty($error_messages)) {
            Message::addErrorMessage($error_messages);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $reference_number = $userId.'-'.time();
        $data = array_merge(
            $post,
            array(
            "user_id"=>$userId,
            "reference"=>$reference_number,
            'fieldIdsArr'=>$fieldIdsArr
            )
        );

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$userObj->addSupplierRequestData($data, $this->siteLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('MSG_details_not_saved', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1)) {
            $approval_request = 1;
            $msg = Labels::getLabel('MSG_Your_seller_approval_form_request_sent', $this->siteLangId);
        } else {
            $approval_request = 0;
            $msg = Labels::getLabel('MSG_Your_application_is_approved', $this->siteLangId);
        }

        if (!$userObj->notifyAdminSupplierApproval($userObj, $data, $approval_request, $this->siteLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_SELLER_APPROVAL_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_USER,
        'notification_record_id' => $userObj->getMainTableRecordId(),
        'notification_user_id' => $userId,
        'notification_label_key' => ($approval_request)?Notification::NEW_SUPPLIER_APPROVAL_NOTIFICATION:Notification::NEW_SELLER_APPROVED_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_SELLER_APPROVAL_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('userId', $userId);
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function profileConfirmation()
    {
        if (!$userId = $this->getRegisteredSupplierId()) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
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

        if (/* $userdata['credential_active'] == 1 &&  */$userdata['credential_verified'] == applicationConstants::YES) {
            $success_message = Labels::getLabel('MSG_SUCCESS_SELLER_SIGNUP_VERIFIED', $this->siteLangId);
        } else {
            $success_message = Labels::getLabel('MSG_SUCCESS_SELLER_SIGNUP', $this->siteLangId);
        }

        unset($_SESSION['registered_supplier']['id']);
        $this->set('success_message', $success_message);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function uploadSupplierFormImages()
    {
        $userId = $this->getRegisteredSupplierId();

        if (!$this->isRegisteredSupplierId($userId)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
        }

        if (UserAuthentication::isUserLogged()) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_User_Already_Logged_in', $this->siteLangId));
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            /* Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
        }
        if (!isset($post['field_id']) || FatUtility::int($post['field_id']) == 0) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
        }
        $field_id = $post['field_id'];

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
        }

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile($fileHandlerObj::FILETYPE_SELLER_APPROVAL_FILE, $userId, 0, $field_id);

        if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], $fileHandlerObj::FILETYPE_SELLER_APPROVAL_FILE, $userId, $field_id, $_FILES['file']['name'], -1, false)
        ) {
            /* Message::addErrorMessage($fileHandlerObj->getError()); */
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', /* $_FILES['file']['name'].' '. */Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function faq()
    {
        $cmsPagesToFaq = FatApp::getConfig('conf_cms_pages_to_faq_page');
        $cmsPagesToFaq = unserialize($cmsPagesToFaq);
        if (sizeof($cmsPagesToFaq) > 0 && is_array($cmsPagesToFaq)) {
            $contentPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $contentPageSrch->addCondition('cpage_id', 'in', $cmsPagesToFaq);
            $contentPageSrch->addMultipleFields(array('cpage_id','cpage_identifier','cpage_title'));
            $rs = $contentPageSrch->getResultSet();
            $cpages = FatApp::getDb()->fetchAll($rs);
            $this->set('cpages', $cpages);
        }
        $this->_template->render();
    }

    public function searchFaqs($catId = '')
    {
        $faqMainCat = FatApp::getConfig("CONF_SELLER_PAGE_MAIN_CATEGORY", FatUtility::VAR_STRING, '');
        if (!empty($catId) && $catId > 0) {
            $faqCatId = array( $catId );
        } elseif ($faqMainCat) {
            $faqCatId=array($faqMainCat);
        } else {
            $srchFAQCat = FaqCategory::getSearchObject($this->siteLangId);
            $srchFAQCat->setPageSize(1);
            $srchFAQCat->addFld('faqcat_id');
            $rs = $srchFAQCat->getResultSet();
            $faqCatId = FatApp::getDb()->fetch($rs, 'faqcat_id');
        }

        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = '.applicationConstants::ACTIVE.'  and faq_deleted = '.applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::SELLER_PAGE);
        if (!empty($faqCatId)) {
            $srch->addCondition('faqcat_id', 'IN', $faqCatId);
        }

        $question = FatApp::getPostedData('question', FatUtility::VAR_STRING, '');
        if (!empty($question)) {
            $srchCondition = $srch->addCondition('faq_title', 'like', "%$question%");
            $srch->doNotLimitRecords();
        }
        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();

        if (isset($srchCondition)) {
            $srchCondition->remove();
        }

        $this->set('siteLangId', $this->siteLangId);
        $this->set('faqCatIdArr', $faqCatId);
        $this->set('list', $records);
        $json['html'] = '';//$this->_template->render( false, false,'_partial/no-record-found.php', true );
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'supplier/search-faqs.php', true, false);
        }
        FatUtility::dieJsonSuccess($json);
    }

    public function faqCategoriesPanel()
    {
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id AND faq_active = ' . applicationConstants::ACTIVE . ' AND faq_deleted = '.applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::SELLER_PAGE);
        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();

        $srch->addGroupBy('faqcat_id');
        $srch->addMultipleFields(array('faqcat_name','faqcat_id'));
        $srch->addFld('COUNT(*) AS faq_count');
        if (isset($srchCondition)) {
            $srchCondition->remove();
        }
        $rsCat = $srch->getResultSet();
        $recordsCategories = array();
        if ($rsCat) {
            $recordsCategories = FatApp::getDb()->fetchAll($rsCat);
        }

        $faqMainCat = FatApp::getConfig("CONF_SELLER_PAGE_MAIN_CATEGORY", FatUtility::VAR_STRING, '');

        $this->set('siteLangId', $this->siteLangId);
        $this->set('list', $records);
        $this->set('listCategories', $recordsCategories);
        $this->set('faqMainCat', $faqMainCat);
        $json['html'] = $this->_template->render(false, false, '_partial/no-record-found.php', true, false);
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'supplier/search-faqs.php', true, false);
        }
        $json['categoriesPanelHtml'] = $this->_template->render(false, false, 'custom/faq-categories-panel.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    private function getRegisteredSupplierId()
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

    private function getSellerRegistrationForm()
    {
        $frm = new Form('frmSellerRegistration');

        $frm->addRequiredField(Labels::getLabel('LBL_NAME', $this->siteLangId), 'user_name');

        $frm->addHiddenField('', 'user_id', 0, array('id'=>'user_id'));

        $fld = $frm->addTextBox(Labels::getLabel('LBL_USERNAME', $this->siteLangId), 'user_username');
        $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
        $fld->requirements()->setRequired();
        $fld->requirements()->setUsername();

        $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $this->siteLangId), 'user_email');
        $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');

        $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD', $this->siteLangId), 'user_password');
        $fld->requirements()->setRequired();
        $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));

        $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD', $this->siteLangId), 'password1');
        $fld1->requirements()->setRequired();
        $fld1->requirements()->setCompareWith('user_password', 'eq', Labels::getLabel('LBL_PASSWORD', $this->siteLangId));

        $fld = $frm->addCheckBox('', 'agree', 1);
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Terms_Condition_is_mandatory.', $this->siteLangId));
        if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION')) {
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key != '' || $list_id != '') {
                $frm->addCheckBox(Labels::getLabel('LBL_Newsletter_Signup', $this->siteLangId), 'user_newsletter_signup', 1);
            }
        }
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_SUBMIT', $this->siteLangId));

        return $frm;
    }

    private function getSupplierForm()
    {
        $frm = new Form('frmSupplierForm');
        $frm->addHiddenField('', 'id', 0);

        $userObj = new User();
        $supplier_form_fields = $userObj->getSupplierFormFields($this->siteLangId);

        foreach ($supplier_form_fields as $field) {
            $fieldName = 'sformfield_'.$field['sformfield_id'];

            switch ($field['sformfield_type']) {
                case User::USER_FIELD_TYPE_TEXT:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName);
                    break;

                case User::USER_FIELD_TYPE_TEXTAREA:
                    $fld = $frm->addTextArea($field['sformfield_caption'], $fieldName);
                    break;

                case User::USER_FIELD_TYPE_FILE:
                    $fld1 = $frm->addButton(
                        $field['sformfield_caption'],
                        'button['.$field['sformfield_id'].']',
                        Labels::getLabel('LBL_Upload_File', $this->siteLangId),
                        array('class'=>'fileType-Js btn--sm','id'=>'button-upload'.$field['sformfield_id'],'data-field_id'=>$field['sformfield_id'])
                    );
                    $fld1->htmlAfterField='<span id="input-sformfield'.$field['sformfield_id'].'"></span>';
                    if ($field['sformfield_required'] == 1) {
                        $fld1->captionWrapper = array('<div class="astrick">','</div>');
                    }
                    // $fld = $frm->addHiddenField($field['sformfield_caption'],$fieldName,'',array('id'=>$fieldName));
                    $fld = $frm->addTextBox('', $fieldName, '', array('id'=>$fieldName, 'hidden'=>'hidden', 'title'=>$field['sformfield_caption']));
                    $fld->setRequiredStarWith(Form::FORM_REQUIRED_STAR_WITH_NONE);
                    $fld1->attachField($fld);
                    break;

                case User::USER_FIELD_TYPE_DATE:
                    $fld = $frm->addDateField($field['sformfield_caption'], $fieldName, '', array('readonly'=>'readonly'));
                    break;

                case User::USER_FIELD_TYPE_DATETIME:
                    $fld = $frm->addDateTimeField($field['sformfield_caption'], $fieldName, '', array('readonly'=>'readonly'));
                    break;

                case User::USER_FIELD_TYPE_TIME:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName);
                    $fld->requirement->setRegularExpressionToValidate(ValidateElement::TIME_REGEX);
                    $fld->htmlAfterField = Labels::getLabel('LBL_HH:MM', $this->siteLangId);
                    $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_time_format.', $this->siteLangId));
                    break;

                case User::USER_FIELD_TYPE_PHONE:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName, '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
                    $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
                    break;
            }

            if ($field['sformfield_required'] == 1) {
                $fld->requirements()->setRequired();
            }
            if ($field['sformfield_comment']) {
                $fld->htmlAfterField = '<p class="note">'.$field['sformfield_comment'].'</p>';
            }
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getSellerForm()
    {
        $frm = new Form('frmSeller');
        $frm->addHiddenField('', 'user_id', 0, array('id'=>'user_id'));
        $frm->setFormTagAttribute("class", "form invalid");
        $frm->setFormTagAttribute("action", CommonHelper::generateUrl('supplier', 'account'));
        $fld = $frm->addEmailField(Labels::getLabel('LBL_Your_Email', $this->siteLangId), 'user_email', '');
        $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Your_Name', $this->siteLangId), 'user_name', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_Start_Selling', $this->siteLangId));
        /* $frm->addHtml('', 'htmlNote',Labels::getLabel('Lbl_Need_help_in_getting_PAN/VAT',$this->siteLangId).'?');
        $frm->addHtml('', 'htmlNote','<a href="" class="">'.Labels::getLabel('Lbl_Click_Here',$this->siteLangId).'</a> '.Labels::getLabel('Lbl_to_contact_our_partners_near_your_location',$this->siteLangId));     */
        return $frm;
    }
}
