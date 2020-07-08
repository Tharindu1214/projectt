<?php
require_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

class AffiliateController extends AffiliateBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';

        $get_twitter_url = $_SESSION["TWITTER_URL"]=CommonHelper::generateFullUrl('Affiliate', 'twitterCallback');

        try {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"));
            $request_token = $twitteroauth->oauth('oauth/request_token', array('oauth_callback' => $get_twitter_url));
            $_SESSION['oauth_token'] = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            $twitterUrl = $twitteroauth->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            $this->set('twitterUrl', $twitterUrl);
        } catch (\Exception $e) {
            $this->set('twitterUrl', false);
        }

        $usrObj = new User();
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $userInfo = User::getAttributesById($loggedUserId, array('user_fb_access_token', 'user_referral_code'));

        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);

        $fbAccessToken = '';
        $fbLoginUrl = '';

        $redirectUrl = CommonHelper::generateFullUrl('Affiliate', 'getFbToken', array(), '', false);
        $fbLoginUrl = $fb->getLoginUrl($redirectUrl);
        if ($userInfo['user_fb_access_token']!='') {
            $fbAccessToken = $userInfo['user_fb_access_token'];
        }

        //$_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';

        /*
        * Referred User Listing
        */
        $srch = $usrObj->referredByAffilates($loggedUserId);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srch->getResultSet();
        $user_listing = FatApp::getDb()->fetchAll($rs);

        /*
        * Transactions Listing
        */
        $srch = Transactions::getUserTransactionsObj($loggedUserId);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srch->getResultSet();
        $transactions = FatApp::getDb()->fetchAll($rs, 'utxn_id');

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($loggedUserId, date('Y-m-d'));
        $this->set('txnsSummary', $txnsSummary);

        $sharingFrm = $this->getSharingForm($this->siteLangId);
        $affiliateTrackingUrl = CommonHelper::affiliateReferralTrackingUrl($userInfo['user_referral_code']);
        $this->set('affiliateTrackingUrl', $affiliateTrackingUrl);
        $this->set('sharingFrm', $sharingFrm);
        $this->set('fbLoginUrl', $fbLoginUrl);
        $this->set('fbAccessToken', $fbAccessToken);
        $this->set('user_listing', $user_listing);
        $this->set('transactions', $transactions);
        $this->set('txnStatusArr', Transactions::getStatusArr($this->siteLangId));
        $this->set('affiliateTrackingUrl', $affiliateTrackingUrl);
        $this->set('userBalance', User::getUserBalance($loggedUserId));
        $this->set('userRevenue', User::getAffiliateUserRevenue($loggedUserId));
        $this->set('todayRevenue', User::getAffiliateUserRevenue($loggedUserId, date('Y-m-d H:i:s')));
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function paymentInfoForm()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $frm = $this->getPaymentInfoForm($this->siteLangId);
        /* $userExtraData = User::getUserExtraData( $loggedUserId, array(
        'uextra_tax_id',
        'uextra_payment_method',
        'uextra_cheque_payee_name',
        'uextra_bank_name',
        'uextra_bank_branch_number',
        'uextra_bank_swift_code',
        'uextra_bank_account_name',
        'uextra_bank_account_number',
        'uextra_paypal_email_id') ); */
        $userExtraData = User::getUserExtraData(
            $loggedUserId,
            array(
            'uextra_tax_id',
            'uextra_payment_method',
            'uextra_cheque_payee_name',
            'uextra_paypal_email_id')
        );
        if($userExtraData['uextra_payment_method'] == 0){
            $userExtraData['uextra_payment_method'] = User::AFFILIATE_PAYMENT_METHOD_CHEQUE;
        }
        $userObj = new User($loggedUserId);
        $userBankInfo = $userObj->getUserBankInfo();
        $frmData = $userExtraData;
        if (is_array($userBankInfo) && !empty($userBankInfo)) {
            $frmData = array_merge($frmData, $userBankInfo);
        }
        $frm->fill($frmData);
        $this->set('userExtraData', $frmData);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setUpPaymentInfo()
    {
        $frm = $this->getPaymentInfoForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('Affiliate'));
        }

        $loggedUserId = UserAuthentication::getLoggedUserId();
        $userObj = new User($loggedUserId);

        /* saving user extras[ */
        $dataToSave = array(
        'uextra_user_id'    =>    $loggedUserId,
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
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'ProfileInfo'));
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
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'ProfileInfo'));
        }
        /* ] */

        $this->set('msg', Labels::getLabel('MSG_Payment_details_saved_successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getFbToken()
    {
        $userId = UserAuthentication::getLoggedUserId();
        if (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'])) {
            $redirectUrl = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'];
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user']);
        } else {
            $redirectUrl = CommonHelper::generateUrl('Affiliate', 'Sharing');
        }

        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';

        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);
        $fbObj = $fb->getInstance();

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                Message::addErrorMessage($helper->getErrorDescription());
            //Message::addErrorMessage($helper->getErrorReason());
            } else {
                Message::addErrorMessage(Labels::getLabel('Msg_Bad_Request', $this->siteLangId));
            }
        } else {
            // The OAuth 2.0 client handler helps us manage access tokens
            $oAuth2Client = $fbObj->getOAuth2Client();

            if (! $accessToken->isLongLived()) {
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    Message::addErrorMessage($helper->getMessage());
                    FatApp::redirectUser($redirectUrl);
                }
            }

            $fbAccessToken = $accessToken->getValue();
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_code']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_access_token']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_user_id']);

            $userObj = new User($userId);
            $userData = array('user_fb_access_token'=>$fbAccessToken);
            $userObj->assignValues($userData);
            if (!$userObj->save()) {
                Message::addErrorMessage(Labels::getLabel("MSG_Token_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            }
        }
        FatApp::redirectUser($redirectUrl);
    }

    public function twitterCallback()
    {
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';
        $get = FatApp::getQueryStringData();

        if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            try {
                $access_token = $twitteroauth->oauth("oauth/access_token", ["oauth_verifier" => $get['oauth_verifier']]);
            } catch (exception $e) {
                $this->set('errors', $e->getMessage());
                $this->_template->render(false, false, 'buyer/twitter-response.php');
                return;
            }

            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $access_token['oauth_token'], $access_token['oauth_token_secret']);

            $info = $twitteroauth->get('account/verify_credentials', array("include_entities" => false));

            $anchor_tag = CommonHelper::affiliateReferralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code'));

            $urlapi = "http://tinyurl.com/api-create.php?url=".$anchor_tag;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $shorturl = curl_exec($ch);
            curl_close($ch);
            $anchor_length=strlen($shorturl);

            //$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
            $message = substr($shorturl." ".sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_TWITTER_POST_TITLE".$this->siteLangId), FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId)), 0, 134-$anchor_length);

            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $this->siteLangId);
            $error = false;
            $postMedia = false;
            if (!empty($file_row)) {
                $image_path = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
                $image_path = CONF_UPLOADS_PATH.$image_path;
                if (filesize($image_path) <= (5*1000000)) { /*Max 5mb size image can be uploaded by Twitter*/
                    $handle = fopen($image_path, 'rb');
                    $image = fread($handle, filesize($image_path));
                    fclose($handle);
                    $twitteroauth->setTimeouts(60, 30);
                    try {
                        $result = $twitteroauth->upload('media/upload', array('media' => $image_path));
                        if ($twitteroauth->getLastHttpCode() == 200) {
                            $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId), 'status' => $message, 'media_ids' => $result->media_id_string);
                            try {
                                $post = $twitteroauth->post('statuses/update', $parameters);
                                $postMedia = true;
                            } catch (exception $e) {
                                $error = $e->getMessage();
                            }
                        }
                    } catch (exception $e) {
                        ;
                        $error = $e->getMessage();
                    }
                }
            }

            if (!$postMedia) {
                $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId), 'status' => $message);
                try {
                    $post = $twitteroauth->post('statuses/update', $parameters, false);
                } catch (exception $e) {
                    $error = $e->getMessage();
                }
            }
            $this->set('errors', isset($post->errors) ? $post->errors : $error);
            $this->_template->render(false, false, 'affiliate/twitter-response.php');
        }
    }

    public function sharing()
    {
        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitter/twitteroauth.php';
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $userInfo = User::getAttributesById($loggedUserId, array('user_fb_access_token', 'user_referral_code'));
        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);

        $fbAccessToken = '';
        $fbLoginUrl = '';

        $redirectUrl = CommonHelper::generateFullUrl('Affiliate', 'getFbToken', array(), '', false);
        $fbLoginUrl = $fb->getLoginUrl($redirectUrl);
        if ($userInfo['user_fb_access_token']!='') {
            $fbAccessToken = $userInfo['user_fb_access_token'];
        }

        $sharingFrm = $this->getSharingForm($this->siteLangId);
        $affiliateTrackingUrl = CommonHelper::affiliateReferralTrackingUrl($userInfo['user_referral_code']);
        $this->set('affiliateTrackingUrl', $affiliateTrackingUrl);
        $this->set('sharingFrm', $sharingFrm);
        $this->set('fbLoginUrl', $fbLoginUrl);
        $this->set('fbAccessToken', $fbAccessToken);
        $this->_template->render(true, true);
    }

    public function setUpMailAffiliateSharing()
    {
        $sharingFrm = $this->getSharingForm($this->siteLangId);
        $post = $sharingFrm->getFormDataFromArray(FatApp::getPostedData());

        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $error = '';
        FatUtility::validateMultipleEmails($post["email"], $error);
        if ($error != '') {
            Message::addErrorMessage($error);
            FatUtility::dieWithError(Message::getHtml());
        }
        $emailsArr = CommonHelper::multipleExplode(array(",",";","\t","\n"), trim($post["email"], ","));
        $emailsArr = array_unique($emailsArr);
        if (count($emailsArr) && !empty($emailsArr)) {
            $personalMessage = empty($post['message'])?"":"<b>".Labels::getLabel('Lbl_Personal_Message_From_Affiliate', $this->siteLangId).":</b> ".nl2br($post['message']);
            $emailNotificationObj = new EmailHandler();
            foreach ($emailsArr as $email_id) {
                $email_id = trim($email_id);
                if (!CommonHelper::isValidEmail($email_id)) {
                    continue;
                }

                /* email notification handling[ */
                $emailNotificationObj = new EmailHandler();
                if (!$emailNotificationObj->sendAffiliateMailShare(UserAuthentication::getLoggedUserId(), $email_id, $personalMessage, $this->siteLangId)) {
                    Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
                    CommonHelper::redirectUserReferer();
                }
                /* ] */
            }
        }

        $this->set('msg', Labels::getLabel('MSG_invitation_emails_sent_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addressInfo()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $siteLangId = $this->siteLangId;
        $userExtraData = User::getUserExtraData($loggedUserId, array('uextra_company_name', 'uextra_website'));
        $srch = User::getSearchObject();
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'u.user_country_id = c.country_id', 'c');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c.country_id = c_l.countrylang_country_id AND countrylang_lang_id = '.$siteLangId, 'c_l');
        $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'u.user_state_id = s.state_id', 's');
        $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.state_id = s_l.statelang_state_id AND statelang_lang_id = '.$siteLangId, 's_l');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('user_address1', 'user_address2', 'user_zip', 'user_city', 'IFNULL(country_name, country_code) as country_name', 'IFNULL(state_name, state_identifier) as state_name'));
        $srch->addCondition('user_id', '=', $loggedUserId);
        $rs = $srch->getResultSet();
        $userData = FatApp::getDb()->fetch($rs);

        $userExtraData = (!empty($userExtraData)) ? $userExtraData : array('uextra_company_name' => '', 'uextra_website' => '');
        $userData = array_merge($userData, $userExtraData);

        $this->set('userData', $userData);
        $this->_template->render(false, false);
    }

    private function getPaymentInfoForm($siteLangId)
    {
        $siteLangId = FatUtility::int($siteLangId);
        $frm = new Form('frmPaymentInfoForm');

        $frm->addRadioButtons(Labels::getLabel('LBL_Payment_Method', $siteLangId), 'uextra_payment_method', User::getAffiliatePaymentMethodArr($siteLangId), User::AFFILIATE_PAYMENT_METHOD_CHEQUE, array('class' => 'links--inline'));
        $frm->addTextBox(Labels::getLabel('LBL_Tax_Id', $siteLangId), 'uextra_tax_id');
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
        return $frm;
    }

    private function getSharingForm($siteLangId)
    {
        $siteLangId = FatUtility::int($siteLangId);
        $frm = new Form('frmAffiliateSharingForm');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Friends_Email', $siteLangId), 'email');
        $str = Labels::getLabel('LBL_Use_commas_separate_emails', $siteLangId);
        $str .= ", ".Labels::getLabel("LBL_Do_not_use_space_and_comma_at_end_of_string", $siteLangId);
        $fld->htmlAfterField =' <small>(' . $str . ')</small>';
        $fld->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('L_Personal_Message', $siteLangId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('L_Invite_Your_Friends', $siteLangId));
        return $frm;
    }

    public function referredByMe()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $usrObj = new User();
        $srch = $usrObj->referredByAffilates($loggedUserId);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srch->getResultSet();
        $user_listing = FatApp::getDb()->fetchAll($rs);
        $frmSearch = $this->getUserSearchForm();

        $this->set('user_listing', $user_listing);
        $this->set('frmSearch', $frmSearch);
        $this->set('user_listing', $user_listing);
        $this->_template->render(true, true);
    }

    private function getUserSearchForm()
    {
        $frm = new Form('frmUserSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Name_Or_Email', $this->siteLangId), 'keyword', '', array('id'=>'keyword','autocomplete'=>'off'));
        //$keyword->setFieldTagAttribute('onKeyUp','usersAutocomplete(this)');

        $arr_options = array('-1'=>Labels::getLabel('LBL_Does_Not_Matter', $this->siteLangId))+applicationConstants::getActiveInactiveArr($this->siteLangId);
        $arr_options1 = array('-1'=>Labels::getLabel('LBL_Does_Not_Matter', $this->siteLangId))+applicationConstants::getYesNoArr($this->siteLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Active_Users', $this->siteLangId), 'user_active', $arr_options, -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Email_Verified', $this->siteLangId), 'user_verified', $arr_options1, -1, array(), '');

        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'user_id', '');
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick'=>'clearSearch();'));

        return $frm;
    }

    public function userSearch()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getUserSearchForm();

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $userObj = new User();
        $srch = $userObj->referredByAffilates($loggedUserId);

        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, -1);
        if ($user_id > 0) {
            $srch->addCondition('user_id', '=', $user_id);
        } else {
            $keyword = FatApp::getPostedData('keyword', null, '');
            if (!empty($keyword)) {
                $cond = $srch->addCondition('uc.credential_username', 'like', '%'.$keyword.'%');
                $cond->attachCondition('uc.credential_email', 'like', '%'.$keyword.'%', 'OR');
                $cond->attachCondition('u.user_name', 'like', '%'. $keyword .'%');
            }
        }

        $user_active = FatApp::getPostedData('user_active', FatUtility::VAR_INT, -1);
        if ($user_active > -1) {
            $srch->addCondition('uc.credential_active', '=', $user_active);
        }

        $user_verified = FatApp::getPostedData('user_verified', FatUtility::VAR_INT, -1);
        if ($user_verified > -1) {
            $srch->addCondition('uc.credential_verified', '=', $user_verified);
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'user_id');
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function autoCompleteJson()
    {
        $post = FatApp::getPostedData();
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $userObj = new User();
        $srch = $userObj->referredByAffilates($loggedUserId);
        $srch->addOrder('user_name', 'ASC');
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('uc.credential_username', 'like', '%'.$keyword.'%');
            $cond->attachCondition('uc.credential_email', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%'. $keyword .'%');
        }

        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $users = $db->fetchAll($rs, 'user_id');

        $json = array();
        foreach ($users as $key => $user) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($user['user_name'], ENT_QUOTES, 'UTF-8')),
            'username'      => strip_tags(html_entity_decode($user['credential_username'], ENT_QUOTES, 'UTF-8')),
            'credential_email'      => strip_tags(html_entity_decode($user['credential_email'], ENT_QUOTES, 'UTF-8')),
            );
        }

        die(json_encode($json));
    }
}
