<?php
class ExtraAttributeGroupsController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewExtraAttributes($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditExtraAttributes($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }

    public function index() 
    {
        $this->objPrivilege->canViewExtraAttributes();
        $srchFrm = $this->getSearchForm();
        $this->set("frmSearch", $srchFrm);
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewExtraAttributes();
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);    
        $post = $searchForm->getFormDataFromArray($data);
        
        $extraAttrGroupObj = new ExtraAttributeGroup();
        $srch = $extraAttrGroupObj->getSearchObject();
        $srch->addFld('eag.*');
        
        if(!empty($post['keyword']) ) {
            $cnd = $srch->addCondition('eattrgroup_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('eattrgroup_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->joinTable(
            ExtraAttributeGroup::DB_TBL . '_lang', 'LEFT OUTER JOIN',
            'eattrgrouplang_eattrgroup_id = eattrgroup_id AND eattrgrouplang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array("eattrgroup_name"));
        
        $rs = $srch->getResultSet();
        
        $pageCount = $srch->pages();
        $records =array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);            
        }
        
        $this->set("arr_listing", $records);
        $this->set('pageCount', $pageCount);
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                        
        $this->_template->render(false, false);    
    }
    
    public function form( $eattrgroup_id = 0 )
    {
        $this->objPrivilege->canEditExtraAttributes();
        
        $eattrgroup_id=FatUtility::int($eattrgroup_id);
        $extraAttrGroupsFrm = $this->getForm($eattrgroup_id);
        if (0 < $eattrgroup_id ) {
            $data = ExtraAttributeGroup::getAttributesById($eattrgroup_id, array('eattrgroup_id','eattrgroup_identifier'));            
            if ($data === false ) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $extraAttrGroupsFrm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('extraAttrGroupsFrm', $extraAttrGroupsFrm);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditExtraAttributes();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::addHtml());
        }
        
        $eattrgroup_id = $post['eattrgroup_id'];
        unset($post['eattrgroup_id']);
        
        $record = new ExtraAttributeGroup($eattrgroup_id);
        $record->assignValues($post);
        
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::addHtml());
        }
        
        $newTabLangId=0;    
        if($eattrgroup_id > 0) { 
            $languages=Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row = ExtraAttributeGroup::getAttributesByLangId($langId, $eattrgroup_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        }else{
            $eattrgroup_id = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }    
        
        $this->set('msg', Labels::getLabel('LBL_Extra_Attribute_Group_Setup_Successful.', $this->adminLangId));
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('lang_id', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getForm($eattrgroup_id=0)
    {
        $this->objPrivilege->canEditExtraAttributes();        
        $eattrgroup_id=FatUtility::int($eattrgroup_id);
        
        $ExtraAttrGroupObj = new ExtraAttributeGroup();                
        $frm = new Form('frmExtraAttributeGroup', array('id'=>'frmExtraAttributeGroup'));        
        $frm->addHiddenField('', 'eattrgroup_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Extra_Attribute_Group_Identifier', $this->adminLangId), 'eattrgroup_identifier');                
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
    
    public function langForm($eattrgroup_id=0, $lang_id=0)
    {
        $this->objPrivilege->canEditExtraAttributes();
        
        $eattrgroup_id = FatUtility::int($eattrgroup_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($eattrgroup_id == 0 || $lang_id == 0 ) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $extraAttrGroupLangFrm = $this->getLangForm($eattrgroup_id, $lang_id);
        $langData = ExtraAttributeGroup::getAttributesByLangId($lang_id, $eattrgroup_id);        
        
        if($langData ) {
            $extraAttrGroupLangFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('eattrgroup_lang_id', $lang_id);
        $this->set('extraAttrGroupLangFrm', $extraAttrGroupLangFrm);
        $this->_template->render(false, false);
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditExtraAttributes();
        $post = FatApp::getPostedData();
        
        $eattrgroup_id = $post['eattrgroup_id'];
        $lang_id = $post['lang_id'];
        
        if($eattrgroup_id==0 || $lang_id == 0 ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $frm = $this->getLangForm($eattrgroup_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['eattrgroup_id']);
        unset($post['lang_id']);
        $data = array(
        'eattrgrouplang_eattrgroup_id'=>$eattrgroup_id,
        'eattrgrouplang_lang_id'=>$lang_id,
        'eattrgroup_name'=>$post['eattrgroup_name'],
        );
        
        $extraAttributeGroupObj = new ExtraAttributeGroup($eattrgroup_id);    
        if(!$extraAttributeGroupObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($extraAttributeGroupObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;    
        $languages = Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row=ExtraAttributeGroup::getAttributesByLangId($langId, $eattrgroup_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }    
    
        $this->set('msg', Labels::getLabel('LBL_Extra_Attribute_Group_Setup_Successful', $this->adminLangId));
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('lang_id', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditExtraAttributes();
        $eattrgroup_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($eattrgroup_id < 1 ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $extraAttrGroupObj = new ExtraAttributeGroup($eattrgroup_id);
        if(!$extraAttrGroupObj->canRecordMarkDelete($eattrgroup_id) ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $extraAttrGroupObj->assignValues(array(ExtraAttributeGroup::tblFld('deleted') => 1));
        if(!$extraAttrGroupObj->save() ) {
            Message::addErrorMessage($optionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }
    
    private function getLangForm($eattrgroup_id=0,$lang_id=0)
    {            
        $frm = new Form('frmExtraAttributeGroupLang', array('id'=>'frmExtraAttributeGroupLang'));        
        $frm->addHiddenField('', 'eattrgroup_id', $eattrgroup_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Extra_Attribute_Group_Name', $this->adminLangId), 'eattrgroup_name');        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));        
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit =$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);    
        return $frm;
    }
}
