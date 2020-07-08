<?php
class CountriesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCountries($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCountries($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCountries();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->addJs('js/import-export.js');
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
        $this->objPrivilege->canViewCountries();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Countries::getSearchObject(false, $this->adminLangId);

        $srch->addFld('c.* , c_l.country_name');
        $srch->addOrder('country_name', 'ASC');
        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('c.country_code', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('c_l.country_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

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

    public function form($countryId)
    {
        $this->objPrivilege->canEditCountries();

        $countryId =  FatUtility::int($countryId);

        $frm = $this->getForm($countryId);

        if (0 < $countryId) {
            $data = Countries::getAttributesById($countryId, array('country_id','country_code','country_active','country_currency_id','country_language_id'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('country_id', $countryId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCountries();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $countryId = $post['country_id'];
        unset($post['country_id']);

        $record = new Countries($countryId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($countryId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Countries::getAttributesByLangId($langId, $countryId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $countryId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        Product::updateMinPrices();
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('countryId', $countryId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($countryId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCountries();

        $countryId = FatUtility::int($countryId);
        $lang_id = FatUtility::int($lang_id);

        if ($countryId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($countryId, $lang_id);

        $langData = Countries::getAttributesByLangId($lang_id, $countryId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('countryId', $countryId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCountries();
        $post = FatApp::getPostedData();

        $countryId = $post['country_id'];
        $lang_id = $post['lang_id'];

        if ($countryId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($countryId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['country_id']);
        unset($post['lang_id']);

        $data = array(
        'countrylang_lang_id'=>$lang_id,
        'countrylang_country_id'=>$countryId,
        'country_name'=>$post['country_name']
        );

        $countryObj = new Countries($countryId);

        if (!$countryObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($countryObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Countries::getAttributesByLangId($langId, $countryId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('countryId', $countryId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($countryId = 0)
    {
        $this->objPrivilege->canViewCountries();
        $countryId =  FatUtility::int($countryId);

        $frm = new Form('frmCountry');
        $frm->addHiddenField('', 'country_id', $countryId);
        $frm->addRequiredField(Labels::getLabel('LBL_Country_code', $this->adminLangId), 'country_code');

        $currencyArr = Currency::getCurrencyNameWithCode($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Currency', $this->adminLangId), 'country_currency_id', $currencyArr);

        $languageArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'country_language_id', array(0=>'Site Default')+$languageArr, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'country_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($countryId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCountries();
        $frm = new Form('frmCountryLang');
        $frm->addHiddenField('', 'country_id', $countryId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Country_Name', $this->adminLangId), 'country_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditCountries();
        $countryId = FatApp::getPostedData('countryId', FatUtility::VAR_INT, 0);
        if (0 >= $countryId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Countries::getAttributesById($countryId, array('country_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['country_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateCountryStatus($countryId, $status);
        Product::updateMinPrices();
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditCountries();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $countryIdsArr = FatUtility::int(FatApp::getPostedData('country_ids'));
        if (empty($countryIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($countryIdsArr as $countryId) {
            if (1 > $countryId) {
                continue;
            }

            $this->updateCountryStatus($countryId, $status);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateCountryStatus($countryId, $status)
    {
        $status = FatUtility::int($status);
        $countryId = FatUtility::int($countryId);
        if (1 > $countryId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $countryObj = new Countries($countryId);
        if (!$countryObj->changeStatus($status)) {
            Message::addErrorMessage($countryObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
