<?php
class FilterGroupsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewFilterGroups($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditFilterGroups($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }

    public function index() 
    {
        $this->objPrivilege->canViewFilterGroups();
        $search = $this->getSearchForm();                    
        $this->set("search", $search);    
        $this->_template->render();
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        $fld_submit =$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);    
        return $frm;
    }
    
    public function search()
    {
        $this->objPrivilege->canViewFilterGroups();
        
        $pagesize=FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);        
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $filterGroupObj = new FilterGroup();
        $srch = $filterGroupObj->getSearchObject();
        $srch->addFld('fg.*');
        
        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('fg.filtergroup_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('fgl.filtergroup_name', 'like', '%'.$post['keyword'].'%', 'OR');         
        }
        
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $srch->joinTable(
            FilterGroup::DB_TBL . '_lang', 'LEFT OUTER JOIN',
            'fgl.filtergrouplang_filtergroup_id = fg.filtergroup_id AND fgl.filtergrouplang_lang_id = ' . $this->adminLangId, 'fgl'
        );
        $srch->addMultipleFields(array("fgl.filtergroup_name"));
        
        $rs = $srch->getResultSet();
        
        $pageCount = $srch->pages();
        $records =array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);            
        }
        
        $this->set("arr_listing", $records);
        $this->set('pageCount', $pageCount);
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());        
        $this->_template->render(false, false);    
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditFilterGroups();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $filtergroup_id = $post['filtergroup_id'];
        unset($post['filtergroup_id']);
        
        $record = new FilterGroup($filtergroup_id);
        $record->assignValues($post);
        
        if (!$record->save()) {             
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $newTabLangId=0;    
        if($filtergroup_id>0) {
            $filterGroupId=$filtergroup_id;
            $languages=Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row=FilterGroup::getAttributesByLangId($langId, $filtergroup_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        }else{
            $filterGroupId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }    
        
        $this->set('msg', Labels::getLabel('LBL_Filter_Group_Setup_Successful', $this->adminLangId));
        $this->set('filterGroupId', $filterGroupId);
        $this->set('langId', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditFilterGroups();
        $post=FatApp::getPostedData();
        
        $filtergroup_id = FatUtility::int($post['filtergroup_id']);
        $lang_id = FatUtility::int($post['lang_id']);
        
        if($filtergroup_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $frm = $this->getLangForm($filtergroup_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['filtergroup_id']);
        unset($post['lang_id']);
        $data=array(
        'filtergrouplang_lang_id'=>$lang_id,
        'filtergrouplang_filtergroup_id'=>$filtergroup_id,
        'filtergroup_name'=>$post['filtergroup_name'],
        );
        
        $filterGroupObj=new FilterGroup($filtergroup_id);    
        if(!$filterGroupObj->updateLangData($lang_id, $data)) {            
            Message::addErrorMessage($filterGroupObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId=0;    
        $languages=Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row=FilterGroup::getAttributesByLangId($langId, $filtergroup_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }    
    
        $this->set('msg', Labels::getLabel('LBL_Filter_Group_Setup_Successful', $this->adminLangId));
        $this->set('filterGroupId', $filtergroup_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function form($filtergroup_id=0)
    {
        $this->objPrivilege->canEditFilterGroups();
        
        $filtergroup_id=FatUtility::int($filtergroup_id);
        $filterGroupsFrm = $this->getForm($filtergroup_id);

        if (0 < $filtergroup_id ) {
            $data = FilterGroup::getAttributesById($filtergroup_id, array('filtergroup_id','filtergroup_identifier','filtergroup_active'));            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $filterGroupsFrm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('filtergroup_id', $filtergroup_id);
        $this->set('filterGroupsFrm', $filterGroupsFrm);
        $this->_template->render(false, false);
    }
    
    private function getForm($filtergroup_id=0)
    {
        $this->objPrivilege->canEditFilterGroups();        
        $filtergroup_id=FatUtility::int($filtergroup_id);

        $action=Labels::getLabel('LBL_Add_New', $this->adminLangId);
        if($filtergroup_id>0) {
            $action=Labels::getLabel('LBL_Update', $this->adminLangId);
        }
        $filterGroupObj = new FilterGroup();                
        $frm = new Form('frmFilterGroups', array('id'=>'frmFilterGroups'));        
        $frm->addHiddenField('', 'filtergroup_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Filter_Group_Identifier', $this->adminLangId), 'filtergroup_identifier');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);    
        $frm->addSelectBox(Labels::getLabel('LBL_Filter_Group_Active', $this->adminLangId), 'filtergroup_active', $activeInactiveArr, '', array(), '');                
        $frm->addSubmitButton('', 'btn_submit', $action);        
        return $frm;
    }
    
    public function langForm($filtergroup_id=0,$lang_id=0)
    {        
        $this->objPrivilege->canEditFilterGroups();
        
        $filtergroup_id = FatUtility::int($filtergroup_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($filtergroup_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $filterGroupLangFrm = $this->getLangForm($filtergroup_id, $lang_id);
        $langData = FilterGroup::getAttributesByLangId($lang_id, $filtergroup_id);        
        
        if($langData) {
            $filterGroupLangFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('filtergroup_id', $filtergroup_id);
        $this->set('filtergroup_lang_id', $lang_id);
        $this->set('filterGroupLangFrm', $filterGroupLangFrm);
        $this->_template->render(false, false);
    }
    
    private function getLangForm($filtergroup_id=0,$lang_id=0)
    {            
        $frm = new Form('frmFilterGroupLang', array('id'=>'frmFilterGroupLang'));        
        $frm->addHiddenField('', 'filtergroup_id', $filtergroup_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Name', $this->adminLangId), 'filtergroup_name');        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditFilterGroups();
        
        $filtergroup_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($filtergroup_id < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $filterGroupObj = new FilterGroup($filtergroup_id);
        if(!$filterGroupObj->canRecordMarkDelete($filtergroup_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $filterGroupObj->assignValues(array(FilterGroup::tblFld('deleted') => 1));
        if(!$filterGroupObj->save()) {
            Message::addErrorMessage($filterGroupObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        FatUtility::dieJsonSuccess($this->str_delete_record);    
    }
}
