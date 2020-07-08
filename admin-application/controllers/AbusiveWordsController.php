<?php
class AbusiveWordsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','search','setup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die('Invalid Action.');
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAbusiveWords($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAbusiveWords($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewAbusiveWords();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        //$this->set('languages', Language::getAllNames());
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewAbusiveWords();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Abusive::getSearchObject();
        $srch->joinTable('tbl_languages', 'inner join', 'abusive_lang_id = language_id and language_active = '.applicationConstants::ACTIVE);
        $srch->addOrder('aw.' . Abusive::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('aw.abusive_keyword', 'like', '%'.$post['keyword'].'%');
        }

        if ($post['lang_id'] > 0) {
            $cond = $srch->addCondition('aw.abusive_lang_id', '=', $post['lang_id']);
        }

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($abusive_id)
    {
        $this->objPrivilege->canViewAbusiveWords();

        $abusive_id = FatUtility::int($abusive_id);

        $frm = $this->getForm($abusive_id);

        $data = array('abusive_id'=>$abusive_id);
        if ($abusive_id > 0) {
            $data =  Abusive::getAttributesById($abusive_id);
            if ($data ==  false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
        }

        $frm->fill($data);

        $this->set('abusive_id', $abusive_id);
        $this->set('frm', $frm);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditAbusiveWords();
        $data = FatApp::getPostedData();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $abusive_id = FatUtility::int($post['abusive_id']);
        unset($post['abusive_id']);

        $record = new Abusive($abusive_id);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', 'Set up successful');
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditAbusiveWords();

        $abusive_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($abusive_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data =  Abusive::getAttributesById($abusive_id);
        if ($data ==  false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($abusive_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditAbusiveWords();
        $abusiveIdsArr = FatUtility::int(FatApp::getPostedData('abusive_ids'));

        if (empty($abusiveIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($abusiveIdsArr as $abusiveId) {
            $data =  Abusive::getAttributesById($abusiveId);
            if (1 > $abusiveId || false === $data) {
                continue;
            }

            $this->markAsDeleted($abusiveId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($abusiveId)
    {
        $abusiveId = FatUtility::int($abusiveId);
        if (1 > $abusiveId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new Abusive($abusiveId);
        if (!$obj->deleteRecord(false)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewAbusiveWords();
        $frm = new Form('frmWordSearch');
        $f1 = $frm->addTextBox('Keyword', 'keyword', '');
        $languages = Language::getAllNames();
        $frm->addSelectBox('Language', 'lang_id', array(0=>Labels::getLabel('LBL_Does_not_Matter', $this->adminLangId))+$languages, '', array(), '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', 'Search');
        $fld_cancel = $frm->addButton("", "btn_clear", "Clear Search");
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm($abusiveId = 0)
    {
        $this->objPrivilege->canViewAbusiveWords();
        $frm = new Form('frmAbusiveWord');
        $frm->addHiddenField('', 'abusive_id', $abusiveId);
        $languages = Language::getAllNames();
        $frm->addSelectBox('Language', 'abusive_lang_id', $languages, '', array(), '');
        $frm->addTextbox('Keyword', 'abusive_keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
