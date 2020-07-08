<?php
class LanguagesController extends AdminBaseController
{
    private $canView;
    private $canEdit;


    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewLanguage($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditLanguage($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index( )
    {
        $this->objPrivilege->canViewLanguage();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
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

    public function search()
    {
        $this->objPrivilege->canViewLanguage();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Language::getSearchObject(false, $this->adminLangId);

        $srch->addFld('l.* ');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('l.language_code', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('l.language_name', 'like', '%'.$post['keyword'].'%', 'OR');
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

    public function form($languageId)
    {
        $this->objPrivilege->canEditLanguage();

        $languageId =  FatUtility::int($languageId);

        $frm = $this->getForm($languageId);

        if (0 < $languageId ) {
            $data = Language::getAttributesById($languageId, array('language_id','language_code','language_name','language_active','language_layout_direction'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('language_id', $languageId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditLanguage();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $languageId = FatApp::getPostedData('language_id', FatUtility::VAR_INT, 0);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $record = new Language($languageId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_language_code_is_not_available', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Keyword', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }


    private function getForm($languageId = 0)
    {
        $this->objPrivilege->canViewLanguage();
        $languageId =  FatUtility::int($languageId);

        $frm = new Form('frmLanguage');
        $frm->addHiddenField('', 'language_id', $languageId);
        $frm->addRequiredField(Labels::getLabel('LBL_Language_code', $this->adminLangId), 'language_code');
        $frm->addRequiredField(Labels::getLabel('LBL_Language_name', $this->adminLangId), 'language_name');
        $fld =$frm->addRadioButtons(
            Labels::getLabel("LBL_Language_Layout_Direction", $this->adminLangId), 'language_layout_direction',
            applicationConstants::getLayoutDirections($this->adminLangId), '', array('class'=>'list-inline')
        );
        $arrFlags = $this->getlanguageFlags();
        /* $arrFlag= array();
        $dir    = '..'.CONF_WEBROOT_FRONT_URL.'public/images/flags/';
        foreach($arrFlags  as  $flag){
        $arrFlag []= '<img src ="'.$dir.$flag.'">';
        }
        $fld =$frm->addRadioButtons(Labels::getLabel("LBL_Language_Flag",$this->adminLangId),'language_flag',
        $arrFlag,'',array('class'=>'list-inline')); */
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'language_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function getLanguageFlags()
    {
        $arrFlag = array();
        $dir    = CONF_INSTALLATION_PATH.'public/images/flags';
        $arrFlags = array_diff(scandir($dir, 1), array(".", ".."));

        return $arrFlags;
    }

    public function media($languageId)
    {
        $this->objPrivilege->canEditLanguage();

        if(0 >= $languageId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $selectedFlag = Language::getAttributesById($languageId, 'language_flag');
        $flags = $this->getlanguageFlags();
        $this->set('selectedFlag', $selectedFlag);
        $this->set('flags', $flags);
        $this->set('language_id', $languageId);
        $this->_template->render(false, false);
    }
    public function changeStatus()
    {
        $this->objPrivilege->canEditLanguage();
        $languageId = FatApp::getPostedData('languageId', FatUtility::VAR_INT, 0);
        if(0 >= $languageId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Language::getAttributesById($languageId, array('language_active'));

        if($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ( $data['language_active'] == applicationConstants::ACTIVE ) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $countryObj = new Language($languageId);
        if (!$countryObj->changeStatus($status)) {
            Message::addErrorMessage($countryObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_update_record);
    }
    public function updateImage()
    {
        $this->objPrivilege->canEditLanguage();
        $languageId = FatApp::getPostedData('languageId', FatUtility::VAR_INT, 0);
        $flag = FatApp::getPostedData('flag', FatUtility::VAR_STRING, '');
        if(0 >= $languageId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Language::getAttributesById($languageId, array('language_active'));

        if($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $data['language_flag']= $flag;
        $record = new Language($languageId);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unable_to_set_image', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

}
