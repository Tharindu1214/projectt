<?php
class ShippingCompaniesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShippingCompanies($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShippingCompanies($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShippingCompanies();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShippingCompanies();

        $srch = ShippingCompanies::getSearchObject(false, $this->adminLangId);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('scompany_id', 'DESC');

        $rs = $srch->getResultSet();
        $records =array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($shippingCompanyId)
    {
        $this->objPrivilege->canViewShippingCompanies();
        $shippingCompanyId =  FatUtility::int($shippingCompanyId);

        $frm = $this->getForm($shippingCompanyId);

        /* if(1 > $shippingCompanyId){
        Message::addErrorMessage($this->str_invalid_request_id);
        FatUtility::dieJsonError( Message::getHtml() );
        } */
        if (0 < $shippingCompanyId) {
            $data = ShippingCompanies::getAttributesById($shippingCompanyId, array('scompany_id','scompany_identifier','scompany_active'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('languages', Language::getAllNames());
        $this->set('scompany_id', $shippingCompanyId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShippingCompanies();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scompany_id = FatUtility::int($post['scompany_id']);
        unset($post['scompany_id']);

        /* $data = ShippingCompanies::getAttributesById($scompany_id,array('scompany_id'));
        if ($data === false) {
        FatUtility::dieWithError($this->str_invalid_request);
        } */

        $record = new ShippingCompanies($scompany_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($scompany_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = ShippingCompanies::getAttributesByLangId($langId, $scompany_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $scompany_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sCompanyId', $scompany_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($scompany_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewShippingCompanies();

        $scompany_id = FatUtility::int($scompany_id);
        $lang_id = FatUtility::int($lang_id);

        if ($scompany_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($scompany_id, $lang_id);

        $langData = ShippingCompanies::getAttributesByLangId($lang_id, $scompany_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('scompany_id', $scompany_id);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditShippingCompanies();
        $post = FatApp::getPostedData();

        $scompany_id = $post['scompany_id'];
        $lang_id = $post['lang_id'];

        if ($scompany_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($scompany_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['scompany_id']);
        unset($post['lang_id']);

        $data = array(
        'scompanylang_lang_id'=>$lang_id,
        'scompanylang_scompany_id'=>$scompany_id,
        'scompany_name'=>$post['scompany_name']
        );

        $sCompanyObj = new ShippingCompanies($scompany_id);

        if (!$sCompanyObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($sCompanyObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = ShippingCompanies::getAttributesByLangId($langId, $scompany_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sCompanyId', $scompany_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditShippingCompanies();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $sCompanyObj = new ShippingCompanies();
            if (!$sCompanyObj->updateOrder($post['shippingMethod'])) {
                Message::addErrorMessage($sCompanyObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditShippingCompanies();
        $scompanyId = FatApp::getPostedData('scompanyId', FatUtility::VAR_INT, 0);
        if (0 >= $scompanyId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ShippingCompanies::getAttributesById($scompanyId, array('scompany_id', 'scompany_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['scompany_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateShippingCompanyStatus($scompanyId, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditShippingCompanies();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $scompanyIdsArr = FatUtility::int(FatApp::getPostedData('scompany_ids'));
        if (empty($scompanyIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($scompanyIdsArr as $scompanyId) {
            if (1 > $scompanyId) {
                continue;
            }

            $this->updateShippingCompanyStatus($scompanyId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateShippingCompanyStatus($scompanyId, $status)
    {
        $status = FatUtility::int($status);
        $scompanyId = FatUtility::int($scompanyId);
        if (1 > $scompanyId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new ShippingCompanies($scompanyId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getForm($scompany_id = 0)
    {
        $scompany_id =  FatUtility::int($scompany_id);

        $frm = new Form('frmShippingCompany');
        $frm->addHiddenField('', 'scompany_id', $scompany_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shipping_Identifier', $this->adminLangId), 'scompany_identifier');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'scompany_active', $activeInactiveArr, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($scompany_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmShippingCompanyLang');
        $frm->addHiddenField('', 'scompany_id', $scompany_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shipping_Api_Name', $this->adminLangId), 'scompany_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
