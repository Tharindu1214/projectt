<?php
class OrderCancelReasonsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewOrderCancelReasons($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderCancelReasons($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewOrderCancelReasons();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewOrderCancelReasons();

        $srch = OrderCancelReason::getSearchObject($this->adminLangId);

        $srch->addMultipleFields(array('ocreason.*' , 'ocreason_l.ocreason_title'));
        $srch->addOrder('ocreason_id', 'DESC');
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
        $this->objPrivilege->canViewOrderCancelReasons();

        $reasonId =  FatUtility::int($reasonId);

        $frm = $this->getForm($reasonId);

        if (0 < $reasonId) {
            $data = OrderCancelReason::getAttributesById($reasonId, array('ocreason_id','ocreason_identifier'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('ocreason_id', $reasonId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditOrderCancelReasons();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $reasonId = $post['ocreason_id'];
        unset($post['ocreason_id']);
        $record = new OrderCancelReason($reasonId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($reasonId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = OrderCancelReason::getAttributesByLangId($langId, $reasonId)) {
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
        $this->objPrivilege->canViewOrderCancelReasons();
        $reasonId = FatUtility::int($reasonId);
        $lang_id = FatUtility::int($lang_id);

        if ($reasonId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($reasonId, $lang_id);
        $langData = OrderCancelReason::getAttributesByLangId($lang_id, $reasonId);

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
        $this->objPrivilege->canEditOrderCancelReasons();
        $post = FatApp::getPostedData();

        $reasonId = $post['ocreason_id'];
        $lang_id = $post['lang_id'];

        if ($reasonId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($reasonId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['ocreason_id']);
        unset($post['lang_id']);

        $data = array(
        'ocreasonlang_lang_id'=>$lang_id,
        'ocreasonlang_ocreason_id'=>$reasonId,
        'ocreason_title'=>$post['ocreason_title'],
        // 'ocreason_description'=>$post['ocreason_description']
        );

        $reasonObj = new OrderCancelReason($reasonId);

        if (!$reasonObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($reasonObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = OrderCancelReason::getAttributesByLangId($langId, $reasonId)) {
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
        $this->objPrivilege->canViewOrderCancelReasons();
        $reasonId =  FatUtility::int($reasonId);

        $frm = new Form('frmOrderCancelReason');
        $frm->addHiddenField('', 'ocreason_id', $reasonId);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Identifier', $this->adminLangId), 'ocreason_identifier');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($reasonId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewOrderCancelReasons();
        $frm = new Form('frmOrderCancelReasonLang');
        $frm->addHiddenField('', 'ocreason_id', $reasonId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Title', $this->adminLangId), 'ocreason_title');
        // $frm->addTextarea('Reason Description', 'ocreason_description');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditOrderCancelReasons();

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
        $this->objPrivilege->canEditOrderCancelReasons();
        $ocreasonIdsArr = FatUtility::int(FatApp::getPostedData('ocreason_ids'));

        if (empty($ocreasonIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($ocreasonIdsArr as $reasonId) {
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
        $obj = new OrderCancelReason($reasonId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }
}
