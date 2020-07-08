<?php
class AdminGuestController extends FatController
{
    public function __construct($action)
    {
        parent::__construct($action);
        CommonHelper::initCommonVariables(true);
        //$this->adminLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        $this->adminLangId = CommonHelper::getLangId();
        if (AdminAuthentication::isAdminLogged()) {
            if(FatUtility::isAjaxCall() ) {
                Message::addErrorMessage(Labels::getLabel('MSG_You_are_already_logged_in', $this->adminLangId));
                $json['loggedIn'] = true;
                FatUtility::dieJsonError($json);
            }else{
                FatApp::redirectUser(CommonHelper::generateUrl('home'));
            }
        }
        $controllerName = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($controllerName));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $controllerName = ucfirst(FatUtility::dashed2Camel($urlController));

        $jsVariables = array(
        'processing' =>Labels::getLabel('LBL_Processing...', $this->adminLangId),
        'isMandatory' =>Labels::getLabel('LBL_is_mandatory', $this->adminLangId)
        );
		if (CommonHelper::demoUrl() == true) { 
            $this->_template->addCss('css/demo.css');
        }
        $this->set('isAdminLogged', AdminAuthentication::isAdminLogged());
        $this->set('controllerName', $controllerName);
        $this->set('jsVariables', $jsVariables);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('bodyClass', 'page--front');
    }

    public function loginForm()
    {

        $frm = $this->getLoginForm();
        //$forgotfrm = $this->getForgotForm();

        /* custom theming of login form[ */
        $frm->setValidatorJsObjectName('loginValidator');
        $frm->setFormTagAttribute('onsubmit', 'login(this, loginValidator); return(false);');
        //$frm->setFormTagAttribute ( 'class', '' );

        $frm->setFormTagAttribute('id', 'adminLoginForm');
        $frm->setFormTagAttribute('class', 'web_form');
        $frm->setRequiredStarPosition('none');
        $frm->setRequiredStarWith('none');
        $frm->setJsErrorDisplay(FORM::FORM_ERROR_TYPE_AFTER_FIELD);

        $vwfld = $frm->getField('username');
        $vwfld->addFieldTagAttribute('title', 'Username');
        $vwfld->addFieldTagAttribute('autocomplete', 'off');
        $vwfld->setRequiredStarWith('none');

        $vwfld = $frm->getField('password');
        $vwfld->addFieldTagAttribute('title', 'Password');
        $vwfld->addFieldTagAttribute('autocomplete', 'off');

        /* $vwfld = $frm->getField('rememberme');
        $vwfld->addFieldTagAttribute('id', 'rememberme');
        $vwfld->addFieldTagAttribute('title', 'Remember Me');
        $vwfld->addFieldTagAttribute('autocomplete', 'off'); */
        /* ] */

        /* custom theming of forgot form[ */
        /* 	$forgotfrm->setFormTagAttribute('id', 'frmForgot');
        $forgotfrm->setFormTagAttribute('class', 'web_form');
        $forgotfrm->setRequiredStarPosition('none');
        $forgotfrm->setValidatorJsObjectName('forgotValidator');
        $forgotfrm->setFormTagAttribute ( 'onsubmit', 'forgotPassword(this, forgotValidator); return false;');

        $email_fld = $forgotfrm->getField('admin_email');
        $email_fld->addFieldTagAttribute('title', 'Email Address');
        $email_fld->addFieldTagAttribute('autocomplete', 'off');
        $email_fld->setRequiredStarWith('none');*/
        /* ] */

        if($this->doCookieAdminLogin()) {
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }

        $this->set('frm', $frm);
        /* $this->set('frmForgot', $forgotfrm ); */

        $this->_template->render();
    }

    public function forgotPasswordForm()
    {

        $frm = $this->getLoginForm();
        $forgotfrm = $this->getForgotForm();


        /* custom theming of forgot form[ */
        $forgotfrm->setFormTagAttribute('id', 'frmForgot');
        $forgotfrm->setFormTagAttribute('class', 'web_form');
        $forgotfrm->setRequiredStarPosition('none');
        $forgotfrm->setValidatorJsObjectName('forgotValidator');
        $forgotfrm->setFormTagAttribute('onsubmit', 'forgotPassword(this, forgotValidator); return false;');

        $email_fld = $forgotfrm->getField('admin_email');
        $email_fld->addFieldTagAttribute('title', 'Email Address');
        $email_fld->addFieldTagAttribute('autocomplete', 'off');
        $email_fld->setRequiredStarWith('none');
        /* ] */

        if($this->doCookieAdminLogin()) {
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }

        $this->set('frm', $frm);
        $this->set('frmForgot', $forgotfrm);

        $this->_template->render();
    }



    public function login()
    {
        $username = FatApp::getPostedData('username');
        $password = FatApp::getPostedData('password');

        $adminAuthObj = AdminAuthentication::getInstance();
        if (!$adminAuthObj->login($username, $password, $_SERVER['REMOTE_ADDR'])) {
            Message::addErrorMessage($adminAuthObj->getError());
            $json['errorMsg'] = Message::getHtml();
            FatUtility::dieJsonError($json);
        }
        $success_message = Labels::getLabel('LBL_Login_Successful', $this->adminLangId);
        $rememberme = FatApp::getPostedData('rememberme', FatUtility::VAR_INT, 0);
        if ($rememberme == 1) {
            $this->setAdminLoginCookie();
        }

        /* Redirect to previous page[ */
        $redirectUrl = '';
        if(isset($_SESSION['admin_referer_page_url'])) {
            $redirectUrl = $_SESSION['admin_referer_page_url'];
            unset($_SESSION['admin_referer_page_url']);
        }

        if($redirectUrl == '' ) {
            $redirectUrl = CommonHelper::generateUrl('Home');
        }
        $this->set('redirectUrl', $redirectUrl);
        /* ] */

        Message::addMessage($success_message);
        $this->set('msg', $success_message);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function forgotPassword()
    {
        if(!FatUtility::isAjaxCall() ) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
        }
        $frm = $this->getForgotForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }
        $adminEmail = FatApp::getPostedData('admin_email');
        if(FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '') && !CommonHelper::verifyCaptcha()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Incorrect_Security_Code', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }

        $adminAuthObj = AdminAuthentication::getInstance();

        $admin = $adminAuthObj->checkAdminEmail($adminEmail);

        if(!$admin) {
            Message::addErrorMessage($adminAuthObj->getError());
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }
        if($adminAuthObj->checkAdminPwdResetRequest($admin['admin_id'])) {
            Message::addErrorMessage($adminAuthObj->getError());
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }

        $token = UserAuthentication::encryptPassword(FatUtility::getRandomString(20));

        $data = array('admin_id'=>$admin['admin_id'], 'token'=>$token);
        $reset_url = CommonHelper::generateFullUrl('adminGuest', 'resetPwd', array($admin['admin_id'], $token));
        $website_url_scheme = FatUtility::getUrlScheme();
        $website_url = CommonHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONTEND);
        $adminAuthObj->deleteOldPasswordResetRequest();
        if(!$adminAuthObj->addPasswordResetRequest($data)) {
            Message::addErrorMessage($adminAuthObj->getError());
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }

        if(!EmailHandler::sendMailTpl(
            $admin['admin_email'], 'admin_forgot_password', $this->adminLangId, array(
            '{reset_url}'          => $reset_url,
            '{site_domain}'     => CommonHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONTEND),
            '{user_full_name}'     => trim($admin['admin_name']),
            )
        )) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unable_to_send_email', $this->adminLangId));
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }

        $this->set('msg', Labels::getLabel('MSG_YOUR_PASSWORD_RESET_INSTRUCTIONS_TO_YOUR_EMAIL', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php', true, false);
    }

    function resetPwd($adminId = 0, $token = '')
    {
        /* die("We are currently working on this area..., for now, we have saved the sent email and token in table for this, but you cannot update the password for now <a href=".CommonHelper::generateFullUrl('','',array()).">Go to Admin Area</a>"); */
        $adminId = FatUtility::int($adminId);

        if($adminId < 1 || strlen(trim($token)) < 20) {
            Message::addErrorMessage(Labels::getLabel('MSG_Link_is_invalid_or_expired', $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('adminGuest', 'loginForm'));
        }

        $adminAuthObj = AdminAuthentication::getInstance();

        if(!$adminAuthObj->checkResetLink($adminId, trim($token))) {
            Message::addErrorMessage($adminAuthObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('adminGuest', 'loginForm'));
        }

        $frm = $this->getResetPwdForm($adminId, trim($token));

        $frm->setFormTagAttribute('id', 'frmResetPassword');
        $frm->setFormTagAttribute('class', 'web_form');
        $frm->setRequiredStarPosition('none');
        $frm->setValidatorJsObjectName('resetValidator');

        $frm->setFormTagAttribute("action", '');
        $frm->setFormTagAttribute('onsubmit', 'reset_password(this, resetValidator); return false;');

        $btn_fld = $frm->getField('btn_reset');
        $btn_fld->addFieldTagAttribute('id', 'btn_reset');

        $fld_np = $frm->getField('new_pwd');
        $fld_np->addFieldTagAttribute('title', 'New Password');
        $fld_np->addFieldTagAttribute('placeholder', ' Enter New Password');

        $fld_np->addFieldTagAttribute('autocomplete', 'off');
        $fld_np->addFieldTagAttribute('id', 'new_pwd');
        $fld_np->requirements()->setLength(4, 20);
        $fld_np->setRequiredStarWith('none');

        $fld_ncp = $frm->getField('confirm_pwd');
        $fld_ncp->addFieldTagAttribute('title', 'Confirm Password');
        $fld_ncp->addFieldTagAttribute('placeholder', ' Enter Confirm Password');

        $fld_ncp->addFieldTagAttribute('autocomplete', 'off');
        $fld_ncp->addFieldTagAttribute('id', 'confirm_pwd');
        $fld_ncp->setRequiredStarWith('none');

        $this->set('frmResetPassword', $frm);
        $this->_template->render();
    }

    public function resetPasswordSubmit()
    {
        if(!FatUtility::isAjaxCall()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $newPwd = FatApp::getPostedData('new_pwd');
        $confirmPwd = FatApp::getPostedData('confirm_pwd');
        $adminId = FatApp::getPostedData('apr_id', FatUtility::VAR_INT);
        $token = FatApp::getPostedData('token', FatUtility::VAR_STRING);

        if($adminId < 1 || strlen(trim($token)) < 20) {
            Message::addErrorMessage(Labels::getLabel('MSG_Request_is_Invalid_or_Expired', $this->adminLangId));
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }
        $frm = $this->getResetPwdForm($adminId, $token);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (!$frm->validate($post)) {
            FatUtility::dieJsonError($frm->getValidationErrors());
        }

        $adminAuthObj = AdminAuthentication::getInstance();

        if(!$adminAuthObj->checkResetLink($adminId, trim($token))) {
            Message::addErrorMessage($adminAuthObj->getError());
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }
        $admin_row = $adminAuthObj->getAdminById($adminId);

        $pwd = UserAuthentication::encryptPassword($newPwd);

        if($admin_row['admin_id'] != $adminId || !$adminAuthObj->changeAdminPwd($adminId, $pwd)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            $this->set('msg', Message::getHtml());
            $this->_template->render(false, false, 'json-error.php', true, false);
        }

        $arr_replacements = array(
        '{user_full_name}'    =>    trim($admin_row['admin_name']),
        '{login_link}'        =>    CommonHelper::generateFullUrl('adminGuest', 'loginForm', array())
        );
        EmailHandler::sendMailTpl($admin_row['admin_email'], 'user_admin_password_changed_successfully', $this->adminLangId, $arr_replacements);
        $this->set('msg', Labels::getLabel('MSG_Password_Changed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php', true, false);
    }

    private function setAdminLoginCookie()
    {
        $admin_id = AdminAuthentication::getLoggedAdminId();

        if($admin_id < 1) {
            return false;
        }
        $token = $this->generateLoginToken();
        $expiry = strtotime('+7 day');
        $values = array(
        'admauth_admin_id'=>$admin_id,
        'admauth_token'=>$token,
        'admauth_expiry'=>date('Y-m-d H:i:s', $expiry),
        'admauth_browser'=>$_SERVER['HTTP_USER_AGENT'],
        'admauth_last_access'=>date('Y-m-d H:i:s'),
        'admauth_last_ip'=>$_SERVER['REMOTE_ADDR'],
        );
        $adminAuthObj = AdminAuthentication::getInstance();
        if($adminAuthObj->saveRememberLoginToken($values)) {
            $cookie_name = AdminAuthentication::ADMIN_REMEMBER_ME_COOKIE_NAME;
            $cookres = setcookie($cookie_name, $token, $expiry, CONF_WEBROOT_FRONT_URL);
            return true;
        }
        return false;
    }

    private function doCookieAdminLogin()
    {
        $remember_me_cookie_name = AdminAuthentication::ADMIN_REMEMBER_ME_COOKIE_NAME;

        if(isset($_COOKIE[$remember_me_cookie_name])) {
            $token = $_COOKIE[$remember_me_cookie_name];
            $auth_row = false;
            $auth_row = AdminAuthentication::checkLoginTokenInDB($token);
            if(strlen($token) != 32 || empty($auth_row)) {
                AdminAuthentication::clearLoggedAdminLoginCookie();
                return false;
            }

            $browser = $_SERVER['HTTP_USER_AGENT'];
            $ip = $_SERVER['REMOTE_ADDR'];
            if(strtotime($auth_row['admauth_expiry']) < strtotime('now') || $auth_row['admauth_browser'] != $browser || $ip != $auth_row['admauth_last_ip']) {
                AdminAuthentication::clearLoggedAdminLoginCookie();
                return false;
            }
            if($this->loginById($auth_row['admauth_admin_id'])) {
                return true;
            }
            AdminAuthentication::clearLoggedAdminLoginCookie();
        }
        return false;
    }

    private function loginById($admin_id)
    {
        if(!$admin_id) { return false;
        }
        if($row = AdminUsers::getAttributesById($admin_id)) {
            $row['admin_ip'] = $_SERVER['REMOTE_ADDR'];
            $adminAuthObj = AdminAuthentication::getInstance();
            $adminAuthObj->setAdminSession($row);
            return true;
        }
        return false;
    }

    private function generateLoginToken()
    {
        do{
            $salt = substr(md5(microtime()), 5, 12);
            $token = md5($salt . microtime() . substr($salt, 5));
        }while(AdminAuthentication::checkLoginTokenInDB($token));
        return $token;
    }

    private function getLoginForm()
    {
        $userName ='';$pass = '';
        if (CommonHelper::demoUrl()) {
            $userName = 'admin';
            $pass = 'admin@123';
        }

        $frm = new Form('frmLogin');
        $frm->addTextBox('', 'username', $userName)->requirements()->setRequired();
        $frm->addPasswordField('', 'password', $pass)->requirements()->setRequired();
        $frm->addCheckBox('', 'rememberme', 1);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Sign_In', $this->adminLangId));
        return $frm;
    }

    private function getForgotForm()
    {
        $frm = new Form('adminFrmForgot');
        $frm->addEmailField('', 'admin_email', '', array('placeholder'=>Labels::getLabel('LBL_Enter_Your_Email_Address', $this->adminLangId)))->requirements()->setRequired();
        //$frm->addRequiredField('', 'security_code');
        if(FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '') != '' ) {
            $frm->addHtml('', 'security_code', '<div class="g-recaptcha" data-sitekey="'.FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '').'"></div>');
        }
        $frm->addSubmitButton('', 'btn_forgot', Labels::getLabel('LBL_Send_Reset_Pasword_Email', $this->adminLangId));
        return $frm;
    }

    private function getResetPwdForm($aId, $token)
    {
        $frm = new Form('frmResetPassword');
        $fld_np = $frm->addPasswordField('', 'new_pwd')->requirements()->setRequired();
        $fld_cp = $frm->addPasswordField('', 'confirm_pwd');
        $fld_cp->requirements()->setCompareWith('new_pwd', 'eq', '');
        $frm->addHiddenField('', 'apr_id', $aId, array('id'=>'apr_id'));
        $frm->addHiddenField('', 'token', $token, array('id'=>'token'));
        $frm->addSubmitButton('', 'btn_reset', Labels::getLabel('LBL_Reset_Pasword', $this->adminLangId));
        return $frm;
    }
}
