<?php
class OrderReturnReasonsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewOrderReturnReasons($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderReturnReasons($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewOrderReturnReasons();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewOrderReturnReasons();

        $srch = OrderReturnReason::getSearchObject($this->adminLangId);

        $srch->addMultipleFields(array('orreason.*' , 'orreason_l.orreason_title'));
        $srch->addOrder('orreason_id', 'DESC');

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }


    public function form($reasonId)
    {
        $this->objPrivilege->canViewOrderReturnReasons();

        $reasonId =  FatUtility::int($reasonId);

        $frm = $this->getForm($reasonId);

        if (0 < $reasonId) {
            $data = OrderReturnReason::getAttributesById($reasonId, array('orreason_id','orreason_identifier'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('orreason_id', $reasonId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditOrderReturnReasons();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $reasonId = $post['orreason_id'];
        unset($post['orreason_id']);
        $record = new OrderReturnReason($reasonId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($reasonId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = OrderReturnReason::getAttributesByLangId($langId, $reasonId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $reasonId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('reasonId', $reasonId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($reasonId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewOrderReturnReasons();
        $reasonId = FatUtility::int($reasonId);
        $lang_id = FatUtility::int($lang_id);

        if ($reasonId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($reasonId, $lang_id);
        $langData = OrderReturnReason::getAttributesByLangId($lang_id, $reasonId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('reasonId', $reasonId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditOrderReturnReasons();
        $post = FatApp::getPostedData();

        $reasonId = $post['orreason_id'];
        $lang_id = $post['lang_id'];

        if ($reasonId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($reasonId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['orreason_id']);
        unset($post['lang_id']);

        $data = array(
        'orreasonlang_lang_id'=>$lang_id,
        'orreasonlang_orreason_id'=>$reasonId,
        'orreason_title'=>$post['orreason_title'],
        // 'orreason_description'=>$post['orreason_description']
        );

        $reasonObj = new OrderReturnReason($reasonId);

        if (!$reasonObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($reasonObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = OrderReturnReason::getAttributesByLangId($langId, $reasonId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('reasonId', $reasonId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($reasonId = 0)
    {
        $this->objPrivilege->canViewOrderReturnReasons();
        $reasonId =  FatUtility::int($reasonId);

        $frm = new Form('frmOrderReturnReason');
        $frm->addHiddenField('', 'orreason_id', $reasonId);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Identifier', $this->adminLangId), 'orreason_identifier');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($reasonId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewOrderReturnReasons();
        $frm = new Form('frmOrderReturnReasonLang');
        $frm->addHiddenField('', 'orreason_id', $reasonId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Title', $this->adminLangId), 'orreason_title');
        // $frm->addTextarea('Reason Description', 'orreason_description');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditOrderReturnReasons();

        $reasonId = FatApp::getPostedData('reasonId', FatUtility::VAR_INT, 0);
        if ($reasonId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($reasonId);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditOrderReturnReasons();
        $orreasonIdsArr = FatUtility::int(FatApp::getPostedData('orreason_ids'));

        if (empty($orreasonIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($orreasonIdsArr as $reasonId) {
            if (1 > $reasonId) {
                continue;
            }
            $this->markAsDeleted($reasonId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($reasonId)
    {
        $reasonId = FatUtility::int($reasonId);
        if (1 > $reasonId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new OrderReturnReason($reasonId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

}
