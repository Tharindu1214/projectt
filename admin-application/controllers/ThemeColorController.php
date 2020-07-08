<?php
class ThemeColorController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct($action)
    { 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewThemeColor($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditThemeColor($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index() 
    {
        $this->_template->addJs('js/jscolor.js');
        $this->objPrivilege->canViewThemeColor();    
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewThemeColor();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);    
        
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $srch = ThemeColor::getSearchObject(CommonHelper::getLangId(), false);
        $srch->addMultipleFields(array('t.tcolor_id , IFNULL(t_l.tcolor_name, t.tcolor_identifier) as tcolor_name','t.tcolor_first_color','t.tcolor_added_by'));
        if(!empty($post['keyword'])) {
            
            $srchCondition= $srch->addCondition('t_l.tcolor_name', 'like', '%'.$post['keyword'].'%');
            $srchCondition->attachCondition('t.tcolor_identifier', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        $srch->addOrder('tcolor_name', 'ASC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                                    
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));                        
        $this->_template->render(false, false);    
    }
        
    public function listing($tColorId)
    {
        $tColorId = FatUtility::int($tColorId);
        if(1 > $tColorId) {
            FatUtility::dieJsonError($this->str_invalid_request);    
        }        
        
        $frmSearch = $this->getSearchForm();
        $frmSearch->fill(array('tcolor_id'=>$tColorId));
        
    
        
        $this->set('data', $data);
        $this->set('tColorId', $tColorId);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }
    
    public function listingSearch()
    {
        $this->objPrivilege->canViewThemeColor();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $srch = ThemeColor::getSearchObject(false, $this->adminLangId);
        
        $srch->addFld('t.* , IFNULL(t_l.tcolor_name, t.tcolor_identifier) as tcolor_name');
        
        if(!empty($post['keyword'])) {
            
            $condition->attachCondition('t_l.tcolor_name', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('t.tcolor_identifier', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $rs = $srch->getResultSet();
        $records = array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);            
        }
        
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);        
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                
        $this->_template->render(false, false);    
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch');        
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
    
    public function form($tColorId)
    {
        
        $this->objPrivilege->canEditThemeColor();
        $tColorId =  FatUtility::int($tColorId);
        
        $frm = $this->getForm($tColorId);
        
        if (0 < $tColorId ) {
            $data = ThemeColor::getAttributesById($tColorId);
        
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('tcolor_id', $tColorId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);    
    }
    
    public function cloneForm($tColorId = 0)
    {
        
        $this->objPrivilege->canEditThemeColor();
        $tColorId =  FatUtility::int($tColorId);
        
        $frm = $this->getForm($tColorId);
        
        if (0 < $tColorId ) {
            $data = ThemeColor::getAttributesById($tColorId);
        
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $data['tcolor_id'] = 0;
            $data['tcolor_identifier'] = 'Copy of '.$data['tcolor_identifier'];
            $frm->fill($data);
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('tcolor_id', 0);
        $this->set('frm', $frm);
        $this->_template->render(false, false);    
    }
    
    public function setup()
    {
        $this->objPrivilege->canEditThemeColor();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }

        $tColorId = $post['tcolor_id'];
        unset($post['tcolor_id']);
        $data = $post;
        $data['tcolor_added_by'] = $this->admin_id;
        $record = new ThemeColor($tColorId);    
        $record->assignValues($data);
        
        if (!$record->save()) {     
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());            
        } 
        
        $newTabLangId=0;    
        if($tColorId>0) {            
            $languages = Language::getAllNames();    
            foreach($languages as $langId =>$langName ){            
                if(!$row = ThemeColor::getAttributesByLangId($langId, $tColorId)) {
                    $newTabLangId = $langId;
                    break;
                }            
            }    
            $this->set('msg', Labels::getLabel($this->str_update_record, $this->adminLangId));    
            
        }else{
            $tColorId = $record->getMainTableRecordId();
            $this->set('msg', Labels::getLabel($this->str_setup_successful, $this->adminLangId));
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);    
        }
        
        $this->set('tColorId', $tColorId);
        $this->set('langId', $newTabLangId); 
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($tColorId = 0,$lang_id = 0)
    {
        $this->objPrivilege->canViewThemeColor();
        
        $tColorId = FatUtility::int($tColorId);
        $lang_id = FatUtility::int($lang_id);
        
        if($tColorId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $langFrm = $this->getLangForm($tColorId, $lang_id);
            
        $langData = ThemeColor::getAttributesByLangId($lang_id, $tColorId);        
        
        if($langData) {
            $langFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('tColorId', $tColorId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }
    
    public function langSetup()
    {
        $this->objPrivilege->canEditThemeColor();
        $post = FatApp::getPostedData();
        
        $tColorId = $post['tcolor_id'];
        $lang_id = $post['lang_id'];
        
        if($tColorId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $frm = $this->getLangForm($tColorId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['tcolor_id']);
        unset($post['lang_id']);
        
        $data = array(
        'tcolorlang_lang_id'=>$lang_id,
        'tcolorlang_tcolor_id'=>$tColorId,
        'tcolor_name'=>$post['tcolor_name']            
        );
        
        $themeColorObj = new ThemeColor($tColorId);
        
        if(!$themeColorObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($themeColorObj->getError());
            FatUtility::dieJsonError(Message::getHtml());                    
        }

        $newTabLangId = 0;    
        $languages = Language::getAllNames();    
        foreach($languages as $langId =>$langName ){            
            if(!$row = ThemeColor::getAttributesByLangId($langId, $tColorId)) {
                $newTabLangId = $langId;
                break;
            }            
        }    
    
        $this->set('msg', $this->str_setup_successful);
        $this->set('tColorId', $tColorId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getForm($tColorId = 0)
    {
        $this->objPrivilege->canViewThemeColor();
        $tColorId =  FatUtility::int($tColorId);
        
        $frm = new Form('frmThemeColor');        
        $frm->addHiddenField('', 'tcolor_id', $tColorId);
        
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'tcolor_identifier');
        $frm->addRequiredField(Labels::getLabel('LBL_First_Color', $this->adminLangId), 'tcolor_first_color')->addFieldTagAttribute('class', 'jscolor');
        $frm->addRequiredField(Labels::getLabel('LBL_Second_Color', $this->adminLangId), 'tcolor_second_color')->addFieldTagAttribute('class', 'jscolor');
        //$frm->addTextBox(Labels::getLabel('LBL_Third_Color',$this->adminLangId), 'tcolor_third_color')->addFieldTagAttribute('class', 'jscolor');
        
        /* $fld1 = $frm->addRequiredField(Labels::getLabel('LBL_Text_Color',$this->adminLangId), 'tcolor_text_color');*/
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Text_Color', $this->adminLangId), 'tcolor_text_color', '', array('class' => 'jscolor' ));
        $fld->htmlAfterField = '<br/><small>'.Labels::getLabel('LBL_White_color_is_not_recommended_for_this_field', $this->adminLangId).' </small>';
        
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Text_Light_Color', $this->adminLangId), 'tcolor_text_light_color', '', array('class' => 'jscolor' ));
        $fld->htmlAfterField = '<br/><small>'.Labels::getLabel('LBL_White_color_is_not_recommended_for_this_field', $this->adminLangId).' </small>';
        
        $frm->addRequiredField(Labels::getLabel('LBL_Header_Text_Color', $this->adminLangId), 'tcolor_header_text_color')->addFieldTagAttribute('class', 'jscolor');
        $frm->addRequiredField(Labels::getLabel('LBL_Border_Color', $this->adminLangId), 'tcolor_border_first_color')->addFieldTagAttribute('class', 'jscolor');
        //$frm->addTextBox(Labels::getLabel('LBL_Border_Second_Color',$this->adminLangId), 'tcolor_border_second_color')->addFieldTagAttribute('class', 'jscolor');
        //$frm->addTextBox(Labels::getLabel('LBL_Second_Button_Color',$this->adminLangId), 'tcolor_second_btn_color')->addFieldTagAttribute('class', 'jscolor');
        //$frm->addRequiredField(Labels::getLabel('LBL_Display_Order',$this->adminLangId), 'tcolor_display_order');
            
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
    }
    
    private function getLangForm($tColorId = 0,$lang_id = 0)
    {
        $this->objPrivilege->canViewThemeColor();
        $frm = new Form('frmThemeColorLang');        
        $frm->addHiddenField('', 'tcolor_id', $tColorId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_tcolor_name', $this->adminLangId), 'tcolor_name');                
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
        
    public function activateThemeColor($tColorId = 0)
    {
        /* if(CONF_DEVELOPMENT_MODE){	
        Message::addErrorMessage('We will activate this functionality when all css work done, Dont do it now');
        if(FatUtility::isAjaxCall()){
        FatUtility::dieWithError(Message::getHtml());
        }else{
        FatApp::redirectUser(CommonHelper::generateUrl('ThemeColor',''));
        }
        } */  
        $this->objPrivilege->canEditThemeColor();
        if(FatUtility::isAjaxCall()) {                
            $tColorId = FatApp::getPostedData('tColorId', FatUtility::VAR_INT, 0);    
        }
    
        if(0 >= $tColorId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        
        $data = ThemeColor::getAttributesById($tColorId, array('tcolor_id'));        
        
        if($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $configurationObj=new Configurations();
        if(!$configurationObj->update(array('CONF_FRONT_THEME'=>$tColorId))) {

            Message::addErrorMessage($configurationObj->getError());
            if(FatUtility::isAjaxCall()) {    
                FatUtility::dieJsonError(Message::getHtml());        
            }else{
                FatApp::redirectUser(CommonHelper::generateUrl('ThemeColor', ''));
            }
        }
        /* $this->updateCssFiles(); */
        if(FatUtility::isAjaxCall()) {
            $this->set('msg', Labels::getLabel('Msg_Theme_Activated_Successfully', CommonHelper::getLangId()));
            $this->_template->render(false, false, 'json-success.php');        
        }else{
            Message::addMessage(Labels::getLabel('Msg_Theme_Activated_Successfully', CommonHelper::getLangId()));
            FatApp::redirectUser(CommonHelper::generateUrl('ThemeColor', ''));
        }
    }
    
    public function deleteTheme()
    {
        $this->objPrivilege->canEditThemeColor();
        $tColorId = FatApp::getPostedData('tColorId', FatUtility::VAR_INT, 0);    
        
        if(0 >= $tColorId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        
        $data = ThemeColor::getAttributesById($tColorId, array('tcolor_id'));        
        
        if($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $this->set('msg', Labels::getLabel('Msg_Theme_Settings_Deleted_Successfully', CommonHelper::getLangId()));
        $themeObj = new ThemeColor($tColorId);
        if(!$themeObj->deleteRecord(true)) {
            Message::addErrorMessage($themeObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $this->_template->render(false, false, 'json-success.php');                
    }
    
    public function updateCssFiles()
    {
        $theme_detail = ThemeColor::getAttributesById(FatApp::getConfig('CONF_FRONT_THEME'));
        
        
        if(!$theme_detail) {
            $selected_theme = 1;
        }
        
        $filesArr =  array(
        'common-css/1base.css'=>'css/css-templates/1base.css',
        'common-css/2nav.css'=>'css/css-templates/2nav.css',
        'common-css/3skeleton.css'=>'css/css-templates/3skeleton.css',
        'common-css/4phone.css'=>'css/css-templates/4phone.css'
        );
        $i=1;
        
        foreach ($filesArr as $fileKey=>$fileName){
            $str='';
            if (substr($fileName, '-4') != '.css') { continue; 
            }
            $oldFile = CONF_FRONT_END_THEME_PATH . $fileName;
            if (file_exists($oldFile)) { $str .= file_get_contents($oldFile); 
            }
            $newFileName = CONF_FRONT_END_THEME_PATH.$fileKey;
            $newFile = fopen($newFileName, 'w');
            $replace_arr=array(

            "var(--first-color)"=>$theme_detail['tcolor_first_color'],

            "var(--second-color)"=>$theme_detail['tcolor_second_color'],

            "var(--third-color)"=>$theme_detail['tcolor_third_color'],

            "var(--txt-color)"=>$theme_detail['tcolor_text_color'],

            "var(--txt-color-light)"=>$theme_detail['tcolor_text_light_color'],

            "var(--border-color)"=>$theme_detail['tcolor_border_first_color'],

            "var(--border-color-second)"=>$theme_detail['tcolor_border_second_color'],

            "var(--second-btn-color)"=>$theme_detail['tcolor_second_btn_color'],
                
            "var(--header-txt-color)"=>$theme_detail['tcolor_header_text_color'],
                
            );
            
            foreach ($replace_arr as $key => $val) {
                $str = str_replace($key, "#".$val, $str);
            }
            fwrite($newFile, $str);
        }
    }
    
    public function preview($tColorId) 
    {
        $tColorId = FatUtility::int($tColorId);
        if(0 >= $tColorId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatApp::redirectUser(CommonHelper::generateUrl('ThemeColor'));    
        }
        
        /* $tObj = new ThemeColor();
       	$theme = $tObj->getAttributesById($tColorId); */
        if (!$tColorId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatApp::redirectUser(CommonHelper::generateUrl('ThemeColor'));    
        }
        $_SESSION['preview_theme']= $tColorId;
        
        $this->set('theme', $tColorId);
        $this->_template->render(false, false);
    }
}    