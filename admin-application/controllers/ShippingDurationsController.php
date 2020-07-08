<?php
class ShippingDurationsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewShippingDurationLabels($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShippingDurationLabels($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShippingDurationLabels();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShippingDurationLabels();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = ShippingDurations::getSearchObject($this->adminLangId);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('sd.sduration_identifier', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('sd_l.sduration_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('sduration_id', 'DESC');

        $rs = $srch->getResultSet();
        $records =array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($sduration_id = 0)
    {
        $this->objPrivilege->canViewShippingDurationLabels();

        $sduration_id = FatUtility::int($sduration_id);
        $frm = $this->getForm();

        if (0 < $sduration_id) {
            $data = ShippingDurations::getAttributesById($sduration_id, array('sduration_id','sduration_identifier','sduration_from','sduration_to','sduration_days_or_weeks'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('sduration_id', $sduration_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShippingDurationLabels();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sduration_id = $post['sduration_id'];
        unset($post['sduration_id']);

        $record = new ShippingDurations($sduration_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($sduration_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = ShippingDurations::getAttributesByLangId($langId, $sduration_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $sduration_id = $record->getMainTableRecordId();
            $newTabLangId = $this->adminLangId;
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sdurationId', $sduration_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langform($sduration_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewShippingDurationLabels();

        $sduration_id = FatUtility::int($sduration_id);
        $lang_id = FatUtility::int($lang_id);

        if ($sduration_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($sduration_id, $lang_id);

        $langData = ShippingDurations::getAttributesByLangId($lang_id, $sduration_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('sduration_id', $sduration_id);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('languages', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditShippingDurationLabels();
        $post = FatApp::getPostedData();

        $sduration_id = $post['sduration_id'];
        $lang_id = $post['lang_id'];

        if ($sduration_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($sduration_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $data = array(
        'sdurationlang_sduration_id'=>$sduration_id,
        'sdurationlang_lang_id'=>$lang_id,
        'sduration_name'=>$post['sduration_name'],
        );

        $obj = new ShippingDurations($sduration_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = ShippingDurations::getAttributesByLangId($langId, $sduration_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sdurationId', $sduration_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditShippingDurationLabels();

        $sduration_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($sduration_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($sduration_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditShippingDurationLabels();
        $sdurationIdsArr = FatUtility::int(FatApp::getPostedData('sduration_ids'));

        if (empty($sdurationIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($sdurationIdsArr as $sduration_id) {
            if (1 > $sduration_id) {
                continue;
            }
            $this->markAsDeleted($sduration_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($sduration_id)
    {
        $sduration_id = FatUtility::int($sduration_id);
        if (1 > $sduration_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new ShippingDurations($sduration_id);
        if (!$obj->canRecordMarkDelete($sduration_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj->assignValues(array(ShippingDurations::tblFld('deleted') => 1));

        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function getSearchForm()
    {
        $frm = new Form('frmshipDurationSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm()
    {
        $this->objPrivilege->canViewShippingDurationLabels();

        $frm = new Form('frmShippingDuration');
        $frm->addHiddenField('', 'sduration_id', 0);
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'sduration_identifier');
        $fld->setUnique(ShippingDurations::DB_TBL, 'sduration_identifier', 'sduration_id', 'sduration_id', 'sduration_id');

        $arr = array();
        for ($i = 1; $i < 11 ; $i++) {
            $arr[$i] = $i;
        }

        $frm->addSelectbox(Labels::getLabel('LBL_From', $this->adminLangId), 'sduration_from', $arr, '', array(), '');
        $frm->addSelectbox(Labels::getLabel('LBL_To', $this->adminLangId), 'sduration_to', $arr, '', array(), '');
        $frm->addSelectbox(Labels::getLabel('LBL_Duration', $this->adminLangId), 'sduration_days_or_weeks', ShippingDurations::getShippingDurationDaysOrWeekArr($this->adminLangId), '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($sduration_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewShippingDurationLabels();

        $sduration_id = FatUtility::int($sduration_id);
        $lang_id = FatUtility::int($lang_id);

        $frm = new Form('frmShippingDurationLang');
        $frm->addHiddenField('', 'sduration_id', $sduration_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Label', $this->adminLangId), 'sduration_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
