<?php
class UserAuthentication extends FatModel
{
    const SESSION_ELEMENT_NAME = 'yokartUserSession';
    const AFFILIATE_SESSION_ELEMENT_NAME = 'yokartAffiliateSession';
    const YOKARTUSER_COOKIE_NAME = '_uyokart';

    const DB_TBL_USER_PRR = 'tbl_user_password_reset_requests';
    const DB_TBL_UPR_PREFIX = 'uprr_';

    const DB_TBL_USER_AUTH = 'tbl_user_auth_token';
    const DB_TBL_UAUTH_PREFIX = 'uauth_';

    const TOKEN_LENGTH = 32;

    private $commonLangId;

    const AFFILIATE_REG_STEP1 = 1;
    const AFFILIATE_REG_STEP2 = 2;
    const AFFILIATE_REG_STEP3 = 3;
    const AFFILIATE_REG_STEP4 = 4;

    public function __construct()
    {
        $this->commonLangId = CommonHelper::getLangId();
    }

    public static function getAffiliateRegisterationStepArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error("Language Id not specified.", E_USER_ERROR);
        }
        return array(
        static::AFFILIATE_REG_STEP1    =>    Labels::getLabel('LBL_Personal_Details', $langId),
        static::AFFILIATE_REG_STEP2    =>    Labels::getLabel('LBL_Company_Details', $langId),
        static::AFFILIATE_REG_STEP3    =>    Labels::getLabel('LBL_Payment_Information', $langId),
        static::AFFILIATE_REG_STEP4    =>    Labels::getLabel('LBL_Confirmation', $langId),
        );
    }

    public static function encryptPassword($pass)
    {
        return md5(PASSWORD_SALT . $pass . PASSWORD_SALT);
    }

    public function logFailedAttempt($ip, $username)
    {
        $db = FatApp::getDb();

        $db->deleteRecords(
            'tbl_failed_login_attempts',
            array(
            'smt' => 'attempt_time < ?',
            'vals' => array(date('Y-m-d H:i:s', strtotime("-7 Day")) ) )
        );

        $db->insertFromArray(
            'tbl_failed_login_attempts',
            array(
            'attempt_username'=>$username,
            'attempt_ip'=>$ip,
            'attempt_time'=>date('Y-m-d H:i:s')
            )
        );

        // For improvement, we can send an email about the failed attempt here.
    }

    public function clearFailedAttempt($ip, $username)
    {
        $db = FatApp::getDb();

        return $db->deleteRecords(
            'tbl_failed_login_attempts',
            array(
            'smt' => 'attempt_username = ? and attempt_ip = ?',
            'vals' => array($username,$ip) )
        );
    }

    public function isBruteForceAttempt($ip, $username)
    {
        $db = FatApp::getDb();
        $ips = explode(',', FatApp::getConfig("CONF_WHITELISTED_IP", FatUtility::VAR_STRING, ''));
        if (in_array($ip, $ips)) {
            return false;
        }
        $srch = new SearchBase('tbl_failed_login_attempts');
        $srch->addCondition('attempt_ip', '=', $ip)->attachCondition('attempt_username', '=', $username);
        $srch->addCondition('attempt_time', '>=', date('Y-m-d H:i:s', strtotime("-5 minutes")));
        $srch->addFld('COUNT(*) AS total');
        $rs = $srch->getResultSet();

        $row = $db->fetch($rs);

        return ($row['total'] > 3);
    }

    public static function doAppLogin($token, $userType = 0)
    {
        $authRow = self::checkLoginTokenInDB($token);

        if (strlen($token) != self::TOKEN_LENGTH || empty($authRow)) {
            self::clearLoggedUserLoginCookie();
            return false;
        }

        $browser = CommonHelper::userAgent();
        if (strtotime($authRow['uauth_expiry']) < strtotime('now')) {
            self::clearLoggedUserLoginCookie();
            return false;
        }

        $ths = new UserAuthentication();
        if ($row = $ths->loginByAppToken($authRow)) {
            return true;
        }

        return false;
    }

    private function loginByAppToken($authRow)
    {
        $userObj = new User($authRow['uauth_user_id']);

        if ($row = $userObj->getProfileData()) {
            if ($row['credential_verified'] != applicationConstants::YES) {
                return false;
            }

            if ($row['credential_active'] != applicationConstants::YES) {
                return false;
            }
            $row['user_ip'] = CommonHelper::getClientIp();
            $this->setSession($row);

            return $row;
        }
        return false;
    }

    public static function doCookieLogin($returnAuthRow = true)
    {
        $cookieName = self::YOKARTUSER_COOKIE_NAME;

        if (!array_key_exists($cookieName, $_COOKIE)) {
            return false;
        }

        $token = $_COOKIE[$cookieName];
        $authRow = false;

        $authRow = self::checkLoginTokenInDB($token);

        if (strlen($token) != self::TOKEN_LENGTH || empty($authRow)) {
            self::clearLoggedUserLoginCookie();
            return false;
        }

        $browser = CommonHelper::userAgent();
        if (strtotime($authRow['uauth_expiry']) < strtotime('now') || $authRow['uauth_browser'] != $browser || CommonHelper::userIp() != $authRow['uauth_last_ip']) {
            self::clearLoggedUserLoginCookie();
            return false;
        }

        $ths = new UserAuthentication();
        if ($ths->loginByCookie($authRow)) {
            if (true === $returnAuthRow) {
                return $authRow;
            }
            return true;
        }
        return false;
    }

    public function guestLogin($useremail, $name, $ip)
    {
        $db = FatApp::getDb();
        $srch = User::getSearchObject(true, false);
        $srch->addCondition('credential_email', '=', $useremail);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!empty($row)) {
            if ($row['user_is_buyer'] != applicationConstants::YES) {
                $this->error = Labels::getLabel('MSG_Please_login_with_buyer_account', $this->commonLangId);
                return false;
            }

            if ($row['credential_verified'] == applicationConstants::YES && $row['credential_active'] == applicationConstants::ACTIVE) {
                $this->error = Labels::getLabel('ERR_YOUR_ACCOUNT_ALREADY_EXIST._PLEASE_LOGIN', $this->commonLangId);
                return false;
            }

            $rowUser = User::getAttributesById($row['user_id']);

            $rowUser['user_ip'] = $ip;
            $rowUser['user_is_guest'] = true;
            $rowUser['user_email'] = $row['credential_email'];
            Cart::setCartAttributes($row['user_id']);
            $this->setSession($rowUser);
            return true;
        }


        $userObj = new User();
        $db->startTransaction();

        $data = array(
        'user_name'=>$name,
        'user_username'=>$useremail,
        'user_email'=>$useremail,
        'user_is_buyer'=>1,
        'user_preferred_dashboard'=>User::USER_BUYER_DASHBOARD,
        'user_registered_initially_for'=>User::USER_TYPE_BUYER,
        );
        $userObj->assignValues($data);

        if (!$userObj->save()) {
            $db->rollbackTransaction();
            $this->error = Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->commonLangId) . $userObj->getError();
            return false;
        }

        $active = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1) ? 0: 1;
        $verify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;

        $pass =  CommonHelper::getRandomPassword(5);
        if (!$userObj->setLoginCredentials($useremail, $useremail, $pass, $active, $verify)) {
            $this->error =Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->commonLangId) . $userObj->getError();
            $db->rollbackTransaction();
            return false;
        }

        if (FatApp::getConfig('CONF_NOTIFY_ADMIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->notifyAdminRegistration($data, $this->commonLangId)) {
                $this->error =Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->commonLangId);
                $db->rollbackTransaction();
                return false;
            }
        }

        if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->guestUserWelcomeEmail($data, $this->commonLangId)) {
                $this->error = Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->commonLangId);
                $db->rollbackTransaction();
                return false;
            }
        }

        $db->commitTransaction();

        $srch = User::getSearchObject(true, false);
        $srch->addCondition('credential_email', '=', $useremail);
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            $this->logFailedAttempt($ip, $useremail);
            $this->error = Labels::getLabel('ERR_INVALID_USERNAME_OR_PASSWORD', $this->commonLangId);
            return false;
        }

        $rowUser = User::getAttributesById($row['credential_user_id']);

        $rowUser['user_ip'] = $ip;
        $rowUser['user_is_guest'] = true;
        $rowUser['user_email'] = $row['credential_email'];
        Cart::setCartAttributes($row['credential_user_id']);
        $this->setSession($rowUser);

        $this->clearFailedAttempt($ip, $useremail);

        return true;
    }

    public function login($username, $password, $ip, $encryptPassword = true, $isAdmin = false, $tempUserId = 0, $userType = 0)
    {
        $db = FatApp::getDb();
        if ($this->isBruteForceAttempt($ip, $username)) {
            $userSrch = User::getSearchObject(true, false);
            $userSrch->addCondition('credential_username', '=', $username);
            $userRs = $userSrch->getResultSet();

            if ($row = $db->fetch($userRs)) {
                $email = new EmailHandler();
                $email->failedLoginAttempt(FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1), $row);
            }

            $this->error =  Labels::getLabel('ERR_LOGIN_ATTEMPT_LIMIT_EXCEEDED_PLEASE_TRY_LATER', $this->commonLangId);
            return false;
        }

        if ($encryptPassword) {
            $password = UserAuthentication::encryptPassword($password);
        }

        $srch = User::getSearchObject(true, false);
        $condition=$srch->addCondition('credential_username', '=', $username);
        $condition->attachCondition('credential_email', '=', $username, 'OR');
        $srch->addCondition('credential_password', '=', $password);
        if (0 < $userType) {
            switch ($userType) {
                case User::USER_TYPE_BUYER:
                    $srch->addCondition('user_is_buyer', '=', applicationConstants::YES);
                    break;
                case User::USER_TYPE_SELLER:
                    $srch->addCondition('user_is_supplier', '=', applicationConstants::YES);
                    break;
                case User::USER_TYPE_ADVERTISER:
                    $srch->addCondition('user_is_advertiser', '=', applicationConstants::YES);
                    break;
                case User::USER_TYPE_AFFILIATE:
                    $srch->addCondition('user_is_affiliate', '=', applicationConstants::YES);
                    break;
                default:
                    $srch->addCondition('user_registered_initially_for', '=', $userType);
                    break;
            }
        }
        $rs = $srch->getResultSet();


        if (!$row = $db->fetch($rs)) {
            $this->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('ERR_INVALID_USERNAME_OR_PASSWORD', $this->commonLangId);
            return false;
        }

        if ($row && $row['user_deleted'] == applicationConstants::YES) {
            $this->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('ERR_USER_INACTIVE_OR_DELTED', $this->commonLangId);
            return false;
        }

        if ($row['user_is_shipping_company'] == applicationConstants::YES) {
            $this->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('ERR_Shipping_user_are_not_allowed_to_login', $this->commonLangId);
            return false;
        }

        if ((!(strtolower($row['credential_username']) === strtolower($username) || strtolower($row['credential_email']) === strtolower($username))) || $row['credential_password'] !== $password) {
            $this->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('ERR_INVALID_USERNAME_OR_PASSWORD', $this->commonLangId);
            return false;
        }
        if (!$isAdmin) {
            if ($row['credential_verified'] != applicationConstants::YES) {
                $this->error = str_replace("{clickhere}", '<a href="javascript:void(0)" onclick="resendVerificationLink('."'".$username."'".')">'.Labels::getLabel('LBL_Click_Here', $this->commonLangId).'</a>', Labels::getLabel('MSG_Your_Account_verification_is_pending_{clickhere}', $this->commonLangId));

                if (true ===  MOBILE_APP_API_CALL) {
                    $this->error = Labels::getLabel('MSG_Your_Account_verification_is_pending', $this->commonLangId);
                }

                if (FatUtility::isAjaxCall() || true ===  MOBILE_APP_API_CALL) {
                    $json['status'] = 0;
                    $json['msg'] = $this->error;
                    $json['notVerified'] = 1;
                    die(json_encode($json));
                }
                return false;
            }

            if ($row['credential_active'] != applicationConstants::ACTIVE) {
                $this->error = Labels::getLabel('ERR_YOUR_ACCOUNT_HAS_BEEN_DEACTIVATED_OR_NOT_ACTIVE', $this->commonLangId);
                return false;
            }
        }

        $rowUser = User::getAttributesById($row['credential_user_id']);

        $rowUser['user_ip'] = $ip;
        $rowUser['user_email'] = $row['credential_email'];
        Cart::setCartAttributes($row['credential_user_id'], $tempUserId);
        $this->setSession($rowUser);
        /* $_SESSION[static::SESSION_ELEMENT_NAME] = array(
        'user_id'=>$rowUser['user_id'],
        'user_name'=>$rowUser['user_name'],
        'user_ip'=>$ip
        ); */

        /* clear failed login attempt for the user [ */
        $this->clearFailedAttempt($ip, $username);
        /* ] */

        return true;
    }

    private function setSession($data)
    {
        session_regenerate_id();
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME] = array(
        'user_id' => $data['user_id'],
        'user_name' => $data['user_name'],
        'user_ip' => $data['user_ip'],
        'user_email' => $data['user_email'],
        'user_is_guest' => isset($data['user_is_guest'])?$data['user_is_guest']:false,
        );
        return true;
    }

    private function loginByCookie($authRow)
    {
        $userObj = new User($authRow['uauth_user_id']);
        if ($row = $userObj->getProfileData()) {
            if ($row['credential_verified'] != applicationConstants::YES) {
                return false;
            }

            if ($row['credential_active'] != applicationConstants::YES) {
                return false;
            }

            $row['user_ip'] = CommonHelper::userIp();
            $this->setSession($row);
            return true;
        }
        return false;
    }

    public static function checkFcmDeviceTokenInDB($fcmDeviceToken)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL_USER_AUTH);
        $srch->addCondition(static::DB_TBL_UAUTH_PREFIX . 'fcm_id', '=', $fcmDeviceToken);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $db->fetch($rs);
    }

    public static function updateFcmDeviceToken(&$values, $where)
    {
        $db = FatApp::getDb();
        if ($db->updateFromArray(static::DB_TBL_USER_AUTH, $values, $where)) {
            return true;
        }
        return false;
    }

    public static function saveLoginToken(&$values)
    {
        $db = FatApp::getDb();
        if ($db->insertFromArray(static::DB_TBL_USER_AUTH, $values)) {
            return true;
        }

        return false;
    }

    public static function checkLoginTokenInDB($token)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL_USER_AUTH);
        $srch->addCondition(static::DB_TBL_UAUTH_PREFIX . 'token', '=', $token);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $db->fetch($rs);
    }

    public static function clearLoggedUserLoginCookie()
    {
        if (!isset($_COOKIE[static::YOKARTUSER_COOKIE_NAME])) {
            return false;
        }

        $db = FatApp::getDb();
        if (strlen($_COOKIE[static::YOKARTUSER_COOKIE_NAME])) {
            $db->deleteRecords(
                static::DB_TBL_USER_AUTH,
                array(
                'smt' => static::DB_TBL_UAUTH_PREFIX . 'token = ?',
                'vals' => array($_COOKIE[static::YOKARTUSER_COOKIE_NAME])
                )
            );
        }

        setcookie($_COOKIE[static::YOKARTUSER_COOKIE_NAME], '', time() - 3600, CONF_WEBROOT_URL);
        return true;
    }

    public static function isGuestUserLogged($ip = '')
    {
        if ($ip == '') {
            $ip = CommonHelper::getClientIp();
        }

        if (isset($_SESSION [static::SESSION_ELEMENT_NAME])
            /*&& $_SESSION [static::SESSION_ELEMENT_NAME] ['user_ip'] == $ip*/
            && $_SESSION [static::SESSION_ELEMENT_NAME] ['user_is_guest'] == true
            && is_numeric($_SESSION [static::SESSION_ELEMENT_NAME] ['user_id'])
            && 0 < $_SESSION [static::SESSION_ELEMENT_NAME] ['user_id']
        ) {
            return true;
        }
        return false;
    }

    public static function logout()
    {
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }

        include_once CONF_INSTALLATION_PATH . 'library/facebook/facebook.php';
        $facebook = new Facebook(
            array(
            'appId' => FatApp::getConfig("CONF_FACEBOOK_APP_ID"),
            'secret' => FatApp::getConfig("CONF_FACEBOOK_APP_SECRET"),
            )
        );

        $user = $facebook->getUser();

        if ($user) {
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_code']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_access_token']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_user_id']);
        }

        unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]);

        unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]);
        unset($_SESSION[UserAuthentication::AFFILIATE_SESSION_ELEMENT_NAME]);
        unset($_SESSION['activeTab']);
        unset($_SESSION['referer_page_url']);
        unset($_SESSION['registered_supplier']['id']);

        self::clearLoggedUserLoginCookie();
    }

    public static function isUserLogged($ip = '', $token = '')
    {
        if ($ip == '') {
            $ip = CommonHelper::getClientIp();
        }

        $token = empty($token)?CommonHelper::getAppToken():$token;

        if ($token != '' && static::doAppLogin($token)) {
            return true;
        }

        if (isset($_SESSION [static::SESSION_ELEMENT_NAME])
            /*&& $_SESSION [static::SESSION_ELEMENT_NAME] ['user_ip'] == $ip*/
            && $_SESSION [static::SESSION_ELEMENT_NAME] ['user_is_guest'] == false
            && is_numeric($_SESSION [static::SESSION_ELEMENT_NAME] ['user_id'])
            && 0 < $_SESSION [static::SESSION_ELEMENT_NAME] ['user_id']
        ) {
            return true;
        }

        if (static::doCookieLogin(false)) {
            return true;
        }

        return false;
    }

    public static function getLoggedUserAttribute($attr, $returnNullIfNotLogged = false)
    {
        if (! static::isUserLogged() && ! static::isGuestUserLogged()) {
            if ($returnNullIfNotLogged) {
                return null;
            }
            $message = Labels::getLabel('MSG_USER_NOT_LOGGED', CommonHelper::getLangId());
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }


        if (array_key_exists($attr, $_SESSION [static::SESSION_ELEMENT_NAME])) {
            return $_SESSION [static::SESSION_ELEMENT_NAME][$attr];
        }

        return User::getAttributesById($_SESSION[static::SESSION_ELEMENT_NAME]['user_id'], $attr);
    }

    public static function getLoggedUserId($returnZeroIfNotLogged = false)
    {
        return FatUtility::int(static::getLoggedUserAttribute('user_id', $returnZeroIfNotLogged));
    }

    public function getUserByEmail($email, $isActive = true, $isVerfied = true)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase(User::DB_TBL);
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', User::tblFld('id') . '=' . User::DB_TBL_CRED_PREFIX . 'user_id');
        $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'email', '=', $email);

        if (true === $isActive) {
            $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        } else {
            $srch->addFld(User::DB_TBL_CRED_PREFIX . 'active');
        }
        if (true === $isVerfied) {
            $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'verified', '=', applicationConstants::YES);
        } else {
            $srch->addFld(User::DB_TBL_CRED_PREFIX . 'verified');
        }

        $srch->addMultipleFields(
            array(
            User::tblFld('id'),
            User::tblFld('name'),
            User::DB_TBL_CRED_PREFIX . 'email',
            User::DB_TBL_CRED_PREFIX . 'password'
            )
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        if (!$row = $db->fetch($rs, User::tblFld('id'))) {
            $this->error = Labels::getLabel('ERR_INVALID_EMAIL_ADDRESS', $this->commonLangId);
            return false;
        }
        return $row;
    }

    public function getUserByEmailOrUserName($user, $isActive = true, $isVerfied = true, $addDeletedCheck = true)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase(User::DB_TBL);
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', User::tblFld('id') . '=' . User::DB_TBL_CRED_PREFIX . 'user_id');
        $cnd=$srch->addCondition(User::DB_TBL_CRED_PREFIX . 'username', '=', $user);
        $cnd->attachCondition(User::DB_TBL_CRED_PREFIX . 'email', '=', $user, 'OR');

        if (true === $isActive) {
            $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        } else {
            $srch->addFld(User::DB_TBL_CRED_PREFIX . 'active');
        }

        if (true === $isVerfied) {
            $srch->addCondition(User::DB_TBL_CRED_PREFIX . 'verified', '=', applicationConstants::YES);
        } else {
            $srch->addFld(User::DB_TBL_CRED_PREFIX . 'verified');
        }

        if (true === $addDeletedCheck) {
            $srch->addCondition(User::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        }

        $srch->addMultipleFields(
            array(
            User::tblFld('id'),
            User::tblFld('name'),
            User::tblFld('is_shipping_company'),
            User::tblFld('deleted'),
            User::DB_TBL_CRED_PREFIX . 'email',
            User::DB_TBL_CRED_PREFIX . 'password'
            )
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs, User::tblFld('id'))) {
            $this->error = Labels::getLabel('ERR_INVALID_USERNAME', $this->commonLangId);
            return false;
        }

        return $row;
    }

    public function checkUserPwdResetRequest($userId)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL_USER_PRR);
        $srch->addCondition(static::DB_TBL_UPR_PREFIX . 'user_id', '=', $userId);
        $srch->addCondition(static::DB_TBL_UPR_PREFIX . 'expiry', '>', date('Y-m-d H:i:s'));
        $srch->addFld(static::DB_TBL_UPR_PREFIX . 'user_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            return false;
        }
        $this->error = Labels::getLabel('ERR_RESET_PASSWORD_REQUEST_ALREADY_PLACED', $this->commonLangId);
        return true;
    }

    public function deleteOldPasswordResetRequest()
    {
        $db = FatApp::getDb();
        if (!$db->deleteRecords(static::DB_TBL_USER_PRR, array('smt'=>static::DB_TBL_UPR_PREFIX . 'expiry < ?','vals'=>array(date('Y-m-d H:i:s'))))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addPasswordResetRequest($data = array())
    {
        if (!isset($data['user_id']) || $data['user_id'] < 1 || strlen($data['token']) < 20) {
            return false;
        }
        $db = FatApp::getDb();
        if ($db->insertFromArray(
            static::DB_TBL_USER_PRR,
            array(
            static::DB_TBL_UPR_PREFIX . 'user_id' => intval($data['user_id']),
            static::DB_TBL_UPR_PREFIX . 'token' => $data['token'],
            static::DB_TBL_UPR_PREFIX . 'expiry' => date('Y-m-d H:i:s', strtotime("+1 DAY"))
            )
        )) {
            $db->deleteRecords(
                static::DB_TBL_USER_AUTH,
                array(
                'smt' => static::DB_TBL_UAUTH_PREFIX . 'user_id = ?',
                'vals' => array($data['user_id'])
                )
            );
            return true;
        }
        return false;
    }

    public function checkResetLink($uId, $token)
    {
        $uId = FatUtility::convertToType($uId, FatUtility::VAR_INT);
        $token = FatUtility::convertToType($token, FatUtility::VAR_STRING);
        if (intval($uId) < 1 || strlen($token) < 20) {
            $this->error = Labels::getLabel('ERR_INVALID_RESET_PASSWORD_REQUEST', $this->commonLangId);
            return false;
        }
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL_USER_PRR);
        $srch->addCondition(static::DB_TBL_UPR_PREFIX . 'user_id', '=', $uId);
        $srch->addCondition(static::DB_TBL_UPR_PREFIX . 'token', '=', $token);
        $srch->addCondition(static::DB_TBL_UPR_PREFIX . 'expiry', '>', date('Y-m-d H:i:s'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        if (!$row = $db->fetch($rs)) {
            $this->error = Labels::getLabel('ERR_LINK_IS_INVALID_OR_EXPIRED', $this->commonLangId);
            return false;
        }

        if ($row[static::DB_TBL_UPR_PREFIX.'user_id'] == $uId && $row[static::DB_TBL_UPR_PREFIX.'token'] === $token) {
            return true;
        }
        $this->error = Labels::getLabel('ERR_LINK_IS_INVALID_OR_EXPIRED', $this->commonLangId);
        return false;
    }

    public function resetUserPassword($userId, $pwd)
    {
        $userId = FatUtility::convertToType($userId, FatUtility::VAR_INT);
        if ($userId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }
        if (!empty($pwd)) {
            $user = new User($userId);
            if (!$user->resetPassword($pwd)) {
                $this->error = $user->getError();
                return false;
            }
            FatApp::getDb()->deleteRecords(static::DB_TBL_USER_PRR, array('smt' => static::DB_TBL_UPR_PREFIX . 'user_id =?', 'vals'=>array($userId)));
            return true;
        }
        $this->error = Labels::getLabel('ERR_INVALID_PASSWORD', $this->commonLangId);
        return false;
    }

    public static function checkLogin($redirect = true)
    {
        if (static::isUserLogged() || static::isGuestUserLogged()) {
            return true;
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $message = Labels::getLabel('MSG_Session_seems_to_be_expired', CommonHelper::getLangId());
            FatUtility::dieJsonError($message);
        }

        if (FatUtility::isAjaxCall()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Session_seems_to_be_expired', CommonHelper::getLangId()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $_SESSION['referer_page_url'] = CommonHelper::getCurrUrl();
        if ($redirect == true) {
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }

        return false;
    }


    public static function setSessionAffiliateRegistering($data = array())
    {
        $affiliateSessionElementName = UserAuthentication::AFFILIATE_SESSION_ELEMENT_NAME;

        if (empty($data)) {
            trigger_error("Paramaeters are required.", E_USER_ERROR);
        }

        $_SESSION[$affiliateSessionElementName]['affiliate_is_registering_now'] = 1;

        if (isset($data['user_id'])) {
            $_SESSION[$affiliateSessionElementName]['user_id'] = $data['user_id'];
        }

        /* if( isset($data['ua_id']) ){
        $_SESSION[$affiliateSessionElementName]['ua_id'] = $data['ua_id'];
        } */

        if (isset($data['affiliate_register_step_number'])) {
            $_SESSION[$affiliateSessionElementName]['affiliate_register_step_number'] = $data['affiliate_register_step_number'];
        }
        return true;
    }

    public static function getSessionAffiliateByKey($key)
    {
        $affiliateSessionElementName = UserAuthentication::AFFILIATE_SESSION_ELEMENT_NAME;
        return isset($_SESSION[$affiliateSessionElementName][$key]) ? $_SESSION[$affiliateSessionElementName][$key] : false;
    }
}
