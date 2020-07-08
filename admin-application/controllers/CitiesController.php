<?php
class CitiesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCities($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCities($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCities();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId, true);
        $stateArry = array();
        $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'country', $countriesArr);
        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'state', $stateArry);
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewCities();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
    
        $srch = Cities::getSearchObject(false, $this->adminLangId);

        // Countries 
        $countrySearchObj = Countries::getSearchObject(true, $this->adminLangId);
        $countrySearchObj->doNotCalculateRecords();
        $countrySearchObj->doNotLimitRecords();
        $countriesDbView = $countrySearchObj->getQuery();

        // States

        $stateSearchObj = States::getSearchObject(true, $this->adminLangId);
        $stateSearchObj->doNotCalculateRecords();
        $stateSearchObj->doNotLimitRecords();
        $statesDbView = $stateSearchObj->getQuery();

        $srch->joinTable(
            "($countriesDbView)",
            'INNER JOIN',
            'ct.'.Cities::DB_TBL_PREFIX.'country_id = c.'.Countries::tblFld('id'),
            'c'
        );

        $srch->joinTable(
            "($statesDbView)",
            'INNER JOIN',
            'ct.'.Cities::DB_TBL_PREFIX.'state_id = st.'.States::tblFld('id'),
            'st'
        );

        $srch->addMultipleFields(array('ct.*' , 'city_name', 'c.country_name', 'st.state_name'));

        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('ct.city_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('city_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        if (!empty($post['country'])) {
            $condition=$srch->addCondition('ct.city_country_id', '=', $post['country']);
        }

        if (!empty($data['state']) && $data['state'] != -1) {
            $condition = $srch->addCondition('ct.city_state_id', '=', $data['state']);
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('city_name', 'ASC');

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
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


    public function form($cityId)
    {
        $this->objPrivilege->canEditCities();

        $cityId =  FatUtility::int($cityId);

        $frm = $this->getForm($cityId);
        $countryId = 0;
        $stateId = 0;
        if (0 < $cityId) {
            $data = Cities::getAttributesById($cityId, array('city_id','city_code','city_country_id', 'city_state_id','city_identifier','city_active'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $countryId = $data['city_country_id'];
            $stateId = $data['city_state_id'];
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('city_id', $cityId);
        $this->set('city_country_id', $countryId);
        $this->set('city_state_id', $stateId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCities();
        $frm = $this->getForm();
        $data = FatApp::getPostedData();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post['city_state_id'] = $data['city_state_id'];
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cityId = $post['city_id'];
        unset($post['city_id']);
        $record = new Cities($cityId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($cityId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Cities::getAttributesByLangId($langId, $cityId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $cityId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_setup_successful);
        $this->set('cityId', $cityId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($cityId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCities();
        $cityId = FatUtility::int($cityId);
        $lang_id = FatUtility::int($lang_id);

        if ($cityId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($cityId, $lang_id);
        $langData = Cities::getAttributesByLangId($lang_id, $cityId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('stateId', $cityId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCities();
        $post = FatApp::getPostedData();

        $cityId = $post['city_id'];
        $lang_id = $post['lang_id'];

        if ($cityId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($cityId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['city_id']);
        unset($post['lang_id']);

        $data = array(
        'citylang_city_id'=>$lang_id,
        'citylang_lang_id'=>$cityId,
        'city_name'=>$post['city_name']
        );

        $stateObj = new Cities($cityId);

        if (!$stateObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($stateObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Cities::getAttributesByLangId($langId, $cityId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('stateId', $cityId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($cityId = 0)
    {
        $this->objPrivilege->canViewCities();
        $stateId =  FatUtility::int($cityId);

        $frm = new Form('frmState');
        $frm->addHiddenField('', 'city_id', $cityId);
        $frm->addRequiredField(Labels::getLabel('LBL_City_Identifier', $this->adminLangId), 'city_identifier');
        $frm->addRequiredField(Labels::getLabel('LBL_City_Code', $this->adminLangId), 'city_code');

        
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId, true);

        $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'city_country_id', $countriesArr)->requirements()->setRequired(true);


        $stateArry = array();
        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'city_state_id', $stateArry)->requirements()->setRequired(true);

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'city_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($stateId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCities();
        $frm = new Form('frmStateLang');
        $frm->addHiddenField('', 'city_id', $stateId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_City_Name', $this->adminLangId), 'city_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditCities();
        $cityId = FatApp::getPostedData('cityId', FatUtility::VAR_INT, 0);
        if (0 >= $cityId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Cities::getAttributesById($cityId, array('city_id','city_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['city_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateStatesStatus($cityId, $status);
        Product::updateMinPrices();
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditCities();
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $statesIdsArr = FatUtility::int(FatApp::getPostedData('state_ids'));
        if (empty($statesIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($statesIdsArr as $stateId) {
            if (1 > $stateId) {
                continue;
            }

            $this->updateStatesStatus($stateId, $status);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateStatesStatus($stateId, $status)
    {
        $status = FatUtility::int($status);
        $stateId = FatUtility::int($stateId);
        if (1 > $stateId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $stateObj = new Cities($stateId);
        if (!$stateObj->changeStatus($status)) {
            Message::addErrorMessage($stateObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
