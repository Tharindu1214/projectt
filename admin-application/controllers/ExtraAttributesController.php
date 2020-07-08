<?php
class ExtraAttributesController extends AdminBaseController
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

    public function index( $eattrgroup_id = 0 ) 
    {
        $this->objPrivilege->canViewExtraAttributes();
        $eattrgroup_id=FatUtility::int($eattrgroup_id);
        if($eattrgroup_id <= 0) {
            FatUtility::dieWithError($this->str_invalid_request_id); 
        }
        
        $extraAttrGroupdata = ExtraAttributeGroup::getAttributesById($eattrgroup_id, array('eattrgroup_id','eattrgroup_identifier'));            
        if ($extraAttrGroupdata === false) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }
        
        $frmSearch = $this->getSearchForm($eattrgroup_id);                    
        $this->set("frmSearch", $frmSearch); 
        $this->set("eattrgroup_id", $eattrgroup_id); 
        $this->set("extraAttrGroupdata", $extraAttrGroupdata); 
        $this->_template->render();
    }
    
    
    public function search()
    {
        $this->objPrivilege->canViewExtraAttributes();
        $data = FatApp::getPostedData();
        $eattrgroup_id=FatUtility::int($data['eattrgroup_id']);
        if($eattrgroup_id <= 0) {FatUtility::dieWithError($this->str_invalid_request_id); 
        }
        
        $searchForm = $this->getSearchForm($eattrgroup_id);
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $pagesize=FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);    
        $post = $searchForm->getFormDataFromArray($data);
        
        $extraAttrObj = new ExtraAttribute();
        $srch = $extraAttrObj->getSearchObject();
        $srch->addFld('ea.*');
        
        $srch->addCondition('eattribute_eattrgroup_id', '=', $eattrgroup_id);
        
        if(!empty($post['keyword']) ) {
            $cnd = $srch->addCondition('eattribute_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('eattribute_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $srch->joinTable(
            ExtraAttribute::DB_TBL . '_lang', 'LEFT OUTER JOIN',
            'eattributelang_eattribute_id = eattribute_id AND eattributelang_lang_id = ' .$this->adminLangId
        );
        $srch->addMultipleFields(array("eattribute_name"));
        
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
        $this->_template->render(false, false);    
    }
    
    public function form($eattrgroup_id,$eattribute_id=0)
    {
        $this->objPrivilege->canEditExtraAttributes();
        
        $eattrgroup_id=FatUtility::int($eattrgroup_id);
        if($eattrgroup_id <= 0) { FatUtility::dieWithError($this->str_invalid_request_id); 
        }
        
        $eattribute_id=FatUtility::int($eattribute_id);
        $extraAttributeFrm = $this->getForm($eattrgroup_id, $eattribute_id);

        if (0 < $eattribute_id ) {
            $extraAttrObj = new ExtraAttribute();
            $data = $extraAttrObj->getAttributesByIdAndGroupId($eattrgroup_id, $eattribute_id, array('eattribute_id','eattribute_eattrgroup_id','eattribute_identifier'));            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $extraAttributeFrm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('eattribute_id', $eattribute_id);
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('extraAttributeFrm', $extraAttributeFrm);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditExtraAttributes();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }

        $eattrgroup_id = FatUtility::int($post['eattribute_eattrgroup_id']);
        $eattribute_id = FatUtility::int($post['eattribute_id']);
        unset($post['eattribute_id']);        
                
        if (0 < $eattribute_id ) {
            $extraAttrObj = new ExtraAttribute();
            $data = $extraAttrObj->getAttributesByIdAndGroupId($eattrgroup_id, $eattribute_id, array('eattribute_id'));    
            if ($data === false) {
                Message::addErrorMessage($this->str_invalid_request_id);
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        
        $record = new ExtraAttribute($eattribute_id);
        $record->assignValues($post);
        
        if (!$record->save() ) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        
        $newTabLangId=0;    
        if($eattribute_id > 0 ) {
            $languages=Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row = ExtraAttribute::getAttributesByLangId($langId, $eattribute_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        } else {
            $eattribute_id = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }    
        
        $this->set('msg', Labels::getLabel('LBL_Attribute_Setup_Successful', $this->adminLangId));
        $this->set('eattrgroup_id', $eattrgroup_id);
        $this->set('eattribute_id', $eattribute_id);
        $this->set('lang_id', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getForm( $eattrgroup_id=0, $eattribute_id = 0 )
    {
        $this->objPrivilege->canEditExtraAttributes();        
        $eattrgroup_id=FatUtility::int($eattrgroup_id);
        $eattribute_id=FatUtility::int($eattribute_id);
                
        $frm = new Form('frmExtraAttribute', array('id'=>'frmExtraAttribute'));        
        $frm->addHiddenField('', 'eattribute_id', $eattribute_id);
        $frm->addHiddenField('', 'eattribute_eattrgroup_id', $eattrgroup_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Attribute_Identifier', $this->adminLangId), 'eattribute_identifier');        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
    
    public function langForm($eattribute_id = 0,$lang_id = 0)
    {
        $this->objPrivilege->canEditExtraAttributes();
        
        $eattribute_id = FatUtility::int($eattribute_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($eattribute_id==0 || $lang_id==0 ) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $data = ExtraAttribute::getAttributesById($eattribute_id);
        if($data == false ) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }
        $eattribute_eattrgroup_id = $data['eattribute_eattrgroup_id'];
        
        $extraAttributeLangFrm = $this->getLangForm($eattribute_id, $lang_id);
        $langData = ExtraAttribute::getAttributesByLangId($lang_id, $eattribute_id);        
        $langData['eattribute_eattrgroup_id']=$eattribute_eattrgroup_id;        
        if($langData ) {
            $extraAttributeLangFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('eattribute_id', $eattribute_id);
        $this->set('eattribute_eattrgroup_id', $eattribute_eattrgroup_id);
        $this->set('attribute_lang_id', $lang_id);
        $this->set('extraAttributeLangFrm', $extraAttributeLangFrm);
        $this->_template->render(false, false);
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditExtraAttributes();
        $frm = $this->getLangForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        $eattribute_id = FatUtility::int($post['eattribute_id']);
        $lang_id = FatUtility::int($post['lang_id']);
        $eattribute_eattrgroup_id = FatUtility::int($post['eattribute_eattrgroup_id']);
        
        if($eattribute_id==0 || $lang_id==0 ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $data=array(
        'eattributelang_lang_id'=>$lang_id,
        'eattributelang_eattribute_id'=>$eattribute_id,
        'eattribute_name'=>$post['eattribute_name'],
        );
        
        $extraAttrObj = new ExtraAttribute($eattribute_id);
        if(!$extraAttrObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($extraAttrObj->getError());
            FatUtility::dieWithError(Message::getHtml());            
        }

        $newTabLangId=0;    
        $languages=Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row = ExtraAttribute::getAttributesByLangId($langId, $eattribute_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }    
    
        $this->set('msg', Labels::getLabel('LBL_Attribute_Setup_Successful', $this->adminLangId));
        $this->set('eattribute_id', $eattribute_id);
        $this->set('lang_id', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditExtraAttributes();
        $eattribute_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($eattribute_id < 1 ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $extraAttrObj = new ExtraAttribute($eattribute_id);
        if(!$extraAttrObj->canDeleteRecord($eattribute_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());                
        }
        
        if(!$extraAttrObj->deleteRecord()) {
            Message::addErrorMessage($extraAttrObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        FatUtility::dieJsonSuccess($this->str_delete_record);        
    }
    
    private function getLangForm($eattribute_id=0,$lang_id=0)
    {
        $frm = new Form('frmExtraAttributeLang', array('id'=>'frmExtraAttributeLang'));        
        $frm->addHiddenField('', 'eattribute_id', $eattribute_id);
        $frm->addHiddenField('', 'eattribute_eattrgroup_id', 0);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Attribute_Name', $this->adminLangId), 'eattribute_name');        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
    private function getSearchForm($eattrgroup_id=0)
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));        
        $frm->addHiddenField('', 'eattrgroup_id', $eattrgroup_id);
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);            
        return $frm;
    }
}
?>
