<?php
class DeletedUsersController extends AdminBaseController
{
    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewUsers($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditUsers($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewUsers();
        $frmSearch = $this->getDeletedUserSearchForm();
        $data = FatApp::getPostedData();
        if ($data) {
            $data['user_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewUsers();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getDeletedUserSearchForm();

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj(null, true, false);
        $srch->addCondition('user_deleted', '=', applicationConstants::YES);
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addOrder('credential_active', 'DESC');

        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, -1);
        if ($user_id > 0) {
            $srch->addCondition('user_id', '=', $user_id);
        } else {
            $keyword = FatApp::getPostedData('keyword', null, '');
            if (!empty($keyword)) {
                $cond = $srch->addCondition('uc.credential_username', '=', $keyword);
                $cond->attachCondition('uc.credential_email', 'like', '%'.$keyword.'%', 'OR');
                $cond->attachCondition('u.user_name', 'like', '%'. $keyword .'%');
            }
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_STRING, 0);

        switch ($type) {
            case User::USER_TYPE_SELLER:
                $srch->addCondition('u.user_is_supplier', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_BUYER:
                $srch->addCondition('u.user_is_buyer', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_ADVERTISER:
                $srch->addCondition('u.user_is_advertiser', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_AFFILIATE:
                $srch->addCondition('u.user_is_affiliate', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_BUYER_SELLER:
                $srch->addCondition('u.user_is_supplier', '=', applicationConstants::YES);
                $srch->addCondition('u.user_is_buyer', '=', applicationConstants::YES);
                break;
        }

        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);

        $user_regdate_from = FatApp::getPostedData('user_regdate_from', FatUtility::VAR_DATE, '');
        if (!empty($user_regdate_from)) {
            $srch->addCondition('user_regdate', '>=', $user_regdate_from. ' 00:00:00');
        }

        $user_regdate_to = FatApp::getPostedData('user_regdate_to', FatUtility::VAR_DATE, '');
        if (!empty($user_regdate_to)) {
            $srch->addCondition('user_regdate', '<=', $user_regdate_to. ' 23:59:59');
        }

        $srch->addMultipleFields(array('user_is_buyer', 'user_is_supplier','user_is_advertiser','user_is_affiliate', 'user_registered_initially_for'));

        /* $srch->addMultipleFields( array('user_id', 'user_name', 'user_phone', 'user_profile_info', 'user_regdate', 'user_is_buyer', 'credential_username', 'credential_email', 'credential_active', 'credential_verified') ); */

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        // echo $srch->getQuery();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'user_id');

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('canVerify', $this->objPrivilege->canVerifyUsers($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function restore()
    {
        $this->objPrivilege->canEditUsers();
        $post = FatApp::getPostedData();
        if ($post == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = FatUtility::int($post['user_id']);
        if (1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($user_id);
        $userObj->assignValues(array('user_deleted'=>applicationConstants::NO));
        if (!$userObj->save()) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getDeletedUserSearchForm()
    {
        $frm = new Form('frmDeletedUserSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Name_Or_Email', $this->adminLangId), 'keyword', '', array('id'=>'keyword','autocomplete'=>'off'));
        $keyword->setFieldTagAttribute('onKeyUp', 'usersAutocomplete(this)');

        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From', $this->adminLangId), 'user_regdate_from', '', array( 'readonly'=>'readonly'));
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To', $this->adminLangId), 'user_regdate_to', '', array( 'readonly'=>'readonly'));

        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'user_id');
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
