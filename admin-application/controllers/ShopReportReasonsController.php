<?php
class ShopReportReasonsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewShopReportReasons($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShopReportReasons($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShopReportReasons();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShopReportReasons();

        $srch = ShopReportReason::getSearchObject($this->adminLangId);

        $srch->addMultipleFields(array('reportreason.*' , 'reportreason_l.reportreason_title'));
        $srch->addOrder('reportreason_id', 'DESC');
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
        $this->objPrivilege->canViewShopReportReasons();

        $reasonId =  FatUtility::int($reasonId);

        $frm = $this->getForm($reasonId);

        if (0 < $reasonId) {
            $data = ShopReportReason::getAttributesById($reasonId, array('reportreason_id','reportreason_identifier'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('reportreason_id', $reasonId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShopReportReasons();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $reasonId = $post['reportreason_id'];
        unset($post['reportreason_id']);
        $record = new ShopReportReason($reasonId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($reasonId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = ShopReportReason::getAttributesByLangId($langId, $reasonId)) {
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
        $this->objPrivilege->canViewShopReportReasons();
        $reasonId = FatUtility::int($reasonId);
        $lang_id = FatUtility::int($lang_id);

        if ($reasonId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($reasonId, $lang_id);
        $langData = ShopReportReason::getAttributesByLangId($lang_id, $reasonId);

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
        $this->objPrivilege->canEditShopReportReasons();
        $post = FatApp::getPostedData();

        $reasonId = $post['reportreason_id'];
        $lang_id = $post['lang_id'];

        if ($reasonId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($reasonId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['reportreason_id']);
        unset($post['lang_id']);

        $data = array(
        'reportreasonlang_lang_id'=>$lang_id,
        'reportreasonlang_reportreason_id'=>$reasonId,
        'reportreason_title'=>$post['reportreason_title'],
        // 'reportreason_description'=>$post['reportreason_description']
        );

        $reasonObj = new ShopReportReason($reasonId);

        if (!$reasonObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($reasonObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = ShopReportReason::getAttributesByLangId($langId, $reasonId)) {
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
        $this->objPrivilege->canViewShopReportReasons();
        $reasonId =  FatUtility::int($reasonId);

        $frm = new Form('frmShopReportReason');
        $frm->addHiddenField('', 'reportreason_id', $reasonId);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Identifier', $this->adminLangId), 'reportreason_identifier');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($reasonId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewShopReportReasons();
        $frm = new Form('frmShopReportReasonLang');
        $frm->addHiddenField('', 'reportreason_id', $reasonId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Reason_Title', $this->adminLangId), 'reportreason_title');
        // $frm->addTextarea('Reason Description', 'reportreason_description');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditShopReportReasons();

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
        $this->objPrivilege->canEditShopReportReasons();
        $reasonIdsArr = FatUtility::int(FatApp::getPostedData('reportreason_ids'));

        if (empty($reasonIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($reasonIdsArr as $reasonId) {
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
        $obj = new ShopReportReason($reasonId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }
}
