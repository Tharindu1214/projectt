<?php
class PolicyPointsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPolicyPoints($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPolicyPoints($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewPolicyPoints();
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewPolicyPoints();

        $srch = PolicyPoint::getSearchObject($this->adminLangId, false);

        $srch->addMultipleFields(array('pp.*' , 'pp_l.ppoint_title' ));
        $srch->addOrder('ppoint_active', 'desc');
        $srch->addOrder('ppoint_id', 'desc');
        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set("policyPointTypeArr", PolicyPoint::getPolicyPointTypesArr($this->adminLangId));
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }


    public function form($ppointId)
    {
        $this->objPrivilege->canViewPolicyPoints();

        $ppointId =  FatUtility::int($ppointId);

        $frm = $this->getForm($ppointId);

        if (0 < $ppointId) {
            $data = PolicyPoint::getAttributesById($ppointId, array('ppoint_id','ppoint_identifier','ppoint_type','ppoint_active'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('ppoint_id', $ppointId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditPolicyPoints();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $ppointId = $post['ppoint_id'];
        unset($post['ppoint_id']);
        if ($ppointId == 0) {
            $post['ppoint_added_on'] = date('Y-m-d H:i:s');
        }
        $record = new PolicyPoint($ppointId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($ppointId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = PolicyPoint::getAttributesByLangId($langId, $ppointId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $ppointId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('ppointId', $ppointId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($ppointId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewPolicyPoints();
        $ppointId = FatUtility::int($ppointId);
        $lang_id = FatUtility::int($lang_id);

        if ($ppointId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($ppointId, $lang_id);
        $langData = PolicyPoint::getAttributesByLangId($lang_id, $ppointId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('ppointId', $ppointId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditPolicyPoints();
        $post = FatApp::getPostedData();

        $ppointId = $post['ppoint_id'];
        $lang_id = $post['lang_id'];

        if ($ppointId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($ppointId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['ppoint_id']);
        unset($post['lang_id']);

        $data = array(
        'ppointlang_lang_id'=>$lang_id,
        'ppointlang_ppoint_id'=>$ppointId,
        'ppoint_title'=>$post['ppoint_title'],

        );

        $obj = new PolicyPoint($ppointId);

        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = PolicyPoint::getAttributesByLangId($langId, $ppointId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('ppointId', $ppointId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditPolicyPoints();
        $ppointId = FatApp::getPostedData('ppointId', FatUtility::VAR_INT, 0);
        if (0 >= $ppointId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = PolicyPoint::getAttributesById($ppointId, array('ppoint_id','ppoint_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['ppoint_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updatePolicyPointStatus($ppointId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditPolicyPoints();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $ppointIdsArr = FatUtility::int(FatApp::getPostedData('ppoint_ids'));
        if (empty($ppointIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($ppointIdsArr as $ppointId) {
            if (1 > $ppointId) {
                continue;
            }

            $this->updatePolicyPointStatus($ppointId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updatePolicyPointStatus($ppointId, $status)
    {
        $status = FatUtility::int($status);
        $ppointId = FatUtility::int($ppointId);
        if (1 > $ppointId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new PolicyPoint($ppointId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditPolicyPoints();

        $ppoint_id = FatApp::getPostedData('ppointId', FatUtility::VAR_INT, 0);
        if ($ppoint_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($ppoint_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditPolicyPoints();
        $ppointIdsArr = FatUtility::int(FatApp::getPostedData('ppoint_ids'));

        if (empty($ppointIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($ppointIdsArr as $ppoint_id) {
            if (1 > $ppoint_id) {
                continue;
            }
            $this->markAsDeleted($ppoint_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($ppoint_id)
    {
        $ppoint_id = FatUtility::int($ppoint_id);
        if (1 > $ppoint_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $ppointObj = new PolicyPoint($ppoint_id);
        if (!$ppointObj->canRecordMarkDelete($ppoint_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $ppointObj->assignValues(array(PolicyPoint::tblFld('deleted') => 1));
        if (!$ppointObj->save()) {
            Message::addErrorMessage($ppointObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function getForm($ppointId = 0)
    {
        $this->objPrivilege->canViewPolicyPoints();
        $ppointId =  FatUtility::int($ppointId);

        $frm = new Form('frmPolicyPoint');
        $frm->addHiddenField('', 'ppoint_id', $ppointId);
        $frm->addRequiredField(Labels::getLabel('LBL_Policy_Point_Identifier', $this->adminLangId), 'ppoint_identifier');

        $policyPointTypeArr = PolicyPoint::getPolicyPointTypesArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'ppoint_type', $policyPointTypeArr, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'ppoint_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($ppointId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewPolicyPoints();
        $frm = new Form('frmPolicyPointLang');
        $frm->addHiddenField('', 'ppoint_id', $ppointId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Policy_Point_Title', $this->adminLangId), 'ppoint_title');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
