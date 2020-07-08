<?php
class GuestUserController extends MyAppController
{
    public function loginForm($isRegisterForm = 0)
    {
        /* if(UserAuthentication::doCookieLogin()){
        FatApp::redirectUser(CommonHelper::generateUrl('account'));
        } */
        if (UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }

        if (UserAuthentication::isUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        $loginFrm = $this->getLoginForm();
        $loginData = array(
        'loginFrm'             => $loginFrm,
        'siteLangId'    => $this->siteLangId
        );

        $registerFrm = $this->getRegistrationForm();
        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        if (!empty($cpage) && is_array($cpage)) {
            $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
        } else {
            $termsAndConditionsLinkHref = 'javascript:void(0)';
        }
        $registerdata = array(
        'registerFrm'    =>    $registerFrm,
        'termsAndConditionsLinkHref'    =>    $termsAndConditionsLinkHref,
        'siteLangId'    =>    $this->siteLangId
        );
        $isRegisterForm = FatUtility::int($isRegisterForm);
        $this->set('isRegisterForm', $isRegisterForm);
        $this->set('loginData', $loginData);
        $this->set('registerdata', $registerdata);
        $this->_template->render();
    }

    public function login()
    {
        $authentication = new UserAuthentication();
        $userType = FatApp::getPostedData('userType', FatUtility::VAR_INT, 0);
        if (true ===  MOBILE_APP_API_CALL && 1 > $userType) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        if (!$authentication->login(FatApp::getPostedData('username'), FatApp::getPostedData('password'), $_SERVER['REMOTE_ADDR'], true, false, $this->app_user['temp_user_id'], $userType)) {
            $message = Labels::getLabel($authentication->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->app_user['temp_user_id'] = 0;

        $userId = UserAuthentication::getLoggedUserId();

        if (true ===  MOBILE_APP_API_CALL) {
            $uObj = new User($userId);
            if (!$token = $uObj->setMobileAppToken()) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }

            $userInfo = $uObj->getUserInfo(array('user_name', 'user_id', 'user_phone', 'credential_email'), true, true, true);

            $this->set('token', $token);
            $this->set('userInfo', $userInfo);
            $this->_template->render();
        }

        $rememberme = FatApp::getPostedData('remember_me', FatUtility::VAR_INT, 0);
        if ($rememberme == 1) {
            if (!$this->setUserLoginCookie()) {
                Message::addErrorMessage(Labels::getLabel('MSG_COOKIES_NOT_ADDED', $this->siteLangId));
            }
        }

        setcookie('uc_id', $userId, time()+3600*24*30, CONF_WEBROOT_URL);

        $data = User::getAttributesById($userId, array('user_preferred_dashboard','user_registered_initially_for'));

        $preferredDashboard = 0;
        if ($data != false) {
            $preferredDashboard = $data['user_preferred_dashboard'];
        }

        $redirectUrl = '';

        if (isset($_SESSION['referer_page_url'])) {
            $redirectUrl = $_SESSION['referer_page_url'];
            unset($_SESSION['referer_page_url']);


            $userPreferedDashboardType = ($data['user_preferred_dashboard'])?$data['user_preferred_dashboard']:$data['user_registered_initially_for'];

            switch ($userPreferedDashboardType) {
                case User::USER_TYPE_BUYER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
                    break;
                case User::USER_TYPE_SELLER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
                    break;
                case User::USER_TYPE_AFFILIATE:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
                    break;
                case User::USER_TYPE_ADVERTISER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
                    break;
            }


            /* if( User::isBuyer()  || User::isSigningUpBuyer()){
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
            } else if( User::isSeller() || User::isSigningUpForSeller() ){
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
            } else if( User::isAdvertiser() || User::isSigningUpAdvertiser() ){
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
            } else if( User::isAffiliate()  || User::isSigningUpAffiliate()){
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
            } */
        }
        if ($redirectUrl == '') {
            $redirectUrl = User::getPreferedDashbordRedirectUrl($preferredDashboard);
        }

        if ($redirectUrl == '') {
            $redirectUrl = CommonHelper::generateUrl('Account');
        }
        $this->set('redirectUrl', $redirectUrl);
        $this->set('msg', Labels::getLabel("MSG_LOGIN_SUCCESSFULL", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUserPushNotificationToken()
    {
        $fcmDeviceId = FatApp::getPostedData('deviceToken', FatUtility::VAR_STRING, '');
        if (empty($fcmDeviceId)) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $userId = UserAuthentication::getLoggedUserId();
        $uObj= new User($userId);
        if (!$uObj->setPushNotificationToken($this->appToken, $fcmDeviceId)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $this->set('msg', Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId));
        $this->_template->render();
    }

    public function guestLogin()
    {
        $frm = $this->getGuestUserForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $authentication = new UserAuthentication();
        if (!$authentication->guestLogin(FatApp::getPostedData('user_email'), FatApp::getPostedData('user_name'), $_SERVER['REMOTE_ADDR'])) {
            Message::addErrorMessage(Labels::getLabel($authentication->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $redirectUrl = '';

        if (isset($_SESSION['referer_page_url'])) {
            $redirectUrl = $_SESSION['referer_page_url'];
            unset($_SESSION['referer_page_url']);
        }

        if ($redirectUrl == '') {
            $redirectUrl = User::getPreferedDashbordRedirectUrl(User::USER_BUYER_DASHBOARD);
        }

        if ($redirectUrl == '') {
            $redirectUrl = CommonHelper::generateUrl('Home');
        }

        $this->set('redirectUrl', $redirectUrl);
        $this->set('msg', Labels::getLabel("MSG_GUEST_LOGIN_SUCCESSFULL", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function setUserLoginCookie()
    {
        $userId = UserAuthentication::getLoggedUserAttribute('user_id', true);

        if (null == $userId) {
            return false;
        }

        $token = $this->generateLoginToken();
        $expiry = strtotime("+7 DAYS");

        $values = array(
        'uauth_user_id'=>$userId,
        'uauth_token'=>$token,
        'uauth_expiry'=>date('Y-m-d H:i:s', $expiry),
        'uauth_browser'=>CommonHelper::userAgent(),
        'uauth_last_access'=>date('Y-m-d H:i:s'),
        'uauth_last_ip'=>CommonHelper::getClientIp(),
        );

        if (UserAuthentication::saveLoginToken($values)) {
            $cookieName = UserAuthentication::YOKARTUSER_COOKIE_NAME;
            $cookres = setcookie($cookieName, $token, $expiry, CONF_WEBROOT_URL);
            return true;
        }
        return false;
    }

    private function generateLoginToken()
    {
        return substr(md5(rand(1, 99999) . microtime()), 0, UserAuthentication::TOKEN_LENGTH);
    }

    public function LogInFormPopUp()
    {
        $includeGuestLogin = FatApp::getPostedData('includeGuestLogin', FatUtility::VAR_STRING, false);
        $frm = $this->getLoginForm($includeGuestLogin);
        $data = array(
        'loginFrm'             => $frm,
        'siteLangId'    => $this->siteLangId,
        'includeGuestLogin'    => $includeGuestLogin,
        );
        $this->set('data', $data);
        $this->_template->render(false, false);
    }

    public function form()
    {
        $frm = $this->getGuestUserForm($this->siteLangId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function checkAjaxUserLoggedIn()
    {
        $json = array();
        $json['isUserLogged'] = FatUtility::int(UserAuthentication::isUserLogged());
        if (!$json['isUserLogged']) {
            $json['isUserLogged'] = FatUtility::int(UserAuthentication::isGuestUserLogged());
        }
        die(json_encode($json));
    }

    public function socialMediaLogin($oauthProvider)
    {
        if (isset($oauthProvider)) {
            if ($oauthProvider == 'google') {
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginGoogle'));
            } elseif ($oauthProvider == 'facebook') {
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginFacebook'));
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangid));
            }
        }
        CommonHelper::redirectUserReferer();
    }

    public function loginFacebook()
    {
        $userType = FatApp::getPostedData('type', FatUtility::VAR_INT, 0);
        if (true ===  MOBILE_APP_API_CALL) {
            $accessToken = FatApp::getPostedData('accessToken', FatUtility::VAR_STRING, '');
            if (empty($accessToken) || 1 > $userType) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
            include_once CONF_INSTALLATION_PATH . 'library/facebook/facebook.php';
            $facebook = new Facebook(
                array(
                'appId' => FatApp::getConfig("CONF_FACEBOOK_APP_ID", FatUtility::VAR_STRING, ''),
                'secret' => FatApp::getConfig("CONF_FACEBOOK_APP_SECRET", FatUtility::VAR_STRING, ''),
                )
            );
            $facebook->setAccessToken($accessToken);
            $user = $facebook->getUser();
            if (!$user) {
                $message = Labels::getLabel('MSG_Invalid_Token', $this->siteLangId);
                LibHelper::dieJsonError($message);
            }

            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $userProfile = $facebook->api('/me?fields=id,name,email');
            } catch (FacebookApiException $e) {
                FatUtility::dieJsonError($e->getMessage());
            }

            if (empty($userProfile)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangId));
            }

            // User info ok? Let's print it (Here we will be adding the login and registering routines)
            $facebookName = $userProfile['name'];
            $userFacebookId = $userProfile['id'];
            $facebookEmail = !empty($userProfile['email']) ? $userProfile['email'] : '';
        } else {
            $facebookEmail = FatApp::getPostedData('email', FatUtility::VAR_STRING, '');
            $userFacebookId = FatApp::getPostedData('id', FatUtility::VAR_STRING, '');
            $firstName = FatApp::getPostedData('first_name', FatUtility::VAR_STRING, '');
            $facebookName = trim($firstName.' '.FatApp::getPostedData('last_name', FatUtility::VAR_STRING, ''));
        }
        if ((empty($facebookEmail) && empty($userFacebookId)) || empty($facebookName)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId));
        }

        // User info ok? Let's print it (Here we will be adding the login and registering routines)
        $db = FatApp::getDb();
        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array('user_id','user_facebook_id','credential_email','credential_active','user_deleted', 'user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate', 'user_registered_initially_for'), true, false);

        if (!empty($facebookEmail)) {
            $srch->addCondition('credential_email', '=', $facebookEmail);
        } else {
            $srch->addCondition('user_facebook_id', '=', $userFacebookId);
        }

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        // CommonHelper::printArray($row, true);
        if ($row) {
            if ($row['credential_active'] != applicationConstants::ACTIVE) {
                $message = Labels::getLabel("ERR_YOUR_ACCOUNT_HAS_BEEN_DEACTIVATED", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                // CommonHelper::redirectUserReferer();
            }
            if ($row['user_deleted'] == applicationConstants::YES) {
                $message = Labels::getLabel("ERR_USER_INACTIVE_OR_DELETED", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage(Labels::getLabel("ERR_USER_INACTIVE_OR_DELETED", $this->siteLangId));
                // CommonHelper::redirectUserReferer();
            }

            if (0 < $userType) {
                $userTypeArr = [
                    User::USER_TYPE_BUYER => 'user_is_buyer',
                    User::USER_TYPE_SELLER => 'user_is_supplier',
                    User::USER_TYPE_ADVERTISER => 'user_is_advertiser',
                    User::USER_TYPE_AFFILIATE => 'user_is_affiliate',
                ];

                $invalidUser = false;
                if (in_array($userType, array_keys($userTypeArr)) && $row[$userTypeArr[$userType]] == applicationConstants::NO) {
                    $invalidUser = true;
                } elseif (!in_array($userType, array_keys($userTypeArr)) && $row['user_registered_initially_for'] != $userType) {
                    $invalidUser = true;
                }

                if ($invalidUser) {
                    FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_User', $this->siteLangId));
                }
            }
            $userId = $row['user_id'];
            $userObj->setMainTableRecordId($row['user_id']);

            $arr = array('user_facebook_id' => $userFacebookId);

            if (!$userObj->setUserInfo($arr)) {
                $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                // CommonHelper::redirectUserReferer();
            }
        } else {
            $userType = (0 < $userType ? $userType : User::USER_TYPE_BUYER);
            $userId = $this->setupUser($userType, $facebookName, 0, $userFacebookId, $facebookEmail);
        }
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_facebook_id','user_preferred_dashboard','credential_username','credential_password'));
        if (!$userInfo || ($userInfo && $userInfo['user_facebook_id']!= $userFacebookId)) {
            $message = Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            // CommonHelper::redirectUserReferer();
        }

        $authentication = new UserAuthentication();
        if (!$authentication->login($userInfo['credential_username'], $userInfo['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
            $message = Labels::getLabel($authentication->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_code']);
        unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_access_token']);
        unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_user_id']);

        $cartObj = new Cart();
        if ($cartObj->hasProducts() && false ===  MOBILE_APP_API_CALL) {
            $url = CommonHelper::generateFullUrl('cart');
            $this->set('url', $url);
            $this->set('msg', Labels::getLabel('MSG_LoggedIn_SUCCESSFULLY', $this->siteLangId));
            $this->_template->render(false, false, 'json-success.php');
        }

        $preferredDashboard = 0;
        if ($userInfo != false) {
            $preferredDashboard = $userInfo['user_preferred_dashboard'];
            $redirectUrl = $userObj->getPreferedDashbordRedirectUrl($preferredDashboard);
        }
        if (isset($_SESSION['referer_page_url'])) {
            $redirectUrl = $_SESSION['referer_page_url'];
        }
        $this->set('url', $redirectUrl);
        $this->set('msg', Labels::getLabel('MSG_LoggedIn_SUCCESSFULLY', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            if (!$token = $userObj->setMobileAppToken()) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
            $userInfo = $userObj->getUserInfo(array('user_name', 'user_id', 'user_phone', 'credential_email'), true, true, true);
            $this->set('token', $token);
            $this->set('userInfo', $userInfo);
            $this->_template->render(true, true, 'guest-user/login.php');
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function loginGoogle()
    {
        $userType = FatApp::getPostedData('type', FatUtility::VAR_INT, 0);

        if (true ===  MOBILE_APP_API_CALL && 1 > $userType) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        include_once CONF_INSTALLATION_PATH . 'library/GoogleAPI/vendor/autoload.php'; // include the required calss files for google login

        $client = new Google_Client();
        $client->setApplicationName(FatApp::getConfig('CONF_WEBSITE_NAME_'.$this->siteLangId)); // Set your applicatio name
        $client->setScopes(['email']); // set scope during user login
        $client->setClientId(FatApp::getConfig("CONF_GOOGLEPLUS_CLIENT_ID")); // paste the client id which you get from google API Console
        $client->setClientSecret(FatApp::getConfig("CONF_GOOGLEPLUS_CLIENT_SECRET")); // set the client secret

        $currentPageUri = CommonHelper::generateFullUrl('GuestUser', 'loginGoogle', array(), '', false);
        $client->setRedirectUri($currentPageUri);
        $client->setDeveloperKey(FatApp::getConfig("CONF_GOOGLEPLUS_DEVELOPER_KEY")); // Developer key

        $oauth2 =new Google_Service_Oauth2($client); // Call the OAuth2 class for get email address

        if (false ===  MOBILE_APP_API_CALL) {
            $get = FatApp::getQueryStringData();
            $accessToken = false;
            if (isset($get['code'])) {
                $client->authenticate($get['code']); // Authenticate
                $accessToken = $client->getAccessToken();
            }

            if (false === $accessToken) {
                $authUrl = $client->createAuthUrl();
                FatApp::redirectUser($authUrl);
            }
        } else {
            $accessToken = FatApp::getPostedData('accessToken');
        }

        $client->setAccessToken($accessToken);
        $user = $oauth2->userinfo->get();

        $userGoogleEmail = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
        $userGoogleId = $user['id'];
        $userGoogleName = $user['name'];

        if ($user['name'] == '') {
            $exp = explode("@", $user['email']);
            $userGoogleName = substr($exp[0], 0, 80);
        }

        if (isset($userGoogleEmail) && (!empty($userGoogleEmail))) {
            $db = FatApp::getDb();
            $userObj = new User();
            $srch = $userObj->getUserSearchObj(array('user_id','credential_email','credential_active', 'user_deleted', 'user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate', 'user_registered_initially_for'));
            $srch->addCondition('credential_email', '=', $userGoogleEmail);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);

            if ($row) {
                if ($row['credential_active'] != applicationConstants::ACTIVE) {
                    $message = Labels::getLabel('ERR_YOUR_ACCOUNT_HAS_BEEN_DEACTIVATED', $this->siteLangId);
                    if (true ===  MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    CommonHelper::redirectUserReferer();
                }

                if ($row['user_deleted'] == applicationConstants::YES) {
                    $message = Labels::getLabel("ERR_USER_INACTIVE_OR_DELETED", $this->siteLangId);
                    if (true ===  MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($message);
                    }
                    Message::addErrorMessage(Labels::getLabel("ERR_USER_INACTIVE_OR_DELETED", $this->siteLangId));
                    CommonHelper::redirectUserReferer();
                }

                if (0 < $userType) {
                    $userTypeArr = [
                        User::USER_TYPE_BUYER => 'user_is_buyer',
                        User::USER_TYPE_SELLER => 'user_is_supplier',
                        User::USER_TYPE_ADVERTISER => 'user_is_advertiser',
                        User::USER_TYPE_AFFILIATE => 'user_is_affiliate',
                    ];

                    $invalidUser = false;
                    if (in_array($userType, array_keys($userTypeArr)) && $row[$userTypeArr[$userType]] == applicationConstants::NO) {
                        $invalidUser = true;
                    } elseif (!in_array($userType, array_keys($userTypeArr)) && $row['user_registered_initially_for'] != $userType) {
                        $invalidUser = true;
                    }

                    if ($invalidUser) {
                        FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_User', $this->siteLangId));
                    }
                }
                $userId = $row['user_id'];
                $userObj->setMainTableRecordId($userId);

                $arr = array('user_googleplus_id' => $userGoogleId);

                if (!$userObj->setUserInfo($arr)) {
                    $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
                    if (true ===  MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    CommonHelper::redirectUserReferer();
                }
            } else {
                $userType = (0 < $userType ? $userType : User::USER_TYPE_BUYER);
                $userId = $this->setupUser($userType, $userGoogleName, $userGoogleId, 0, $userGoogleEmail);
            }

            $userObj = new User($userId);

            $userInfo = $userObj->getUserInfo(array('user_googleplus_id','user_preferred_dashboard','credential_username','credential_password'));


            if (!$userInfo || ($userInfo && $userInfo['user_googleplus_id']!= $userGoogleId)) {
                $message = Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }

            $authentication = new UserAuthentication();
            if (!$authentication->login($userInfo['credential_username'], $userInfo['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
                $message = Labels::getLabel($authentication->getError(), $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }

            if (true ===  MOBILE_APP_API_CALL) {
                if (!$token = $userObj->setMobileAppToken()) {
                    FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                }
                $userInfo = $userObj->getUserInfo(array('user_name', 'user_id', 'user_phone', 'credential_email'), true, true, true);
                $this->set('token', $token);
                $this->set('userInfo', $userInfo);
                $this->_template->render(true, true, 'guest-user/login.php');
            }

            $cartObj = new Cart();
            if ($cartObj->hasProducts()) {
                FatApp::redirectUser(CommonHelper::generateFullUrl('cart'));
            }


            $preferredDashboard = 0;
            if ($userInfo != false) {
                $preferredDashboard = $userInfo['user_preferred_dashboard'];
            }
            if (isset($_SESSION['referer_page_url'])) {
                $redirectUrl = $_SESSION['referer_page_url'];
                unset($_SESSION['referer_page_url']);
                FatApp::redirectUser($redirectUrl);
            }
            FatApp::redirectUser(User::getPreferedDashbordRedirectUrl($preferredDashboard));
        }

        CommonHelper::redirectUserReferer();
    }

    private function setupUser($user_type, $userName, $userGoogleId, $userFacebookId, $userEmail)
    {
        $userObj = new User();
        $db = FatApp::getDb();
        $user_is_supplier = (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0: 1;
        $user_is_advertiser = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0: 1;

        if (isset($user_type) && $user_type == User::USER_TYPE_BUYER) {
            $userPreferredDashboard = User::USER_BUYER_DASHBOARD;
            $user_registered_initially_for = User::USER_TYPE_BUYER;
        }
        if (isset($user_type) && $user_type == User::USER_TYPE_SELLER) {
            $userPreferredDashboard = User::USER_SELLER_DASHBOARD;
            $user_registered_initially_for = User::USER_TYPE_SELLER;
        }

        $db->startTransaction();

        $userData = array(
        'user_name' => $userName,
        'user_is_buyer' => (isset($user_type) && $user_type == User::USER_TYPE_BUYER) ? 1:0,
        'user_is_supplier' => (isset($user_type) && $user_type == User::USER_TYPE_SELLER) ? 1:0,
        'user_is_advertiser' => $user_is_advertiser,
        'user_googleplus_id' => !empty($userGoogleId) ? $userGoogleId : '',
        'user_facebook_id' => !empty($userFacebookId) ? $userFacebookId : '',
        'user_preferred_dashboard' => $userPreferredDashboard,
        'user_registered_initially_for' => $user_registered_initially_for
        );

        $userObj->assignValues($userData);
        if (!$userObj->save()) {
            $message = Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError();
            Message::addErrorMessage($message);
            $db->rollbackTransaction();
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            CommonHelper::redirectUserReferer();
        }
        $userId = $userObj->getMainTableRecordId();

        $socialUid = !empty($userGoogleId) ? $userGoogleId : $userFacebookId;
        $username = str_replace(" ", "", $userName).$socialUid;

        if (!$userObj->setLoginCredentials($username, $userEmail, uniqid(), 1, 1)) {
            $message = Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError();
            Message::addErrorMessage($message);
            $db->rollbackTransaction();
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            CommonHelper::redirectUserReferer();
        }

        $userData['user_username'] = $username;
        $userData['user_email'] = $userEmail;
        if (FatApp::getConfig('CONF_NOTIFY_ADMIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$this->notifyAdminRegistration($userObj, $userData)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $db->rollbackTransaction();
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1) && $userEmail) {
            $data['user_email'] = $userEmail;
            $data['user_name'] = $username;

            //ToDO::Change login link to contact us link
            $data['link'] = CommonHelper::generateFullUrl('GuestUser', 'loginForm');
            $userEmailObj = new User($userId);
            if (!$this->userWelcomeEmailRegistration($userEmailObj, $data)) {
                $message = Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $db->rollbackTransaction();
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                CommonHelper::redirectUserReferer();
            }
        }

        $db->commitTransaction();
        $userObj->setUpRewardEntry($userId, $this->siteLangId);
        return $userId;
    }

    public function registrationForm()
    {
        if (UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }

        if (UserAuthentication::isUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }

        $registerFrm = $this->getRegistrationForm();

        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        if (!empty($cpage) && is_array($cpage)) {
            $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
        } else {
            $termsAndConditionsLinkHref = 'javascript:void(0)';
        }
        $data = array(
        'registerFrm'    =>    $registerFrm,
        'termsAndConditionsLinkHref'    =>    $termsAndConditionsLinkHref,
        'siteLangId'    =>    $this->siteLangId
        );
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::REGISTRATION_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('data', $data);
        $this->_template->render(true, true, 'guest-user/registration-form.php');
    }

    public function register()
    {
        $frm = $this->getRegistrationForm();
        $post = FatApp::getPostedData();

        $cartObj = new Cart();
        $isCheckOutPage = (isset($post['isCheckOutPage']) && $cartObj->hasProducts()) ? FatUtility::int($post['isCheckOutPage']) : 0;
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if ($post == false) {
            $message = Labels::getLabel(current($frm->getValidationErrors()), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);

            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm', array(applicationConstants::YES)));
        }

        $userObj = new User();
        $db = FatApp::getDb();
        $srch = $userObj->getUserSearchObj(array('user_id','credential_email','credential_username'));
        $condition=$srch->addCondition('credential_username', '=', $post['user_username']);
        $condition->attachCondition('credential_email', '=', $post['user_email'], 'OR');
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if ($row) {
            if ($row['credential_username']==$post['user_username']) {
                $message = Labels::getLabel('MSG_DUPLICATE_USERNAME', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
            } elseif ($row['credential_email']==$post['user_email']) {
                $message = Labels::getLabel('MSG_DUPLICATE_EMAIL', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }
        $db->startTransaction();

        $post['user_is_buyer'] = 1;
        $post['user_is_supplier'] = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1))  ? 0 : 1;
        $post['user_is_advertiser'] = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
        //$post['user_is_supplier'] = 0;
        $post['user_preferred_dashboard'] = User::USER_BUYER_DASHBOARD;
        $post['user_registered_initially_for'] = User::USER_TYPE_BUYER;

        $userObj->assignValues($post);
        if (!$userObj->save()) {
            $db->rollbackTransaction();

            $message = Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError();
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->registrationForm();
            return;
        }

        $active = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1) ? 0: 1;
        $verify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;

        /* from checkout, buyer will be bydeafult acitve and email will be verified[ */
        /* $active = ( $isCheckOutPage == 1 ) ? 1 : $active;
        $verify = ( $isCheckOutPage == 1 ) ? 1 : $verify; */
        /* ] */

        if (!$userObj->setLoginCredentials($post['user_username'], $post['user_email'], $post['user_password'], $active, $verify)) {
            $message = Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError();

            Message::addErrorMessage($message);
            $db->rollbackTransaction();

            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->registrationForm();
            return ;
        }

        $userObj->setUpRewardEntry($userObj->getMainTableRecordId(), $this->siteLangId);

        //$userObj->setUpAffiliateRewarding( $userObj->getMainTableRecordId() );

        if (FatApp::getPostedData('user_newsletter_signup')) {
            include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key == '' || $list_id == '') {
                $message = Labels::getLabel("LBL_Newsletter_is_not_configured_yet,_Please_contact_admin", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                $this->registrationForm();
                return ;
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
            if (!$this->notifyAdminRegistration($userObj, $post)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);

                Message::addErrorMessage($message);
                $db->rollbackTransaction();

                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }

                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                $this->registrationForm();
                return;
            }
        }

        //Send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_USER,
        'notification_record_id' => $userObj->getMainTableRecordId(),
        'notification_user_id' => $userObj->getMainTableRecordId(),
        'notification_label_key' => Notification::NEW_USER_REGISTERATION_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);

            Message::addErrorMessage($message);
            $db->rollbackTransaction();

            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }

            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->registrationForm();
            return;
        }

        if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$this->userEmailVerification($userObj, $post)) {
                $message = Labels::getLabel("MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $db->rollbackTransaction();

                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }

                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                $this->registrationForm();
                return;
            }
        } else {
            if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1)) {
                if (!$this->userWelcomeEmailRegistration($userObj, $post)) {
                    $message = Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);
                    Message::addErrorMessage($message);
                    $db->rollbackTransaction();

                    if (true ===  MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($message);
                    }

                    if (FatUtility::isAjaxCall()) {
                        FatUtility::dieWithError(Message::getHtml());
                    }
                    $this->registrationForm();
                    return;
                }
            }

            $confAutoLoginRegisteration = FatApp::getConfig('CONF_AUTO_LOGIN_REGISTRATION', FatUtility::VAR_INT, 1);
            $confAutoLoginRegisteration = ($isCheckOutPage) ? 1 : $confAutoLoginRegisteration;

            if ($confAutoLoginRegisteration && !(FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1))) {
                $db->commitTransaction();
                $authentication = new UserAuthentication();
                if (!$authentication->login(FatApp::getPostedData('user_username'), FatApp::getPostedData('user_password'), $_SERVER['REMOTE_ADDR'])) {
                    $message = Labels::getLabel($authentication->getError(), $this->siteLangId);

                    if (true ===  MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    if (FatUtility::isAjaxCall()) {
                        FatUtility::dieWithError(Message::getHtml());
                    }

                    FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
                }

                if (false ===  MOBILE_APP_API_CALL) {
                    if ($isCheckOutPage) {
                        $this->set('needLogin', 1);
                        $redirectUrl = CommonHelper::generateUrl('Checkout');
                    } else {
                        $redirectUrl = CommonHelper::generateUrl('Account');
                    }
                    if (FatUtility::isAjaxCall()) {
                        $this->set('msg', Labels::getLabel('LBL_Registeration_Successfull', $this->siteLangId));
                        $this->set('redirectUrl', $redirectUrl);
                        $this->_template->render(false, false, 'json-success.php');
                        exit;
                    }
                    FatApp::redirectUser($redirectUrl);
                }
            }
        }

        $db->commitTransaction();

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', Labels::getLabel('LBL_Registeration_Successfull', $this->siteLangId));
            $this->_template->render();
        }

        $redirectUrl = CommonHelper::generateUrl('GuestUser', 'registrationSuccess');
        if (FatUtility::isAjaxCall()) {
            $this->set('msg', Labels::getLabel('LBL_Registeration_Successfull', $this->siteLangId));
            $this->set('redirectUrl', $redirectUrl);
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }

        FatApp::redirectUser($redirectUrl);
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

    private function notifyAdminRegistration($userObj, $data)
    {
        return $userObj->notifyAdminRegistration($data, $this->siteLangId);
    }

    private function userWelcomeEmailRegistration($userObj, $data)
    {
        $link = CommonHelper::generateFullUrl('GuestUser', 'loginForm');

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

    public function userCheckEmailVerification($code)
    {
        $code = FatUtility::convertToType($code, FatUtility::VAR_STRING);
        if (strlen($code) < 1) {
            Message::addMessage(Labels::getLabel("MSG_PLEASE_CHECK_YOUR_EMAIL_IN_ORDER_TO_VERIFY", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $arrCode = explode('_', $code, 2);

        $userId = FatUtility::int($arrCode[0]);
        if ($userId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_CODE', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $userObj = new User($userId);
        $userData = User::getAttributesById($userId, array('user_id', 'user_is_affiliate'));
        if (!$userData) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_CODE', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$userObj->verifyUserEmailVerificationCode($code)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("ERR_MSG_INVALID_VERIFICATION_REQUEST", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        if ($userData['user_is_affiliate'] != applicationConstants::YES) {
            $srch = new SearchBase('tbl_user_credentials');
            $srch->addCondition('credential_user_id', '=', $userId);
            $rs = $srch->getResultSet();
            $checkActiveRow = $db->fetch($rs);
            if ($checkActiveRow['credential_active'] != applicationConstants::ACTIVE) {
                $active = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1)?0:1;
                if (!$userObj->activateAccount($active)) {
                    $db->rollbackTransaction();
                    Message::addErrorMessage(Labels::getLabel('MSG_INVALID_CODE', $this->siteLangId));
                    FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
                }
            }
        }

        if (!$userObj->verifyAccount()) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_CODE', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $userdata = $userObj->getUserInfo(array('credential_email','credential_password', 'user_name','credential_active'), false);

        if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1)) {
            $data['user_email'] = $userdata['credential_email'];
            $data['user_name'] = $userdata['user_name'];

            //ToDO::Change login link to contact us link
            $data['link'] = CommonHelper::generateFullUrl('GuestUser', 'loginForm');

            if (!$this->userWelcomeEmailRegistration($userObj, $data)) {
                Message::addErrorMessage(Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                $db->rollbackTransaction();
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
            }
        }

        $db->commitTransaction();

        if (FatApp::getConfig('CONF_AUTO_LOGIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            $authentication = new UserAuthentication();

            if (!$authentication->login($userdata['credential_email'], $userdata['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
                Message::addErrorMessage(Labels::getLabel($authentication->getError(), $this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
            }
            FatApp::redirectUser(CommonHelper::generateUrl('Account'));
        }

        Message::addMessage(Labels::getLabel("MSG_EMAIL_VERIFIED", $this->siteLangId));

        FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
    }

    public function changeEmailVerification($code)
    {
        $code = FatUtility::convertToType($code, FatUtility::VAR_STRING);
        if (strlen($code) < 1) {
            Message::addMessage(Labels::getLabel("MSG_PLEASE_CHECK_YOUR_EMAIL_IN_ORDER_TO_VERIFY", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $arrCode = explode('_', $code, 2);

        $userId = FatUtility::int($arrCode[0]);
        if ($userId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_CODE', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $userObj = new User($userId);

        $newUserEmail = $userObj->verifyUserEmailVerificationCode($code);

        if (!$newUserEmail) {
            Message::addErrorMessage(Labels::getLabel("ERR_MSG_INVALID_VERIFICATION_REQUEST", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $usr = new User();
        $srch = $usr->getUserSearchObj(array('uc.credential_email'));
        $srch->addCondition('uc.credential_email', '=', $newUserEmail);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);

        if ($record) {
            Message::addErrorMessage(Labels::getLabel("ERR_DUPLICATE_EMAIL", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }


        $srchUser = $usr->getUserSearchObj(array('u.user_name','uc.credential_email'));
        $srchUser->addCondition('u.user_id', '=', $userId);
        $srchUser->doNotCalculateRecords();
        $srchUser->doNotLimitRecords();
        $rs = $srchUser->getResultSet();
        $data = FatApp::getDb()->fetch($rs);

        if (!$userObj->changeEmail($newUserEmail)) {
            Message::addErrorMessage(Labels::getLabel("MSG_UPDATED_EMAIL_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $email = new EmailHandler();
        $currentEmail = $data['credential_email'];
        if (!empty($currentEmail) && !$email->sendEmailChangedNotification($this->siteLangId, array('user_name' => $data['user_name'],'user_email' => $data['credential_email'],'user_new_email' => $newUserEmail))) {
            Message::addErrorMessage(Labels::getLabel("MSG_UNABLE_TO_SEND_EMAIL_CHANGE_NOTIFICATION", $this->siteLangId) . $userObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        if (FatApp::getConfig('CONF_AUTO_LOGIN_REGISTRATION', FatUtility::VAR_INT, 1) || UserAuthentication::isUserLogged()) {
            $userdata = $userObj->getUserInfo(array('credential_username','credential_password'));
            $authentication = new UserAuthentication();
            if (!$authentication->login($userdata['credential_username'], $userdata['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
                Message::addErrorMessage(Labels::getLabel($authentication->getError(), $this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
            }
            FatApp::redirectUser(CommonHelper::generateUrl('Account'));
        }

        Message::addMessage(Labels::getLabel("MSG_EMAIL_VERIFIED", $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
    }

    public function registrationSuccess()
    {
        if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1)) {
            $this->set('registrationMsg', Labels::getLabel("MSG_SUCCESS_USER_SIGNUP_EMAIL_VERIFICATION_PENDING", $this->siteLangId));
        } elseif (FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1)) {
            $this->set('registrationMsg', Labels::getLabel("MSG_SUCCESS_USER_SIGNUP_ADMIN_APPROVAL_PENDING", $this->siteLangId));
        } else {
            $this->set('registrationMsg', Labels::getLabel("MSG_REGISTERED_SUCCESSFULLY", $this->siteLangId));
        }

        $this->_template->render();
    }

    public function forgotPasswordForm()
    {
        $frm = $this->getForgotForm();
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::FORGOT_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render();
    }

    public function forgotPassword()
    {
        $frm = $this->getForgotForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage($frm->getValidationErrors());
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        if (false ===  MOBILE_APP_API_CALL && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            if (!CommonHelper::verifyCaptcha()) {
                $message = Labels::getLabel('MSG_That_captcha_was_incorrect', $this->siteLangId);
                Message::addErrorMessage($message);
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
            }
        }

        $user = $post['user_email_username'];

        $userAuthObj = new UserAuthentication();
        $row = $userAuthObj->getUserByEmailOrUserName($user, '', false);

        if (!$row || false === $row) {
            $message = Labels::getLabel($userAuthObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        if ($row['user_is_shipping_company'] == applicationConstants::YES) {
            $message = Labels::getLabel('ERR_Shipping_user_are_not_allowed_to_place_forgot_password_request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        if ($userAuthObj->checkUserPwdResetRequest($row['user_id'])) {
            $message = Labels::getLabel($userAuthObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        $token = UserAuthentication::encryptPassword(FatUtility::getRandomString(20));
        $row['token'] = $token;

        $userAuthObj->deleteOldPasswordResetRequest();

        $db = FatApp::getDb();
        $db->startTransaction();
        // commonHelper::printArray($row); die;
        if (!$userAuthObj->addPasswordResetRequest($row)) {
            $db->rollbackTransaction();
            $message = Labels::getLabel($userAuthObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        $row['link'] = CommonHelper::generateFullUrl('GuestUser', 'resetPassword', array($row['user_id'], $token));

        /*Send verification email if email not verified[*/
        $row['user_email'] = $row['credential_email'];
        $srch = new SearchBase('tbl_user_credentials');
        $srch->addCondition('credential_email', '=', $row['user_email']);
        $rs = $srch->getResultSet();
        $checkVerificationRow = $db->fetch($rs);

        $userObj = new User($row['user_id']);
        if ($checkVerificationRow['credential_verified'] != applicationConstants::YES) {
            if (!$this->userEmailVerification($userObj, $row, $this->siteLangId)) {
                $message = Labels::getLabel("MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
            }
        }
        /*]*/

        $email = new EmailHandler();
        if (!$email->sendForgotPasswordLinkEmail($this->siteLangId, $row)) {
            $db->rollbackTransaction();
            $message = Labels::getLabel("MSG_ERROR_IN_SENDING_PASSWORD_RESET_LINK_EMAIL", $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm'));
        }

        $db->commitTransaction();

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', Labels::getLabel("MSG_YOUR_PASSWORD_RESET_INSTRUCTIONS_TO_YOUR_EMAIL", $this->siteLangId));
            $this->_template->render();
        }
        Message::addMessage(Labels::getLabel("MSG_YOUR_PASSWORD_RESET_INSTRUCTIONS_TO_YOUR_EMAIL", $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
    }

    public function resendVerification($usernameOrEmail = '')
    {
        $frm = $this->getForgotForm();
        if (empty($usernameOrEmail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $userAuthObj = new UserAuthentication();

        if (!$row = $userAuthObj->getUserByEmailOrUserName($usernameOrEmail, false, false)) {
            FatUtility::dieJsonError(Labels::getLabel($userAuthObj->getError(), $this->siteLangId));
        }

        $row['user_email'] = $row['credential_email'];
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_user_credentials');
        $srch->addCondition('credential_email', '=', $row['user_email']);
        $rs = $srch->getResultSet();
        $checkVerificationRow = $db->fetch($rs);

        $userObj = new User($row['user_id']);
        if ($checkVerificationRow['credential_verified'] != 1) {
            if (!$this->userEmailVerification($userObj, $row, $this->siteLangId)) {
                FatUtility::dieJsonError(Labels::getLabel("MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
            } else {
                $message = Labels::getLabel("MSG_VERIFICATION_EMAIL_HAS_BEEN_SENT_AGAIN", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    $this->set('msg', $message);
                    $this->_template->render();
                }
                FatUtility::dieJsonSuccess($message);
            }
        } else {
            FatUtility::dieJsonError(Labels::getLabel("MSG_You_are_already_verified_please_login.", $this->siteLangId));
        }
    }

    public function resetPassword($userId = 0, $token = '')
    {
        $userId = FatUtility::int($userId);

        if ($userId < 1 || strlen(trim($token)) < 20) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_RESET_PASSWORD_REQUEST'), $this->siteLangId);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $userAuthObj = new UserAuthentication();

        if (!$userAuthObj->checkResetLink($userId, trim($token), 'form')) {
            Message::addErrorMessage($userAuthObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        $frm = $this->getResetPwdForm($userId, trim($token));
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::RESET_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('frm', $frm);
        $this->_template->render();
    }

    public function resetPasswordSetup()
    {
        $newPwd = FatApp::getPostedData('new_pwd');
        $confirmPwd = FatApp::getPostedData('confirm_pwd');
        $userId = FatApp::getPostedData('user_id', FatUtility::VAR_INT);
        $token = FatApp::getPostedData('token', FatUtility::VAR_STRING);

        if ($userId < 1 && strlen(trim($token)) < 20) {
            Message::addErrorMessage(Labels::getLabel('MSG_REQUEST_IS_INVALID_OR_EXPIRED', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getResetPwdForm($userId, $token);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if ($post == false) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (! ValidateElement::password($post['new_pwd'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userAuthObj = new UserAuthentication();

        if (! $userAuthObj->checkResetLink($userId, trim($token), 'submit')) {
            Message::addErrorMessage($userAuthObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pwd = UserAuthentication::encryptPassword($newPwd);

        if (!$userAuthObj->resetUserPassword($userId, $pwd)) {
            Message::addErrorMessage($userAuthObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $email = new EmailHandler();

        $userObj=new User($userId);
        $row = $userObj->getUserInfo(array(User::tblFld('name'), User::DB_TBL_CRED_PREFIX.'email'), '', false);
        $row['link'] = CommonHelper::generateFullUrl('GuestUser', 'loginForm');
        $email->sendResetPasswordConfirmationEmail($this->siteLangId, $row);

        /* Message::addMessage(Labels::getLabel('MSG_PASSWORD_CHANGED_SUCCESSFULLY',$this->siteLangId));
        FatUtility::dieJsonError( Message::getHtml() ); */

        $this->set('msg', Labels::getLabel('MSG_PASSWORD_CHANGED_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function configureEmail()
    {
        $this->_template->render();
    }

    public function changeEmailForm()
    {
        $frm = $this->getChangeEmailForm(false);

        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false, 'account/change-email-form.php');
    }

    public function updateEmail()
    {
        $emailFrm = $this->getChangeEmailForm(false);
        $post = $emailFrm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            $message = current($emailFrm->getValidationErrors());
            LibHelper::dieJsonError($message);
        }

        if ($post['new_email'] != $post['conf_new_email']) {
            $message = Labels::getLabel('MSG_New_email_confirm_email_does_not_match', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $userObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $userObj->getUserSearchObj(array('user_id','credential_email','user_name'));
        $rs = $srch->getResultSet();

        if (!$rs) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $data = FatApp::getDb()->fetch($rs, 'user_id');
        if ($data === false || $data['credential_email'] != '') {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        /* if ($data['credential_password'] != UserAuthentication::encryptPassword($post['current_password'])) {
        Message::addErrorMessage(Labels::getLabel('MSG_YOUR_CURRENT_PASSWORD_MIS_MATCHED',$this->siteLangId));
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $arr = array(
        'user_name' => $data['user_name'],
        'user_email' => $post['new_email']
        );

        if (!$this->userEmailVerifications($userObj, $arr, true)) {
            $message = Labels::getLabel('MSG_ERROR_IN_SENDING_VERFICATION_EMAIL', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_CHANGE_EMAIL_REQUEST_SENT_SUCCESSFULLY', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function logout()
    {
        UserAuthentication::logout();
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
    }

    private function getForgotForm()
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmPwdForgot');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Username_or_email', $siteLangId), 'user_email_username')->requirements()->setRequired();

        if (false ===  MOBILE_APP_API_CALL && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            $frm->addHtml('', 'htmlNote', '<div class="g-recaptcha" data-sitekey="'.FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '').'"></div>');
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SUBMIT', $siteLangId));
        return $frm;
    }

    private function getResetPwdForm($uId, $token)
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmResetPwd');
        $fld_np = $frm->addPasswordField(Labels::getLabel('LBL_NEW_PASSWORD', $siteLangId), 'new_pwd');
        $fld_np->htmlAfterField='<p class="note">'.sprintf(Labels::getLabel('LBL_Example_password', $siteLangId), 'User@123').'</p>';
        $fld_np->requirements()->setRequired();
        $fld_np->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $fld_np->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $siteLangId));
        $fld_cp = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_NEW_PASSWORD', $siteLangId), 'confirm_pwd');
        $fld_cp->requirements()->setRequired();
        $fld_cp->requirements()->setCompareWith('new_pwd', 'eq', '');

        $frm->addHiddenField('', 'user_id', $uId, array('id'=>'user_id'));
        $frm->addHiddenField('', 'token', $token, array('id'=>'token'));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_RESET_PASSWORD', $siteLangId));
        return $frm;
    }
}
