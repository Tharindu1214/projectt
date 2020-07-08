<?php
class AdminUsersController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAdminUsers($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAdminUsers($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function createProcedures()
    {
        $db = FatApp::getDb();
        $con = $db->getConnectionObject();
        $queries = array(
        "DROP FUNCTION IF EXISTS `GETBLOGCATCODE`",
        "CREATE FUNCTION `GETBLOGCATCODE`(`id` INT) RETURNS varchar(255) CHARSET utf8
			BEGIN
				DECLARE code VARCHAR(255);
				DECLARE catid INT(11);

				SET catid = id;
				SET code = '';
				WHILE catid > 0 DO
					SET code = CONCAT(RIGHT(CONCAT('000000', catid), 6), '_', code);
					SELECT bpcategory_parent INTO catid FROM tbl_blog_post_categories WHERE bpcategory_id = catid;
				END WHILE;
				RETURN code;
			END",
        "DROP FUNCTION IF EXISTS `GETCATCODE`",
        "CREATE FUNCTION `GETCATCODE`(`id` INT) RETURNS varchar(255) CHARSET utf8
			BEGIN
				DECLARE code VARCHAR(255);
				DECLARE catid INT(11);

				SET catid = id;
				SET code = '';
				WHILE catid > 0 DO
					SET code = CONCAT(RIGHT(CONCAT('000000', catid), 6), '_', code);
					SELECT prodcat_parent INTO catid FROM tbl_product_categories WHERE prodcat_id = catid;
				END WHILE;
				RETURN code;
			END",
        "DROP FUNCTION IF EXISTS `GETCATORDERCODE`",
        "CREATE FUNCTION `GETCATORDERCODE`(`id` INTEGER) RETURNS varchar(255) CHARSET utf8
			BEGIN
				DECLARE code VARCHAR(255);
				DECLARE catid INT(11);
				DECLARE myorder INT(11);
				SET catid = id;
				SET code = '';
				set myorder = 0;
				WHILE catid > 0 DO
					SELECT prodcat_parent, prodcat_display_order  INTO catid, myorder FROM tbl_product_categories WHERE prodcat_id = catid;
					SET code = CONCAT(RIGHT(CONCAT('000000', myorder), 6), code);
				END WHILE;
				RETURN code;
			END",
        "DROP FUNCTION IF EXISTS `GETBLOGCATORDERCODE`",
        "CREATE FUNCTION `GETBLOGCATORDERCODE`(`id` INT) RETURNS varchar(500) CHARSET utf8
			BEGIN
				DECLARE code VARCHAR(255);
				DECLARE catid INT(11);
				DECLARE myorder INT(11);
				SET catid = id;
				SET code = '';
				set myorder = 0;
				WHILE catid > 0 DO
					SELECT bpcategory_parent, bpcategory_display_order  INTO catid, myorder FROM tbl_blog_post_categories WHERE bpcategory_id = catid;
					SET code = CONCAT(RIGHT(CONCAT('000000', myorder), 6), code);
				END WHILE;
				RETURN code;
			END"
        );

        foreach ($queries as $qry) {
            if (!$con->query($qry)) {
                die($con->error);
            }
        }
        echo 'Created All the Procedures.';
    }

    public function index()
    {
        $this->objPrivilege->canViewAdminUsers();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewAdminUsers();

        $srch = AdminUsers::getSearchObject(false);

        $srch->addFld('*');
        $srch->addOrder('admin_id', 'DESC');
        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);

        $this->set('recordCount', $srch->recordCount());
        $this->set('adminLoggedInId', $this->admin_id);

        $this->_template->render(false, false);
    }

    public function form($adminId = 0)
    {
        $this->objPrivilege->canViewAdminUsers();

        $adminId =  FatUtility::int($adminId);

        $frm = $this->getForm($adminId);

        if (0 < $adminId) {
            $data = AdminUsers::getAttributesById($adminId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('admin_id', $adminId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditAdminUsers();

        $post = FatApp::getPostedData();

        $adminId = FatUtility::int($post['admin_id']);

        $frm = $this->getForm($adminId);
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['admin_id']);
        $record = new AdminUsers($adminId);
        if ($adminId == 0) {
            $password = $post['password'];
            $encryptedPassword = UserAuthentication::encryptPassword($password);
            $post['admin_password'] = $encryptedPassword;
        }

        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('adminId', $adminId);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function changePassword($adminId = 0)
    {
        $this->objPrivilege->canViewAdminUsers();
        $adminId =  FatUtility::int($adminId);
        $frm = $this->getChangePasswordForm($adminId);

        if (0 >= $adminId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $data = AdminUsers::getAttributesById($adminId);

        $this->set('admin_id', $adminId);
        $this->set('adminProfile', $data);
        $this->set('frm', $frm);

        $this->_template->render(false, false);
    }

    public function setupChangePassword()
    {
        $this->objPrivilege->canEditAdminUsers();

        $post = FatApp::getPostedData();
        $adminId = FatUtility::int($post['admin_id']);
        unset($post['admin_id']);

        if (0 >= $adminId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getChangePasswordForm($adminId);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $record = new AdminUsers($adminId);

        $password = $post['password'];
        $encryptedPassword = UserAuthentication::encryptPassword($password);
        $post['admin_password'] = $encryptedPassword;

        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Password_Changed_Successfully', $this->adminLangId));
        $this->set('adminId', $adminId);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditAdminUsers();
        $adminId = FatApp::getPostedData('adminId', FatUtility::VAR_INT, 0);
        if (0 >= $adminId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = AdminUsers::getAttributesById($adminId, array('admin_id','admin_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status=($data['admin_active'] == applicationConstants::ACTIVE)?0:1;

        $this->updateAdminUserStatus($adminId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditAdminUsers();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $adminIdsArr = FatUtility::int(FatApp::getPostedData('admin_ids'));
        if (empty($adminIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($adminIdsArr as $adminId) {
            if (1 > $adminId) {
                continue;
            }

            $this->updateAdminUserStatus($adminId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateAdminUserStatus($adminId, $status)
    {
        $status = FatUtility::int($status);
        $adminId = FatUtility::int($adminId);
        if (1 > $adminId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $adminObj = new AdminUsers($adminId);
        if (!$adminObj->changeStatus($status)) {
            Message::addErrorMessage($adminObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function permissions($adminId = 0)
    {
        $this->objPrivilege->canViewAdminPermissions();
        $adminId = FatUtility::int($adminId);
        if (1 > $adminId || $adminId==1 || $adminId==$this->admin_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatApp::redirectUser(CommonHelper::generateUrl('adminUsers'));
        }
        $frm = $this->searchForm();
        $allAccessfrm = $this->getAllAccessForm();

        $data = AdminUsers::getAttributesById($adminId);


        $frm->fill(array('admin_id'=>$adminId));

        $this->set('admin_id', $adminId);
        $this->set('frm', $frm);
        $this->set('allAccessfrm', $allAccessfrm);
        $this->set('data', $data);
        $this->_template->render();
    }

    public function roles()
    {
        $this->objPrivilege->canViewAdminPermissions();
        $frmSearch = $this->searchForm();
        $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());
        $adminId = FatUtility::int($post['admin_id']);

        $userData = array();
        if ($adminId > 0) {
            $userData = AdminUsers::getUserPermissions($adminId);
        }

        $permissionModules = AdminPrivilege::getPermissionModulesArr();
        /* $permissionModules = array(0 => Labels::getLabel('MSG_All_Modules',$this->adminLangId)) + $permissionModules; */
        $this->set('arr_listing', $permissionModules);
        $this->set('userData', $userData);
        $this->set('canViewAdminPermissions', $this->objPrivilege->canViewAdminPermissions(AdminAuthentication::getLoggedAdminId(), true));
        $this->_template->render(false, false);
    }

    public function updatePermission($moduleId, $permission)
    {
        $this->objPrivilege->canEditAdminPermissions();

        $moduleId = FatUtility::int($moduleId);
        $permission = FatUtility::int($permission);

        $frmSearch = $this->searchForm();
        $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());

        $adminId = FatUtility::int($post['admin_id']);

        if (2 > $adminId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $data = array(
        'admperm_admin_id'=>$adminId,
        'admperm_section_id'=>$moduleId,
        'admperm_value'=>$permission,
        );
        $obj = new AdminUsers();
        if ($moduleId == 0) {
            if (!$obj->updatePermissions($data, true)) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            $permissionModules = AdminPrivilege::getPermissionModulesArr();
            $permissionArr = AdminPrivilege::getPermissionArr();
            if (!array_key_exists($moduleId, $permissionModules) || !array_key_exists($permission, $permissionArr)) {
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieJsonError(Message::getHtml());
            }
            if (!$obj->updatePermissions($data)) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->adminLangId));
        $this->set('moduleId', $moduleId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function searchForm()
    {
        $frm = new Form('frmAdminSrchFrm');
        $frm->addHiddenField('', 'admin_id');
        return $frm;
    }

    private function getForm($adminId = 0)
    {
        $this->objPrivilege->canViewAdminUsers();
        $adminId =  FatUtility::int($adminId);

        $frm = new Form('frmAdminUser');
        $frm->addHiddenField('', 'admin_id', $adminId);
        $frm->addRequiredField(Labels::getLabel('LBL_Full_Name', $this->adminLangId), 'admin_name');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Username', $this->adminLangId), 'admin_username', '', array('id'=>'admin_username'));
        $fld->setUnique(AdminUsers::DB_TBL, AdminUsers::DB_TBL_PREFIX.'username', 'admin_id', 'admin_id', 'admin_id');
        $fld->requirements()->setRequired();
        $fld->requirements()->setUsername();
        $emailFld = $frm->addRequiredField(Labels::getLabel('LBL_Email', $this->adminLangId), 'admin_email', '', array('id'=>'admin_username'));
        $emailFld->setUnique(AdminUsers::DB_TBL, AdminUsers::DB_TBL_PREFIX.'email', 'admin_id', 'admin_id', 'admin_id');

        if ($adminId == 0) {
            $fld=$frm->addPasswordField(Labels::getLabel('LBL_Password', $this->adminLangId), 'password');
            $fld->requirements()->setRequired();
            $fld->requirements()->setPassword();
            $fld=$frm->addPasswordField(Labels::getLabel('LBL_Confirm_Password', $this->adminLangId), 'confirm_password');
            $fld->requirements()->setRequired();
            $fld->requirements()->setCompareWith('password', 'eq', '');
        }
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        if ($adminId != 1) {
            $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'admin_active', $activeInactiveArr, '', array(), '');
        }

        $frm->addCheckBox(Labels::getLabel('LBL_Send_Email_Notification', $this->adminLangId), 'admin_email_notification', applicationConstants::YES, array(), false, applicationConstants::NO);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getAllAccessForm()
    {
        $this->objPrivilege->canViewAdminUsers();
        $permissionArr = AdminPrivilege::getPermissionArr();
        $frm = new Form('frmAllAccess');
        $frm->setFormTagAttribute('class', 'web_form form_horizontal');
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Select_permission_for_all_modules', $this->adminLangId), 'permissionForAll', $permissionArr, '', array('class'=>'permissionForAll'), Labels::getLabel('LBL_Select', $this->adminLangId));
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply_to_All', $this->adminLangId), array('onclick'=>'updatePermission(0);return false;'));
        return $frm;
    }

    private function getChangePasswordForm($adminId)
    {
        $frm=new Form('frmAdminUserChangePassword');
        $frm->addHiddenField('', 'admin_id', $adminId);
        $fld=$frm->addPasswordField(Labels::getLabel('LBL_New_Password', $this->adminLangId), 'password');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setLength(4, 20);
        $fld=$frm->addPasswordField(Labels::getLabel('LBL_Confirm_Password', $this->adminLangId), 'confirm_password');
        $fld->requirements()->setRequired();
        $fld->requirements()->setCompareWith('password', 'eq', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
