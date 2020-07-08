<?php
class SuccessStoriesController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewSuccessStories($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSuccessStories($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    
    public function index()
    {
        $this->objPrivilege->canViewSuccessStories();
        
        $srchFrm = $this->getSearchForm();
        $this->set("srchFrm", $srchFrm);    
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewSuccessStories();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $srch = SuccessStories::getSearchObject($this->adminLangId);
        $srch->addCondition('sstory_deleted', '=', 0);
        if(!empty($post['keyword'])) {
            $condition = $srch->addCondition('ss.sstory_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('ss_l.sstory_title', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        //	$srch->setPageSize($pagesize);
        $srch->addOrder('sstory_active', 'DESC');
        $srch->addOrder('sstory_display_order', 'asc');
    
        $rs = $srch->getResultSet();
        
        $records = array();
        if($rs) {
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
    
    public function form( $sstory_id = 0 )
    {
        $this->objPrivilege->canViewSuccessStories();
        
        $sstory_id = FatUtility::int($sstory_id);
        
        $frm = $this->getForm();
        $frm->fill(array( 'sstory_id' => $sstory_id ));

        if (0 < $sstory_id ) {
            $srch = SuccessStories::getSearchObject($this->adminLangId, false);
            $srch->addCondition('sstory_id', '=', $sstory_id);            
            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs);            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('sstory_id', $sstory_id);        
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditSuccessStories();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $sstory_id = FatUtility::int($post['sstory_id']);
        unset($post['sstory_id']);
        
        $record = new SuccessStories($sstory_id);
        
        if($sstory_id == 0) {
            $display_order = $record->getMaxOrder();
            $post['sstory_display_order'] = $display_order;
            $post['sstory_added_on'] = date('Y-m-d H:i:s');
        }        
        
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if($sstory_id > 0) {
            $sstory_id = $sstory_id;
            $languages = Language::getAllNames();
            foreach($languages as $langId =>$langName ){
                if(!$row = SuccessStories::getAttributesByLangId($langId, $sstory_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        }else{
            $sstory_id = $record->getMainTableRecordId();
            $newTabLangId = $this->adminLangId;
        }
        
        $this->set('msg', Labels::getLabel('LBL_Category_Setup_Successful', $this->adminLangId));
        $this->set('sstoryId', $sstory_id);    
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($sstory_id = 0, $lang_id = 0)
    {        
        $this->objPrivilege->canViewSuccessStories();
        
        $sstory_id = FatUtility::int($sstory_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($sstory_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $langFrm = $this->getLangForm();        
        $langData = SuccessStories::getAttributesByLangId($lang_id, $sstory_id);        
        
        $langData['sstory_id'] = $sstory_id;        
        $langData['lang_id'] = $lang_id;
        
        if($langData) {
            $langFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('sstory_id', $sstory_id);    
        $this->set('sstory_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }
    
    public function langSetup()    
    {
        $this->objPrivilege->canEditSuccessStories();
        $post = FatApp::getPostedData();

        $sstory_id = $post['sstory_id'];    
        $lang_id = $post['lang_id'];

        if($sstory_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['sstory_id']);    
        unset($post['lang_id']);
        $data = array(
        'sstorylang_lang_id'=>$lang_id,
        'sstorylang_sstory_id'=>$sstory_id,
        'sstory_title'=>$post['sstory_title'],
        'sstory_name'=>$post['sstory_name'],
        'sstory_content'=>$post['sstory_content'],
        );

        $obj = new SuccessStories($sstory_id);
        if(!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach($languages as $langId =>$langName ){
            if(!$row = SuccessStories::getAttributesByLangId($langId, $sstory_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
                
        $this->set('msg', $this->str_setup_successful);
        $this->set('sstoryId', $sstory_id);        
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function updateOrder()
    {
        $this->objPrivilege->canEditSuccessStories();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $obj = new SuccessStories();
            if(!$obj->updateOrder($post['stories'])) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
        }
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditSuccessStories();
        
        $sstory_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($sstory_id < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $res =     SuccessStories::getAttributesById($sstory_id, array('sstory_id'));    
        if($res == false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $obj = new SuccessStories($sstory_id);
        $obj->assignValues(array(SuccessStories::tblFld('deleted') => 1));
        if(!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());        
        }
        
        FatUtility::dieJsonSuccess($this->str_delete_record);    
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch');        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');    
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);        
        return $frm;
    }
    
    private function getForm() 
    {
        $frm = new Form('frmStories');
        $frm->addHiddenField('', 'sstory_id', 0);    
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'sstory_identifier');    
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Site_Domain', $this->adminLangId), 'sstory_site_domain');            
        $fld->htmlAfterField = Labels::getLabel('LBL_Example_:_sitename.com', $this->adminLangId);
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);        
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'sstory_active', $activeInactiveArr, '', array(), '');
        $frm->addCheckBox(Labels::getLabel('LBL_Featured', $this->adminLangId), 'sstory_featured', 1, array(), false, 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
    private function getLangForm()
    {        
        $frm = new Form('frmStories');        
        $frm->addHiddenField('', 'sstory_id');
        $frm->addHiddenField('', 'lang_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'sstory_title');
        $frm->addTextBox(Labels::getLabel('LBL_Name', $this->adminLangId), 'sstory_name');
        $frm->addTextArea(Labels::getLabel('LBL_Content', $this->adminLangId), 'sstory_content');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}    