<?php
class CurrencyManagementController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCurrencyManagement($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCurrencyManagement($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCurrencyManagement();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewCurrencyManagement();

        $srch = Currency::getSearchObject($this->adminLangId, false);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addFld('*');
        $srch->addOrder('currency_display_order', 'ASC');

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($currencyId = 0)
    {
        $this->objPrivilege->canViewCurrencyManagement();

        $currencyId =  FatUtility::int($currencyId);

        $frm = $this->getForm($currencyId);

        if (0 > $currencyId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $defaultCurrency = 0;
        if ($currencyId > 0) {
            $data = Currency::getAttributesById($currencyId, array('currency_id','currency_code','currency_active','currency_symbol_left','currency_symbol_right','currency_value','currency_is_default'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $defaultCurrency = $data['currency_is_default'];
            $frm->fill($data);
        }


        $this->set('languages', Language::getAllNames());
        $this->set('currency_id', $currencyId);
        $this->set('defaultCurrency', $defaultCurrency);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCurrencyManagement();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $currencyId = FatUtility::int($post['currency_id']);
        unset($post['currency_id']);
        if ($currencyId > 0) {
            $data = Currency::getAttributesById($currencyId, array('currency_id','currency_is_default'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if ($data['currency_is_default'] == 1) {
                unset($post['currency_value']);
            }
        }
        $record = new Currency($currencyId);
        $post['currency_date_modified'] = date('Y-m-d H:i:s');
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($currencyId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Currency::getAttributesByLangId($langId, $currencyId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $currencyId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('currencyId', $currencyId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($currencyId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCurrencyManagement();

        $currencyId = FatUtility::int($currencyId);
        $lang_id = FatUtility::int($lang_id);

        if ($currencyId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($currencyId, $lang_id);

        $langData = Currency::getAttributesByLangId($lang_id, $currencyId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('currencyId', $currencyId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCurrencyManagement();
        $post = FatApp::getPostedData();

        $currencyId = $post['currency_id'];
        $lang_id = $post['lang_id'];

        if ($currencyId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($currencyId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['currency_id']);
        unset($post['lang_id']);

        $data = array(
        'currencylang_lang_id'=>$lang_id,
        'currencylang_currency_id'=>$currencyId,
        'currency_name'=>$post['currency_name']
        );

        $currencyObj = new Currency($currencyId);

        if (!$currencyObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($currencyObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Currency::getAttributesByLangId($langId, $currencyId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('currencyId', $currencyId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditCurrencyManagement();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $currencyObj = new Currency();
            if (!$currencyObj->updateOrder($post['currencyList'])) {
                Message::addErrorMessage($currencyObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditCurrencyManagement();
        $currencyId = FatApp::getPostedData('currencyId', FatUtility::VAR_INT, 0);
        if (0 >= $currencyId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Currency::getAttributesById($currencyId, array('currency_id', 'currency_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['currency_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateCurrencyStatus($currencyId, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditCurrencyManagement();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $currencyIdsArr = FatUtility::int(FatApp::getPostedData('currency_ids'));
        if (empty($currencyIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($currencyIdsArr as $currencyId) {
            if (1 > $currencyId) {
                continue;
            }

            $this->updateCurrencyStatus($currencyId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateCurrencyStatus($currencyId, $status)
    {
        $status = FatUtility::int($status);
        $currencyId = FatUtility::int($currencyId);
        if (1 > $currencyId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new Currency($currencyId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getForm($currencyId = 0)
    {
        $this->objPrivilege->canViewCurrencyManagement();
        $currencyId =  FatUtility::int($currencyId);

        $frm = new Form('frmCurrency');
        $frm->addHiddenField('', 'currency_id', $currencyId);
        $frm->addRequiredField(Labels::getLabel('LBL_Currency_code', $this->adminLangId), 'currency_code');
        $frm->addTextbox(Labels::getLabel('LBL_Currency_Symbol_Left', $this->adminLangId), 'currency_symbol_left');
        $frm->addTextbox(Labels::getLabel('LBL_Currency_Symbol_Right', $this->adminLangId), 'currency_symbol_right');
        $frm->addFloatField(Labels::getLabel('LBL_Currency_Conversion_Value', $this->adminLangId), 'currency_value');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'currency_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($currencyId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCurrencyManagement();
        $frm = new Form('frmCurrencyLang');
        $frm->addHiddenField('', 'currency_id', $currencyId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Currency_Name', $this->adminLangId), 'currency_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
