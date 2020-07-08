<?php
class ManualShippingApiController extends AdminBaseController
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
        $this->canView = $this->objPrivilege->canViewManualShippingApi($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditManualShippingApi($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index()
    {
        $this->objPrivilege->canViewManualShippingApi();
        $frmSearch = $this->getSearchForm();                    
        $this->set("frmSearch", $frmSearch);    
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewManualShippingApi();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        
        $state_id = isset($data['state_id'])?FatUtility::int($data['state_id']):0;
        
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $obj = new ManualShippingApi();
        $srch = $obj->getListingObj($this->adminLangId, array('msa.*','msa_l.mshipapi_comment'));
        
        if(!empty($post['keyword'])) {
            $cond = $srch->addCondition('sd.sduration_identifier', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('sd_l.sduration_name', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('msa.mshipapi_zip', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('msa.mshipapi_cost', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('msa.mshipapi_volume_upto', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('msa.mshipapi_weight_upto', 'like', '%'.$post['keyword'].'%', 'OR');
        } 
        
        $country_id = FatUtility::int($post['country_id']);
        if($country_id > -1) {
            $srch->addCondition('c.country_id', '=', $country_id);
        }    
        
        $sduration_id = FatUtility::int($post['sduration_id']);
        if($sduration_id > -1) {
            $srch->addCondition('sd.sduration_id', '=', $sduration_id);
        }
        
        if($state_id > 0) {
            $srch->addCondition('s.state_id', '=', $state_id);
        }
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records =array();
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
    
    public function form($mshipapi_id = 0)
    {
        $this->objPrivilege->canViewManualShippingApi();
    
        $mshipapi_id = FatUtility::int($mshipapi_id);
        $frm = $this->getForm();

        $stateId = 0;
        if (0 < $mshipapi_id ) {
            $data = ManualShippingApi::getAttributesById($mshipapi_id);            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
            $stateId = $data['mshipapi_state_id'];
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('mshipapi_id', $mshipapi_id);
        $this->set('stateId', $stateId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }
    
    function setup()
    {
        $this->objPrivilege->canEditManualShippingApi();

        $post = FatApp::getPostedData();
        
        $mshipapi_state_id =0;
        if(isset($post['mshipapi_state_id'])) {
            $mshipapi_state_id = FatUtility::int($post['mshipapi_state_id']);    
        }
        
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray($post);
        
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $post['mshipapi_state_id'] = $mshipapi_state_id;
        $mshipapi_id = $post['mshipapi_id'];
        unset($post['mshipapi_id']);
        
        $record = new ManualShippingApi($mshipapi_id);        
        $record->assignValues($post);
        
        if (!$record->save()) {     
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());            
        } 
        
        $newTabLangId = 0;    
        if($mshipapi_id > 0) {            
            $languages = Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row = ManualShippingApi::getAttributesByLangId($langId, $mshipapi_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        }else{
            $mshipapi_id = $record->getMainTableRecordId();
            $newTabLangId = $this->adminLangId;    
        }    
        
        $this->set('msg', $this->str_setup_successful);
        $this->set('mshipapiId', $mshipapi_id);
        $this->set('langId', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($mshipapi_id = 0,$lang_id = 0)
    {
        $this->objPrivilege->canViewManualShippingApi();
        
        $mshipapi_id = FatUtility::int($mshipapi_id);
        $lang_id = FatUtility::int($lang_id);
        
        if($mshipapi_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $langFrm = $this->getLangForm($mshipapi_id, $lang_id);
            
        $langData = ManualShippingApi::getAttributesByLangId($lang_id, $mshipapi_id);        
        
        if($langData) {
            $langFrm->fill($langData);            
        }
        
        $this->set('mshipapi_id', $mshipapi_id);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('languages', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);    
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditManualShippingApi();
        $post = FatApp::getPostedData();
        
        $mshipapi_id = $post['mshipapi_id'];
        $lang_id = $post['lang_id'];
        
        if($mshipapi_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $frm = $this->getLangForm($mshipapi_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        $data = array(
        'mshipapilang_mshipapi_id'=>$mshipapi_id,
        'mshipapilang_lang_id'=>$lang_id,
        'mshipapi_comment'=>$post['mshipapi_comment'],
        );
        
        $obj = new ManualShippingApi($mshipapi_id);    
        if(!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());                    
        }

        $newTabLangId=0;    
        $languages = Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row = ManualShippingApi::getAttributesByLangId($langId, $mshipapi_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }    
        
        $this->set('msg', $this->str_setup_successful);
        $this->set('mshipapiId', $mshipapi_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditManualShippingApi();
        
        $mshipapi_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($mshipapi_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new ManualShippingApi($mshipapi_id);
        if(!$obj->canRecordDelete($mshipapi_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }        
        
        if(!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmManualShippingSearch');        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        
        $shipDurationObj = new ShippingDurations();
        $durationArr = $shipDurationObj->getShippingDurationAssoc($this->adminLangId);
        $frm->addSelectbox(Labels::getLabel('LBL_Duration', $this->adminLangId), 'sduration_id', array( -1 =>'Does not Matter' ) + $durationArr, '', array(), '');    
        
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'country_id', array( -1 =>'Does not Matter' )+ $countriesArr, '', array(), '');
        
        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'state_id', array());
        
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);        
        return $frm;
    }
    
    private function getForm()
    {
        $this->objPrivilege->canViewManualShippingApi();        
        
        $shipDurationObj = new ShippingDurations();
        $durationArr = $shipDurationObj->getShippingDurationAssoc($this->adminLangId);
        
        $frm = new Form('frmManualShipping');        
        $frm->addHiddenField('', 'mshipapi_id', 0);
        $frm->addSelectbox(Labels::getLabel('LBL_Duration', $this->adminLangId), 'mshipapi_sduration_id', $durationArr)->requirement->setRequired(true);    
        $frm->addFloatField(Labels::getLabel('LBL_Volume_Upto', $this->adminLangId), 'mshipapi_volume_upto');
        $frm->addFloatField(Labels::getLabel('LBL_Weight_Upto', $this->adminLangId), 'mshipapi_weight_upto');
        $frm->addFloatField(Labels::getLabel('LBL_Cost', $this->adminLangId), 'mshipapi_cost');
        
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'mshipapi_country_id', $countriesArr, '');
        
        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'mshipapi_state_id', array());
        $frm->addTextbox(Labels::getLabel('LBL_Postal_Code', $this->adminLangId), 'mshipapi_zip');
        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
    
    private function getLangForm($mshipapi_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewManualShippingApi();        
        
        $mshipapi_id = FatUtility::int($mshipapi_id);
        $lang_id = FatUtility::int($lang_id);
        
        $frm = new Form('frmManualShippingLang');    
        $frm->addHiddenField('', 'mshipapi_id', $mshipapi_id);        
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addTextarea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'mshipapi_comment');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
}    