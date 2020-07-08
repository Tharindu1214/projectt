<?php
class ProfileController extends AdminBaseController
{
    public $_adminId = 0;
    function __construct($action)
    {
        parent::__construct($action);
        if(0 == $this->_adminId ) {
            $this->_adminId = AdminAuthentication::getLoggedAdminId();
        }
        $this->_adminProfileObj = new AdminUsers($this->_adminId);
    }

    public function index()
    {
        $this->_template->addJs('js/jquery.form.js');
        $this->_template->addJs('js/cropper.js');
        $this->_template->addCss('css/cropper.css');
        $this->_template->render();
    }

    public function profileImageForm()
    {
        $imgFrm = $this->getImageForm();
        $this->set('imgFrm', $imgFrm);
        $this->_template->render(false, false);
    }

    public function profileInfoForm()
    {
        $imgFrm = $this->getImageForm();
        $admin_row = AdminUsers::getAttributesById($this->_adminId);
        $frm = $this->getProfileInfoForm();
        $frm->fill($admin_row);
        $this->set('imgFrm', $imgFrm);
        $this->set('frm', $frm);
        $this->set('admin_row', $admin_row);
        $this->set('clss', 'chg_pass');
        $this->_template->render(false, false);
    }

    public function updateProfileInfo()
    {
        $frm = $this->getProfileInfoForm();
        $post = FatApp::getPostedData();
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($_SESSION[AdminAuthentication::SESSION_ELEMENT_NAME]['admin_name']);
        $_SESSION[AdminAuthentication::SESSION_ELEMENT_NAME]['admin_name'] = $post['admin_name'];

        $this->_adminProfileObj->assignValues($post);
        if (!$this->_adminProfileObj->save()) {
            Message::addErrorMessage($this->_adminProfileObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* function updatePassword(){
    $pwdFrm = $this->getPwdFrm();
    $post = $pwdFrm->getFormDataFromArray(FatApp::getPostedData());
    if(!$pwdFrm->validate($post)){
    Message::addErrorMessage($pwdFrm->getValidationErrors());
    FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
    }

    if(!$curDbPassword = AdminUsers::getAttributesById($this->_adminId, 'admin_password')){
    Message::addErrorMessage($this->_adminProfileObj->getError());
    FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
    }

    $newPassword = UserAuthentication::encryptPassword(FatApp::getPostedData('new_password'));
    $currentPassword = UserAuthentication::encryptPassword(FatApp::getPostedData('current_password'));

    if($curDbPassword != $currentPassword){
    Message::addErrorMessage(Labels::getLabel('LBL_Your_current_Password_mis-matched!',$this->adminLangId));
    FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
    }

    $data = array( 'admin_password' => $newPassword);

    $this->_adminProfileObj->assignValues($data);
    if(!$this->_adminProfileObj->save()){
    Message::addErrorMessage($this->_adminProfileObj->getError());
    FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
    }

    Message::addMessage(Labels::getLabel('LBL_Password_Updated_Successfully',$this->adminLangId));
    FatApp::redirectUser(CommonHelper::generateUrl('profile','changePassword'));
    } */

    private function getProfileInfoForm()
    {
        $frm = new Form('frmProfileInfo');
        $frm->addHiddenField('', ' admin_id', $this->admin_id);
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Username', $this->adminLangId), 'admin_username', '');
        $fld->setUnique('tbl_admin', 'admin_username', 'admin_id', 'admin_id', 'admin_id');

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Email', $this->adminLangId), 'admin_email', '');
        $fld->setUnique('tbl_admin', 'admin_email', 'admin_id', 'admin_id', 'admin_id');

        $frm->addRequiredField(Labels::getLabel('LBL_Full_Name', $this->adminLangId), 'admin_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->adminLangId));
        return $frm;
    }

    private function getImageForm()
    {
        $frm = new Form('frmProfile', array('id'=>'frmProfile'));
        $frm->addFileUpload(Labels::getLabel('LBL_Profile_Picture', $this->adminLangId), 'user_profile_image', array('id'=>'user_profile_image','onchange'=>'popupImage(this)','accept'=>'image/*'));
        $frm->addHiddenField('', 'update_profile_img', Labels::getLabel('LBL_Update_Profile_Picture', $this->adminLangId), array('id'=>'update_profile_img'));
        $frm->addHiddenField('', 'rotate_left', Labels::getLabel('LBL_Rotate_Left', $this->adminLangId), array('id'=>'rotate_left'));
        $frm->addHiddenField('', 'rotate_right', Labels::getLabel('LBL_Rotate_Right', $this->adminLangId), array('id'=>'rotate_right'));
        $frm->addHiddenField('', 'remove_profile_img', 0, array('id'=>'remove_profile_img'));
        $frm->addHiddenField('', 'action', 'avatar', array('id'=>'avatar-action'));
        $frm->addHiddenField('', 'img_data', '', array('id'=>'img_data'));
        return $frm;
    }

    public function uploadProfileImage()
    {

        $post = FatApp::getPostedData();
        if(empty($post) ) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if($post['action'] == "demo_avatar") {
            if (!is_uploaded_file($_FILES['user_profile_image']['tmp_name'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();

            if(!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_ADMIN_PROFILE_IMAGE, $this->_adminId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $this->set('file', CommonHelper::generateFullUrl('Image', 'profileImage', array($this->_adminId)));
        }

        if($post['action'] == "avatar") {
            if (!is_uploaded_file($_FILES['user_profile_image']['tmp_name'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();

            if(!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_ADMIN_PROFILE_CROPED_IMAGE, $this->_adminId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $data = json_decode(stripslashes($post['img_data']));
            CommonHelper::crop($data, CONF_UPLOADS_PATH .$res, $this->adminLangId);
            $this->set('file', CommonHelper::generateFullUrl('Account', 'userProfileImage', array($this->_adminId,'croped',true)));
        }


        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProfileImage()
    {

        $fileHandlerObj = new AttachedFile();
        if(!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_ADMIN_PROFILE_IMAGE, $this->_adminId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if(!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_ADMIN_PROFILE_CROPED_IMAGE, $this->_adminId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));
        if ($action == 'index') {
            $nodes[] = array('title'=>$className);
        }
        else {
            // $nodes[] = array('title'=>$className, 'href'=>CommonHelper::generateUrl($urlController));
            $nodes[] = array('title'=>$action);
        }
        return $nodes;
    }

    function changePassword()
    {
        $pwdFrm = $this->getPwdFrm();
        $admin_row = AdminUsers::getAttributesById($this->_adminId);
        /* $imgForm = $this->getImageForm();
        $this->set('imgForm', $imgForm); */
        $this->set('pwdFrm', $pwdFrm);
        //$this->set('data', $admin_row);
        $this->set('clss', 'chg_pass');
        $this->_template->render();
    }

    function updatePassword()
    {
        $pwdFrm = $this->getPwdFrm();
        $post = $pwdFrm->getFormDataFromArray(FatApp::getPostedData());
        if(!$pwdFrm->validate($post)) {
            Message::addErrorMessage($pwdFrm->getValidationErrors());
            FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
        }

        if(!$curDbPassword = AdminUsers::getAttributesById($this->_adminId, 'admin_password')) {
            Message::addErrorMessage($this->_adminProfileObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
        }

        $newPassword = UserAuthentication::encryptPassword(FatApp::getPostedData('new_password'));
        $currentPassword = UserAuthentication::encryptPassword(FatApp::getPostedData('current_password'));

        if($curDbPassword != $currentPassword) {
            Message::addErrorMessage(Labels::getLabel('LBL_Your_current_Password_mis-matched!', $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
        }

        $data = array( 'admin_password' => $newPassword);

        $this->_adminProfileObj->assignValues($data);
        if(!$this->_adminProfileObj->save()) {
            Message::addErrorMessage($this->_adminProfileObj->getError());
            FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
        }

        Message::addMessage(Labels::getLabel('LBL_Password_Updated_Successfully', $this->adminLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('profile', 'changePassword'));
    }

    public function logout()
    {
        AdminAuthentication::clearLoggedAdminLoginCookie();
        session_destroy();
        Message::addMessage(Labels::getLabel('LBL_You_Are_Logged_Out_Successfully', $this->adminLangId));
        FatApplication::redirectUser(CommonHelper::generateUrl('adminGuest', 'loginForm'));
    }

    public function themeSetup()
    {
        $post = FatApp::getPostedData();
        $session_element_name = AdminAuthentication::SESSION_ELEMENT_NAME;
        $cookie_name = $session_element_name.'layout';
        if(setcookie($cookie_name, $post['layout'], time()+86400*30, CONF_WEBROOT_FRONT_URL)) {
            Message::addMessage(Labels::getLabel('LBL_Setting_Updated_Successfully', $this->adminLangId));
        } else {
            Message::addErrorMessage($this->str_invalid_request);
        }
        FatUtility::dieJsonError(Message::getHtml());
    }

    private function getPwdFrm()
    {
        $frm = new Form('getPwdFrm');
        $frm->setFormTagAttribute('action', CommonHelper::generateUrl('profile', 'updatePassword'));
        $frm->setFormTagAttribute('method', 'post');
        $frm->setFormTagAttribute('id', 'getPwdFrm');

        $curPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_Current_Password', $this->adminLangId),
            'current_password',
            '',
            array('id'=>'current_password')
        );
        $curPwd->requirements()->setRequired();

        $newPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_New_Password', $this->adminLangId),
            'new_password',
            '',
            array('id'=>'new_password')
        );
        $newPwd->requirements()->setRequired();

        $conNewPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_Confirm_New_Password', $this->adminLangId),
            'conf_new_password',
            '',
            array('id'=>'conf_new_password')
        );
        $conNewPwdReq = $conNewPwd->requirements();
        $conNewPwdReq->setRequired();
        $conNewPwdReq->setCompareWith('new_password', 'eq');
        $conNewPwdReq->setCustomErrorMessage(Labels::getLabel('LBL_Confirm_Password_Not_Matched!', $this->adminLangId));

        $frm->addSubmitButton(Labels::getLabel('LBL_Change', $this->adminLangId), 'btn_submit', Labels::getLabel('LBL_Change', $this->adminLangId), array('id'=>'btn_submit'));
        return $frm;
    }
}
