<?php
class ShippingMethodsController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShippingMethods($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShippingMethods($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShippingMethods();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShippingMethods();

        $srch = ShippingMethods::getSearchObject(false, $this->adminLangId);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('shippingapi_id', 'DESC');

        $rs = $srch->getResultSet();
        $records =array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($shippingApiId)
    {
        $this->objPrivilege->canViewShippingMethods();
        $shippingApiId =  FatUtility::int($shippingApiId);

        $frm = $this->getForm($shippingApiId);

        if (1 > $shippingApiId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = ShippingMethods::getAttributesById($shippingApiId, array('shippingapi_id','shippingapi_identifier','shippingapi_active'));
        if ($data === false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $frm->fill($data);

        $this->set('languages', Language::getAllNames());
        $this->set('shippingapi_id', $shippingApiId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShippingMethods();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shippingapi_id = FatUtility::int($post['shippingapi_id']);
        unset($post['shippingapi_id']);

        $data = ShippingMethods::getAttributesById($shippingapi_id, array('shippingapi_id'));
        if ($data === false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $record = new ShippingMethods($shippingapi_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;

        if ($shippingapi_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = ShippingMethods::getAttributesByLangId($langId, $shippingapi_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $shippingapi_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sMethodId', $shippingapi_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($shippingapi_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewShippingMethods();

        $shippingapi_id = FatUtility::int($shippingapi_id);
        $lang_id = FatUtility::int($lang_id);

        if ($shippingapi_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($shippingapi_id, $lang_id);

        $langData = ShippingMethods::getAttributesByLangId($lang_id, $shippingapi_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('shippingapi_id', $shippingapi_id);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditShippingMethods();
        $post = FatApp::getPostedData();

        $shippingapi_id = $post['shippingapi_id'];
        $lang_id = $post['lang_id'];

        if ($shippingapi_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($shippingapi_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['shippingapi_id']);
        unset($post['lang_id']);

        $data = array(
        'shippingapilang_lang_id'=>$lang_id,
        'shippingapilang_shippingapi_id'=>$shippingapi_id,
        'shippingapi_name'=>$post['shippingapi_name']
        );

        $sMethodObj = new ShippingMethods($shippingapi_id);

        if (!$sMethodObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($sMethodObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = PaymentMethods::getAttributesByLangId($langId, $shippingapi_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sMethodId', $shippingapi_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditShippingMethods();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $sMethodObj = new ShippingMethods();
            if (!$sMethodObj->updateOrder($post['shippingMethod'])) {
                Message::addErrorMessage($sMethodObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditShippingMethods();
        $shippingapiId = FatApp::getPostedData('shippingapiId', FatUtility::VAR_INT, 0);
        if (0 >= $shippingapiId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ShippingMethods::getAttributesById($shippingapiId, array('shippingapi_id', 'shippingapi_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['shippingapi_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateShippingMethodsStatus($shippingapiId, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditShippingMethods();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $shippingapiIdsArr = FatUtility::int(FatApp::getPostedData('shippingapi_ids'));
        if (empty($shippingapiIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($shippingapiIdsArr as $shippingapiId) {
            if (1 > $shippingapiId) {
                continue;
            }

            $this->updateShippingMethodsStatus($shippingapiId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateShippingMethodsStatus($shippingapiId, $status)
    {
        $status = FatUtility::int($status);
        $shippingapiId = FatUtility::int($shippingapiId);
        if (1 > $shippingapiId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new ShippingMethods($shippingapiId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getForm($shippingapi_id = 0)
    {
        $shippingapi_id =  FatUtility::int($shippingapi_id);

        $frm = new Form('frmShippingMethod');
        $frm->addHiddenField('', 'shippingapi_id', $shippingapi_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shipping_Identifier', $this->adminLangId), 'shippingapi_identifier');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'shippingapi_active', $activeInactiveArr, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($shippingapi_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmShippingMethodLang');
        $frm->addHiddenField('', 'shippingapi_id', $shippingapi_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shipping_Api_Name', $this->adminLangId), 'shippingapi_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
