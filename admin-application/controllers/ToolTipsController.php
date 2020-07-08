<?php
class ToolTipsController extends AdminBaseController
{
    private $canView;
    private $canEdit;    
    
    public function __construct($action) 
    {
        $ajaxCallArray = array('form','langForm','search','setup','langSetup');
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        } 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewTooltip($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditTooltip($this->admin_id, true);
        
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);    
    }
        
    public function index()
    {    
        $this->objPrivilege->canViewTooltip();
        $search = $this->getSearchForm();                    
        $this->set("search", $search);
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewTooltip();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);        
        
        $srch = Tooltip::getSearchObject();
        $srch->addFld('t.*');
        if(!empty($post['keyword'])) {
            $condition = $srch->addCondition('t.tooltip_key', 'like', '%'.$post['keyword'].'%');
        }
        
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);    
        
        
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        //echo '<pre>';
        //print_r($records);die;
        
        $this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);                        
        $this->set('recordCount', $srch->recordCount());                                
        $this->_template->render(false, false);        
    }    
    
    public function form( $tooltipId = 0 )
    {
        $this->objPrivilege->canEditTooltip();
        
        $tooltipId = FatUtility::int($tooltipId);
        $tooltipFrm = $this->getForm($tooltipId);

        if (0 < $tooltipId ) {
            $data = Tooltip::getAttributesById($tooltipId, array('tooltip_id','tooltip_key','tooltip_default_value'));            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            $tooltipFrm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('tooltipId', $tooltipId);
        $this->set('tooltipFrm', $tooltipFrm);        
        $this->_template->render(false, false);
    }

    public function langForm( $tooltipId = 0, $lang_id = 0,$action = 'add')
    {

        $this->objPrivilege->canEditTooltip();
        
        $tooltipId = FatUtility::int($tooltipId);
        $lang_id = FatUtility::int($lang_id);
        
        if($tooltipId==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $data = Tooltip::getAttributesById($tooltipId, array('tooltip_default_value'));
        
        if($action == 'edit') {
            $defaultValue = $data['tooltip_default_value'];
        }else{
            $defaultValue = 0;
        }

        $tooltipLangFrm = $this->getLangForm($tooltipId, $lang_id, $defaultValue);
        $langData = Tooltip::getAttributesByLangId($lang_id, $tooltipId);        

        if($langData ) {
            $tooltipLangFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('tooltipId', $tooltipId);
        $this->set('action', $action);
        $this->set('tooltip_lang_id', $lang_id);
        $this->set('tooltipLangFrm', $tooltipLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }    
    
    private function getForm( $tooltipId = 0 )
    {
        
        $this->objPrivilege->canEditTooltip();        
        $tooltipId = FatUtility::int($tooltipId);

        $actionValue = Labels::getLabel('LBL_Add_New', $this->adminLangId);
        
        if($tooltipId > 0 ) {
            $actionValue=Labels::getLabel('LBL_Update', $this->adminLangId);
        }
            
        $frm = new Form('frmTooltip', array('id'=>'frmTooltip'));        
        $frm->addHiddenField('', 'tooltip_id', 0);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Tooltip_Key', $this->adminLangId), 'tooltip_key');
        $fld->requirements()->setRequired();
        
        $fld1 = $frm->addTextarea(Labels::getLabel('LBL_Tooltip_Default_Value', $this->adminLangId), 'tooltip_default_value');
                
        $fld1->requirements()->setRequired();        
                    
        $frm->addSubmitButton('', 'btn_submit', $actionValue);        
        return $frm;
    }

    public function setup()
    {
        $this->objPrivilege->canEditTooltip();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }

        $tooltip_id = $post['tooltip_id'];
        
        
        unset($post['tooltip_id']);
        $data = $post;
        
        $record = new Tooltip($tooltip_id);
        $record->assignValues($data);
        
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $tooltip_id = $record->getMainTableRecordId();
        
        $newTabLangId=0;    
        if($tooltip_id>0) {
            $tooltipId=$tooltip_id;
            $languages=Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row=Tooltip::getAttributesByLangId($langId, $tooltip_id)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
        } else {
            $tooltipId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }    
        

        $this->set('msg', Labels::getLabel('MSG_Tooltip_Setup_Successful', $this->adminLangId));
        $this->set('tooltipId', $tooltipId);
        $this->set('langId', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }    
    
    private function getLangForm($tooltipId = 0,$lang_id = 0,$default_val = 0)
    {    
    
        $frm = new Form('frmTooltipLang', array('id'=>'frmTooltipLang'));        
        $frm->addHiddenField('', 'tooltip_id', $tooltipId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        
        if($default_val) {
            $frm->addTextBox(Labels::getLabel('LBL_Tooltip_Default', $this->adminLangId), 'tooltip_default_value_new', $default_val);
        }

        $fld = $frm->addTextarea(Labels::getLabel('LBL_Tooltip_Text', $this->adminLangId), 'tooltip_text');        
        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditTooltip();
        $post=FatApp::getPostedData();
        
        $tooltip_id = $post['tooltip_id'];
        $lang_id = $post['lang_id'];
        
        if($tooltip_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $frm = $this->getLangForm($tooltip_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['tooltip_id']);
        unset($post['lang_id']);
        $data=array(
        'tooltiplang_lang_id'=>$lang_id,
        'tooltiplang_tooltip_id'=>$tooltip_id,
        'tooltip_text'=>$post['tooltip_text'],
        );
        $tooltipObj=new Tooltip($tooltip_id);    
        
        if(!$tooltipObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($tooltipObj->getError());
            FatUtility::dieWithError(Message::getHtml());                
        }

        $newTabLangId=0;    
        $languages=Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row=Tooltip::getAttributesByLangId($langId, $tooltip_id)) {
                $newTabLangId = $langId;
                break;
            }            
        }
        
        $this->set('msg', Labels::getLabel('MSG_Tooltip_Setup_Successful', $this->adminLangId));
        $this->set('tooltipId', $tooltip_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }    
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);        
        return $frm;
    }
        
}
?>
