<?php
class AdminAuthentication extends FatModel
{
    const SESSION_ELEMENT_NAME = 'yokartAdmin';
    const ADMIN_REMEMBER_ME_COOKIE_NAME = 'yokartAdmin_remember_me';
    public static $_instance;

    public function __construct()
    {
        $this->adminLangId = CommonHelper::getLangId();
    }

    public static function getInstance()
    {

        if(self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function isAdminLogged($ip = '')
    {
        if ($ip == '') {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SESSION[static::SESSION_ELEMENT_NAME]) && FatUtility::int($_SESSION[static::SESSION_ELEMENT_NAME]['admin_id']) > 0 /*&& $_SESSION[static::SESSION_ELEMENT_NAME]['admin_ip'] == $ip */) {
            return true;
        }

        return false;
    }

    public function login($username, $password, $ip)
    {
        $objUserAuthentication = new UserAuthentication();
        if ($objUserAuthentication->isBruteForceAttempt($ip, $username)) {
            $this->error = Labels::getLabel('MSG_Login_attempt_limit_exceeded._Please_try_after_some_time.', $this->adminLangId);
            return false;
        }

        $password = UserAuthentication::encryptPassword($password);

        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_admin');
        $srch->addCondition('admin_username', '=', $username);
        $srch->addCondition('admin_password', '=', $password);
        $rs = $srch->getResultSet();

        if (!$row = $db->fetch($rs)) {
            $objUserAuthentication->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('MSG_Invalid_Username_or_Password', $this->adminLangId);
            return false;
        }
        if (strtolower($row['admin_username']) != strtolower($username) || $row['admin_password'] != $password ) {
            $objUserAuthentication->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('MSG_Invalid_Username_or_Password', $this->adminLangId);
            return false;
        }
        if ($row['admin_active'] !== applicationConstants::ACTIVE ) {
            $objUserAuthentication->logFailedAttempt($ip, $username);
            $this->error = Labels::getLabel('MSG_Your_account_is_inactive.', $this->adminLangId);
            return false;
        }
        $row['admin_ip'] = $ip;
        $this->setAdminSession($row);

        /* clear failed login attempt for the user [ */
        $objUserAuthentication->clearFailedAttempt($ip, $username);
        /* ] */

        return true;
    }

    public function setAdminSession($row)
    {
        $_SESSION[static::SESSION_ELEMENT_NAME] = array(
        'admin_id'=>$row['admin_id'],
        'admin_name'=>$row['admin_name'],
        'admin_username'=>$row['admin_username'],
        'admin_ip'=>$row['admin_ip']
        );
    }

    public static function getLoggedAdminAttribute($key, $returnNullIfNotLogged = false)
    {
        if (!static::isAdminLogged()) {
            if ($returnNullIfNotLogged) {
                return null;
            }
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Your_session_seems_to_be_expired.', CommonHelper::getLangId()));
            }
            FatApp::redirectUser(CommonHelper::generateUrl());
        }

        return $_SESSION[static::SESSION_ELEMENT_NAME][$key];
    }

    public static function getLoggedAdminId()
    {
        return static::getLoggedAdminAttribute('admin_id', false);
    }

    public function checkAdminEmail( $email )
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = Labels::getLabel('MSG_Invalid_email_address!', $this->adminLangId);
            return false;
        }
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_admin');
        $srch->addCondition('admin_email', '=', $email);
        $srch->addMultipleFields(array('admin_id','admin_name','admin_email'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if(!$row = $db->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_Invalid_email_address!', $this->adminLangId);
            return false;
        }
        if($row['admin_email'] !== $email) {
            $this->error = Labels::getLabel('MSG_Invalid_email_address!', $this->adminLangId);
            return false;
        }
        return $row;
    }

    public function checkAdminPwdResetRequest($admin_id)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_admin_password_reset_requests');
        $srch->addCondition('aprr_admin_id', '=', $admin_id);
        $srch->addCondition('aprr_expiry', '>', date('Y-m-d H:i:s'));
        $srch->addFld('aprr_admin_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if(!$row = $db->fetch($rs)) {
            return false;
        }
        $this->error = Labels::getLabel('MSG_Your_request_to_reset_password_has_already_been_placed_within_last_24_hours._Please_check_your_emails_or_retry_after_24_hours_of_your_previous_request', $this->adminLangId);
        return true;
    }

    public function deleteOldPasswordResetRequest()
    {
        $db = FatApp::getDb();
        if(!$db->deleteRecords('tbl_admin_password_reset_requests', array('smt'=>'aprr_expiry < ?','vals'=>array(date('Y-m-d H:i:s'))))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addPasswordResetRequest($data = array())
    {
        if(!isset($data['admin_id']) || $data['admin_id'] < 1 || strlen($data['token']) < 20) {
            return false;
        }
        $db = FatApp::getDb();
        if($db->insertFromArray(
            'tbl_admin_password_reset_requests', array(
            'aprr_admin_id'=>intval($data['admin_id']),
            'aprr_token'=>$data['token'],
            'aprr_expiry'=>date('Y-m-d H:i:s', strtotime("+1 DAY"))
            )
        )) {
            $db->deleteRecords(
                'tbl_admin_auth_token', array(
                'smt' => 'admauth_admin_id = ?',
                'vals' => array($data['admin_id'])
                )
            );
            return true;
        }
        return false;
    }

    public function checkResetLink($aId, $token)
    {
        $aId = FatUtility::convertToType($aId, FatUtility::VAR_INT);
        $token = FatUtility::convertToType($token, FatUtility::VAR_STRING);
        if(intval($aId) < 1 || strlen($token) < 20) {
            $this->error = Labels::getLabel('MSG_Link_is_invalid_or_expired!', $this->adminLangId);
            return false;
        }
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_admin_password_reset_requests');
        $srch->addCondition('aprr_admin_id', '=', $aId);
        $srch->addCondition('aprr_token', '=', $token);
        $srch->addCondition('aprr_expiry', '>', date('Y-m-d H:i:s'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        if(!$row = $db->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_Link_is_invalid_or_expired!', $this->adminLangId);
            return false;
        }

        if($row['aprr_admin_id'] == $aId && $row['aprr_token'] === $token) {
            return true;
        }
        $this->error = Labels::getLabel('MSG_Link_is_invalid_or_expired!', $this->adminLangId);
        return false;
    }

    public function getAdminById($aId)
    {
        $aId = FatUtility::convertToType($aId, FatUtility::VAR_INT);
        if ($aId < 1) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->adminLangId);
            return false;
        }
        $db = FatApp::getDb();
        $aId = intval($aId);
        $srch = new SearchBase('tbl_admin');
        $srch->addCondition('admin_id', '=', $aId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $srch->getQuery();
        if(!$row = $db->fetch($rs)) {
            return false;
        }
        return $row;
    }

    public function changeAdminPwd($aId, $pwd)
    {
        $aId = FatUtility::convertToType($aId, FatUtility::VAR_INT);
        if ($aId < 1) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->adminLangId);
            return false;
        }

        $db = FatApp::getDb();
        $data = array('admin_password'=>$pwd);
        if($db->updateFromArray('tbl_admin', $data, array('smt'=>'admin_id=?', 'vals'=>array($aId)))) {
            $db->deleteRecords('tbl_admin_password_reset_requests', array('smt'=>'aprr_admin_id=?', 'vals'=>array($aId)));
            return true;
        }
        return false;
    }

    public function saveRememberLoginToken($values)
    {
        $db = FatApp::getDb();
        if($db->insertFromArray('tbl_admin_auth_token', $values)) {
            return true;
        }
        $this->error = $db->getError();
        return false;
    }

    public static function checkLoginTokenInDB($token)
    {
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_admin_auth_token');
        $srch->addCondition('admauth_token', '=', $token);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $db->fetch($rs);
    }

    public static function clearLoggedAdminLoginCookie()
    {
        if(!isset($_COOKIE[static::ADMIN_REMEMBER_ME_COOKIE_NAME])) {
            return false;
        }
        $db = FatApp::getDb();
        if(strlen($_COOKIE[static::ADMIN_REMEMBER_ME_COOKIE_NAME])) {
            $db->deleteRecords(
                'tbl_admin_auth_token', array(
                'smt' => 'admauth_token = ?',
                'vals' => array($_COOKIE[static::ADMIN_REMEMBER_ME_COOKIE_NAME])
                )
            );
        }
        setcookie(static::ADMIN_REMEMBER_ME_COOKIE_NAME, '', time() - 3600, CONF_WEBROOT_FRONT_URL);
        return true;
    }
}
