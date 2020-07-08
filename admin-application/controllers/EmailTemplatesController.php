<?php
class EmailTemplatesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('langForm','search','setup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewEmailTemplates($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditEmailTemplates($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->set("includeEditor", true);
    }

    public function index()
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frm = new Form('frmEtplsSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewEmailTemplates();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = EmailTemplates::getSearchObject($this->adminLangId);
        $srch->addOrder(EmailTemplates::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupBy(EmailTemplates::DB_TBL_PREFIX . 'code');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('etpl_code', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('etpl_name', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('etpl_subject', 'like', '%'.$post['keyword'].'%', 'OR');
        }

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
        $this->set('langId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditEmailTemplates();
        $data = FatApp::getPostedData();
        $lang_id = $data['lang_id'];
        $frm = $this->getLangForm($data['etpl_code'], $lang_id);
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $etplCode = $post['etpl_code'];

        $etplObj = new EmailTemplates($etplCode);
        $record =  $etplObj->getEtpl($etplCode, $lang_id);

        /* if($record == false){
        Message::addErrorMessage($this->str_invalid_request);
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $languages = Language::getAllNames();

        $data = array(
        'etpl_lang_id'=>$lang_id,
        'etpl_code'=>$etplCode,
        'etpl_name'=>$post['etpl_name'],
        'etpl_subject'=>$post['etpl_subject'],
        'etpl_body'=>$post['etpl_body'],
        );

        if (!$etplObj->addUpdateData($data)) {
            Message::addErrorMessage($etplObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getLangForm($etplCode = '', $lang_id = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();
        $frm = new Form('frmEtplLang');
        $frm->addHiddenField('', 'etpl_code', $etplCode);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->adminLangId), 'etpl_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Subject', $this->adminLangId), 'etpl_subject');
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Body', $this->adminLangId), 'etpl_body');
        $fld->requirements()->setRequired(true);
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Caption', $this->adminLangId), 'replacement_caption', '<h3>'.Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId).'</h3>');
        $frm->addHtml(Labels::getLabel('LBL_Replacement_Vars', $this->adminLangId), 'etpl_replacements', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function langForm($etplCode = '', $lang_id = 0)
    {
        $this->objPrivilege->canViewEmailTemplates();

        $lang_id = FatUtility::int($lang_id);

        if ($etplCode == '' || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($etplCode, $lang_id);
        $etplObj = new EmailTemplates($etplCode);

        $langData =  $etplObj->getEtpl($etplCode, $lang_id);

        if ($langData) {
            $langFrm->fill($langData);
        }
        if ($langData['etpl_replacements'] == '') {
            $etplData =  $etplObj->getEtpl($etplCode);
            $langFrm->getField('etpl_replacements')->value = $etplData['etpl_replacements'];
        }
        $this->set('languages', Language::getAllNames());
        $this->set('etplCode', $etplCode);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditEmailTemplates();
        $etplCode = FatApp::getPostedData('etplCode', FatUtility::VAR_STRING, '');
        if ($etplCode == '') {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $etplObj = new EmailTemplates($etplCode);
        $records =  $etplObj->getEtpl($etplCode);

        if ($records==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $status = ($records['etpl_status'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateEmailTplStatus($etplCode, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditEmailTemplates();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $etplCodesArr = FatApp::getPostedData('etpl_codes');
        if (empty($etplCodesArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($etplCodesArr as $etplCode) {
            if (empty($etplCode)) {
                continue;
            }

            $this->updateEmailTplStatus($etplCode, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateEmailTplStatus($etplCode, $status)
    {
        $status = FatUtility::int($status);
        if (empty($etplCode) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $etplObj = new EmailTemplates($etplCode);
        if (!$etplObj->activateEmailTemplate($status, $etplCode)) {
            Message::addErrorMessage($etplObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
