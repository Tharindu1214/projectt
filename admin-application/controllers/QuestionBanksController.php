<?php
class QuestionBanksController extends AdminBaseController
{
    private $canView;
    private $canEdit;    
    
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        } 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewQuestionBanks($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditQuestionBanks($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index()
    {
        $this->objPrivilege->canViewQuestionBanks();
        $frmSearch = $this->getSearchForm();                    
        $this->set("frmSearch", $frmSearch);    
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewQuestionBanks();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $srch = QuestionBanks::getSearchObject($this->adminLangId, false);
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        if(!empty($post['keyword'])) {
            $cond = $srch->addCondition('qb_l.qbank_name', 'like', '%'.$post['keyword'].'%');
            $cond->attachCondition('qb.qbank_identifier', 'like', '%'.$post['keyword'].'%');
        }
        $srch->addOrder('qb.qbank_active', 'desc');
        $srch->addOrder('qb_l.' . QuestionBanks::DB_TBL_PREFIX . 'name', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'qbank_id');    
        
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                        
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditQuestionBanks();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }

        $qbank_id = $post['qbank_id'];
        unset($post['qbank_id']);
        
        $record = new QuestionBanks($qbank_id);
        $record->assignValues($post);
        
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $newTabLangId=0;    
        if($qbank_id > 0) {
            $qbankId = $qbank_id;
            $languages = Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row = QuestionBanks::getAttributesByLangId($langId, $qbank_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        }else{
            $qbankId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }    
        
        
        $this->set('msg', $this->str_setup_successful);
        $this->set('qbankId', $qbankId);
        $this->set('langId', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function form($qbank_id)
    {    
        $this->objPrivilege->canViewQuestionBanks();
        
        $qbank_id = FatUtility::int($qbank_id);
        
        $frm = $this->getForm();        
        
        $data = array('qbank_id'=>$qbank_id);
        if($qbank_id > 0) {
            $data =  QuestionBanks::getAttributesById($qbank_id);            
            if($data ==  false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }            
        }    
        
        $frm->fill($data);
        
        $this->set('qbank_id', $qbank_id);        
        $this->set('frm', $frm);
        $this->set('languages', Language::getAllNames());        
        $this->_template->render(false, false);    
    }
    
    public function setupLang()
    {
        $this->objPrivilege->canEditQuestionBanks();
        $post = FatApp::getPostedData();
        
        $qbank_id = $post['qbank_id'];
        $lang_id = $post['lang_id'];
        
        if($qbank_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $frm = $this->getLangForm($qbank_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['qbank_id']);
        unset($post['lang_id']);
        $data = array(
        'qbanklang_lang_id'=>$lang_id,
        'qbanklang_qbank_id'=>$qbank_id,
        'qbank_name'=>$post['qbank_name']
        );
        
        $obj = new QuestionBanks($qbank_id);    
        if(!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());                
        }

        $newTabLangId = 0;    
        $languages = Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row = QuestionBanks::getAttributesByLangId($langId, $qbank_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }
        
        $this->set('msg', $this->str_setup_successful);
        $this->set('qbankId', $qbank_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($qbank_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewQuestionBanks();
        
        $qbank_id = FatUtility::int($qbank_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($qbank_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $langFrm = $this->getLangForm($qbank_id, $lang_id);
        $langData = QuestionBanks::getAttributesByLangId($lang_id, $qbank_id);        
        
        if($langData ) {
            $langFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('qbank_id', $qbank_id);
        $this->set('qbank_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);    
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditQuestionBanks();
        
        $qbank_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($qbank_id < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $data = QuestionBanks::getAttributesById($qbank_id);        
        if($data == false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $obj = new QuestionBanks($qbank_id);
        $obj->assignValues(array(QuestionBanks::tblFld('deleted') => 1));
        if(!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());        
        }
        
        FatUtility::dieJsonSuccess($this->str_delete_record);    
    }
    
    private function getSearchForm()
    {
        $this->objPrivilege->canViewQuestionBanks();
        $frm = new Form('frmQuestionBankSearch');        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');        
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);        
        return $frm;
    }
    
    private function getForm($qbank_id = 0)
    {
        
        $this->objPrivilege->canViewQuestionBanks();        
        $qbank_id = FatUtility::int($qbank_id);
        
        $frm = new Form('frmQuestionBank');        
        $frm->addHiddenField('', 'qbank_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'qbank_identifier');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'qbank_active', $activeInactiveArr);                
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
    
    private function getLangForm($qbank_id = 0,$lang_id = 0)
    {            
        $frm = new Form('frmQuestionBankLang');        
        $frm->addHiddenField('', 'qbank_id', $qbank_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Question_Bank_Name', $this->adminLangId), 'qbank_name');        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}    